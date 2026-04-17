<?php
// =============================================
// tambah.php — Tambah Barang Baru
// Fitur baru: Upload Gambar + Validasi Server-Side
// =============================================
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

$errors = [];
$old    = ['nama_barang' => '', 'jumlah' => '', 'harga' => '', 'tanggal_masuk' => date('Y-m-d')];

// ──────────────────────────────────────────────────────────
// PROSES FORM (POST)
// ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil & simpan nilai input agar tidak hilang saat error
    $old = [
        'nama_barang'  => trim($_POST['nama_barang']  ?? ''),
        'jumlah'       => trim($_POST['jumlah']        ?? ''),
        'harga'        => trim($_POST['harga']          ?? ''),
        'tanggal_masuk'=> trim($_POST['tanggal_masuk'] ?? ''),
    ];

    // ── VALIDASI SERVER-SIDE ──────────────────────────────
    if (empty($old['nama_barang'])) {
        $errors['nama_barang'] = 'Nama barang tidak boleh kosong.';
    } elseif (strlen($old['nama_barang']) < 2) {
        $errors['nama_barang'] = 'Nama barang minimal 2 karakter.';
    }

    if ($old['jumlah'] === '') {
        $errors['jumlah'] = 'Jumlah tidak boleh kosong.';
    } elseif (!is_numeric($old['jumlah']) || (int)$old['jumlah'] < 1) {
        $errors['jumlah'] = 'Jumlah harus berupa angka positif (min. 1).';
    }

    if ($old['harga'] === '') {
        $errors['harga'] = 'Harga tidak boleh kosong.';
    } elseif (!is_numeric($old['harga']) || (float)$old['harga'] <= 0) {
        $errors['harga'] = 'Harga harus berupa angka lebih dari 0.';
    }

    if (empty($old['tanggal_masuk'])) {
        $errors['tanggal_masuk'] = 'Tanggal masuk tidak boleh kosong.';
    }

    // ── PROSES UPLOAD GAMBAR ──────────────────────────────
    $gambarPath = null;
    $thumbPath  = null;

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['gambar'];

        // a) Cek error upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['gambar'] = 'Terjadi kesalahan saat upload file.';
        }
        // b) Cek ukuran file (max 2MB)
        elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors['gambar'] = 'Ukuran file maksimal 2MB.';
        }
        else {
            // c) Validasi tipe MIME (lebih aman dari cek ekstensi)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMime = ['image/jpeg', 'image/jpg', 'image/png'];
            $allowedExt  = ['jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($mime, $allowedMime) || !in_array($ext, $allowedExt)) {
                $errors['gambar'] = 'Hanya file JPG dan PNG yang diperbolehkan.';
            } else {
                // d) Buat nama file unik dengan uniqid()
                $uploadDir = __DIR__ . '/uploads/original/';
                $thumbDir  = __DIR__ . '/uploads/thumbs/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                if (!is_dir($thumbDir))  mkdir($thumbDir,  0755, true);

                $namaAsli  = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
                $namaUnik  = uniqid('img_', true) . '_' . $namaAsli;
                $namaThumb = 'thumb_' . $namaUnik;

                // e) Pindahkan ke folder permanen
                $targetPath  = $uploadDir . $namaUnik;
                $thumbTarget = $thumbDir  . $namaThumb;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    // f) Buat thumbnail otomatis
                    buatThumbnail($targetPath, $thumbTarget, $mime);
                    $gambarPath = 'uploads/original/' . $namaUnik;
                    $thumbPath  = 'uploads/thumbs/'   . $namaThumb;
                } else {
                    $errors['gambar'] = 'Gagal menyimpan file ke server.';
                }
            }
        }
    }

    // ── SIMPAN KE DATABASE jika tidak ada error ───────────
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO barang (nama_barang, jumlah, harga, tanggal_masuk, gambar, thumb)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $old['nama_barang'],
            (int)$old['jumlah'],
            (float)$old['harga'],
            $old['tanggal_masuk'],
            $gambarPath,
            $thumbPath,
        ]);

        header('Location: index.php');
        exit;
    }
}

// ──────────────────────────────────────────────────────────
// FUNGSI HELPER: Buat thumbnail dengan GD Library
// ──────────────────────────────────────────────────────────
function buatThumbnail($source, $destination, $mime, $maxW = 200, $maxH = 200) {
    // Jika GD tidak tersedia, salin file asli sebagai fallback
    if (!extension_loaded('gd')) {
        copy($source, $destination);
        return;
    }

    list($w, $h) = getimagesize($source);
    $scale = min($maxW / $w, $maxH / $h);
    $newW  = (int)($w * $scale);
    $newH  = (int)($h * $scale);

    $srcImg = ($mime === 'image/png') ? imagecreatefrompng($source) : imagecreatefromjpeg($source);
    $thumb  = imagecreatetruecolor($newW, $newH);

    // Pertahankan transparansi PNG
    if ($mime === 'image/png') {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
        imagefilledrectangle($thumb, 0, 0, $newW, $newH, $transparent);
    }

    imagecopyresampled($thumb, $srcImg, 0, 0, 0, 0, $newW, $newH, $w, $h);

    // Simpan thumbnail (PNG atau JPEG) — hanya dipanggil SEKALI
    if ($mime === 'image/png') {
        imagepng($thumb, $destination, 8);
    } else {
        imagejpeg($thumb, $destination, 85);
    }

    imagedestroy($srcImg);
    imagedestroy($thumb);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Barang — Inventaris</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #04616E;
    --surface: #161b22;
    --surface2: #1c2333;
    --border: #30363d;
    --accent: #3fb950;
    --accent-hover: #56d364;
    --text: #e6edf3;
    --muted: #8b949e;
    --red: #f85149;
    --blue: #58a6ff;
  }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    padding: 40px 20px;
    position: relative;
    overflow-x: hidden;
  }

  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(rgba(63,185,80,0.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(63,185,80,0.03) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
    z-index: 0;
  }

  body::after {
    content: '';
    position: fixed;
    top: -200px; left: -200px;
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(63,185,80,0.06) 0%, transparent 70%);
    pointer-events: none;
    z-index: 0;
  }

  .container {
    position: relative;
    z-index: 1;
    max-width: 660px;
    margin: 0 auto;
  }

  .back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--muted);
    text-decoration: none;
    font-size: 13px;
    margin-bottom: 32px;
    transition: color 0.2s;
  }
  .back-link:hover { color: var(--text); }
  .back-link::before { content: '←'; font-size: 16px; }

  .page-tag {
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .page-tag::before {
    content: '';
    display: block;
    width: 20px; height: 2px;
    background: var(--accent);
  }

  h1 {
    font-family: 'Syne', sans-serif;
    font-size: 32px;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin-bottom: 4px;
    animation: slideDown 0.5s ease both;
  }

  .subtitle {
    color: var(--muted);
    font-size: 13px;
    margin-bottom: 32px;
    animation: slideDown 0.5s 0.05s ease both;
  }

  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    animation: fadeUp 0.5s 0.1s ease both;
  }

  .card-header {
    padding: 18px 28px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--surface2);
  }

  .card-header-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--accent);
    box-shadow: 0 0 8px var(--accent);
    animation: pulse 2s infinite;
  }

  .card-header-title {
    font-family: 'Syne', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.05em;
  }

  .card-body {
    padding: 28px;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  /* ── Alert Error ── */
  .alert-error {
    background: rgba(248,81,73,0.1);
    border: 1px solid rgba(248,81,73,0.3);
    color: var(--red);
    padding: 14px 16px;
    border-radius: 8px;
    font-size: 13px;
  }
  .alert-error strong {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
  }
  .alert-error ul {
    margin: 0;
    padding-left: 18px;
    line-height: 1.8;
  }

  /* ── Form Elements ── */
  .form-group {
    display: flex;
    flex-direction: column;
    gap: 7px;
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }

  label {
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--muted);
  }
  label span.req { color: var(--red); margin-left: 2px; }
  label span.opt {
    color: var(--muted);
    font-size: 10px;
    letter-spacing: 0;
    text-transform: none;
    font-weight: 400;
  }

  input[type="text"],
  input[type="number"],
  input[type="date"] {
    width: 100%;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    padding: 11px 14px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    appearance: none;
    -webkit-appearance: none;
  }
  input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(63,185,80,0.12);
  }
  input.input-error {
    border-color: var(--red);
    box-shadow: 0 0 0 3px rgba(248,81,73,0.1);
  }
  input::placeholder { color: #4a5568; }
  input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(0.5); cursor: pointer; }

  .field-error {
    color: var(--red);
    font-size: 11.5px;
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: 4px;
  }
  .field-error::before { content: '⚠'; }

  /* ── Upload Area ── */
  .upload-area {
    border: 2px dashed var(--border);
    border-radius: 10px;
    padding: 20px;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
    background: var(--surface2);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 120px;
    position: relative;
  }
  .upload-area:hover {
    border-color: var(--accent);
    background: rgba(63,185,80,0.04);
  }
  .upload-area.has-file {
    border-color: var(--accent);
    border-style: solid;
  }
  .upload-area.error-border { border-color: var(--red); }

  .upload-icon { font-size: 28px; line-height: 1; }
  .upload-label-text {
    font-size: 13px;
    color: var(--muted);
    text-align: center;
  }
  .upload-label-text strong { color: var(--accent); }
  .upload-hint { font-size: 11px; color: #4a5568; }

  /* Preview gambar */
  #previewContainer {
    display: none;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    width: 100%;
  }
  #previewImg {
    max-height: 160px;
    max-width: 100%;
    border-radius: 8px;
    border: 1px solid var(--border);
  }
  #previewName {
    font-size: 12px;
    color: var(--muted);
  }
  #btnGantiGambar {
    font-size: 11px;
    color: var(--accent);
    background: none;
    border: none;
    cursor: pointer;
    text-decoration: underline;
    padding: 0;
  }

  .divider { border: none; border-top: 1px solid var(--border); margin: 4px 0; }

  /* ── Action Buttons ── */
  .form-actions { display: flex; gap: 12px; align-items: center; }

  .btn-submit {
    flex: 1;
    background: var(--accent);
    color: #000;
    border: none;
    border-radius: 8px;
    font-family: 'Syne', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.05em;
    padding: 13px 24px;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
    overflow: hidden;
  }
  .btn-submit::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent);
  }
  .btn-submit:hover {
    background: var(--accent-hover);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(63,185,80,0.3);
  }
  .btn-submit:active { transform: translateY(0); }

  .btn-cancel {
    padding: 13px 20px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: transparent;
    color: var(--muted);
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.2s;
    white-space: nowrap;
  }
  .btn-cancel:hover { border-color: var(--text); color: var(--text); }

  /* ── Animations ── */
  @keyframes slideDown {
    from { opacity: 0; transform: translateY(-16px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
  }

  @media (max-width: 480px) {
    .form-row { grid-template-columns: 1fr; }
    .form-actions { flex-direction: column-reverse; }
    .btn-cancel { width: 100%; text-align: center; }
  }
</style>
</head>
<body>

<div class="container">

  <a href="index.php" class="back-link">Kembali ke Inventaris</a>

  <div class="page-tag">Inventaris Barang</div>
  <h1>Tambah Barang</h1>
  <p class="subtitle">Isi form di bawah untuk menambahkan barang baru ke inventaris.</p>

  <div class="card">
    <div class="card-header">
      <span class="card-header-dot"></span>
      <span class="card-header-title">Form Input Barang</span>
    </div>
    <div class="card-body">

      <?php if (!empty($errors)): ?>
      <div class="alert-error">
        <strong>⚠ Periksa kembali form!</strong>
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <form method="POST" action="" enctype="multipart/form-data">
        <div style="display:flex;flex-direction:column;gap:20px;">

          <!-- Nama Barang -->
          <div class="form-group">
            <label for="nama_barang">Nama Barang <span class="req">*</span></label>
            <input type="text" id="nama_barang" name="nama_barang"
              placeholder="Contoh: Laptop Asus VivoBook"
              class="<?= isset($errors['nama_barang']) ? 'input-error' : '' ?>"
              value="<?= htmlspecialchars($old['nama_barang']) ?>">
            <?php if (isset($errors['nama_barang'])): ?>
              <span class="field-error"><?= htmlspecialchars($errors['nama_barang']) ?></span>
            <?php endif; ?>
          </div>

          <!-- Jumlah & Harga -->
          <div class="form-row">
            <div class="form-group">
              <label for="jumlah">Jumlah <span class="req">*</span></label>
              <input type="number" id="jumlah" name="jumlah"
                placeholder="0" min="1"
                class="<?= isset($errors['jumlah']) ? 'input-error' : '' ?>"
                value="<?= htmlspecialchars($old['jumlah']) ?>">
              <?php if (isset($errors['jumlah'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['jumlah']) ?></span>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label for="harga">Harga Satuan (Rp) <span class="req">*</span></label>
              <input type="number" id="harga" name="harga"
                placeholder="0" min="1" step="1"
                class="<?= isset($errors['harga']) ? 'input-error' : '' ?>"
                value="<?= htmlspecialchars($old['harga']) ?>">
              <?php if (isset($errors['harga'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['harga']) ?></span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Tanggal Masuk -->
          <div class="form-group">
            <label for="tanggal_masuk">Tanggal Masuk <span class="req">*</span></label>
            <input type="date" id="tanggal_masuk" name="tanggal_masuk"
              class="<?= isset($errors['tanggal_masuk']) ? 'input-error' : '' ?>"
              value="<?= htmlspecialchars($old['tanggal_masuk']) ?>">
            <?php if (isset($errors['tanggal_masuk'])): ?>
              <span class="field-error"><?= htmlspecialchars($errors['tanggal_masuk']) ?></span>
            <?php endif; ?>
          </div>

          <!-- Upload Gambar -->
          <div class="form-group">
            <label>
              Foto Barang
              <span class="opt">&nbsp;(Opsional — JPG/PNG, maks. 2MB)</span>
            </label>

            <div class="upload-area <?= isset($errors['gambar']) ? 'error-border' : '' ?>"
                 id="uploadArea"
                 onclick="document.getElementById('gambar').click()">

              <div id="uploadPlaceholder">
                <div class="upload-icon">☁</div>
                <div class="upload-label-text">
                  <strong>Klik untuk pilih gambar</strong>
                </div>
                <div class="upload-hint">JPG, PNG • Maksimal 2MB</div>
              </div>

              <div id="previewContainer">
                <img id="previewImg" src="" alt="Preview">
                <span id="previewName"></span>
                <button type="button" id="btnGantiGambar">Ganti gambar</button>
              </div>
            </div>

            <input type="file"
                   id="gambar"
                   name="gambar"
                   accept=".jpg,.jpeg,.png"
                   style="display:none"
                   onchange="handleFileChange(this)">

            <?php if (isset($errors['gambar'])): ?>
              <span class="field-error"><?= htmlspecialchars($errors['gambar']) ?></span>
            <?php endif; ?>
          </div>

          <hr class="divider">

          <div class="form-actions">
            <a href="index.php" class="btn-cancel">Batal</a>
            <button type="submit" class="btn-submit">+ Simpan Barang</button>
          </div>

        </div>
      </form>

    </div>
  </div>

</div>

<script>
function handleFileChange(input) {
  const file = input.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById('uploadPlaceholder').style.display = 'none';
    document.getElementById('previewContainer').style.display  = 'flex';
    document.getElementById('previewImg').src  = e.target.result;
    document.getElementById('previewName').textContent =
      file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
    document.getElementById('uploadArea').classList.add('has-file');
  };
  reader.readAsDataURL(file);
}

document.getElementById('btnGantiGambar').addEventListener('click', function(e) {
  e.stopPropagation();
  document.getElementById('gambar').click();
});
</script>
</body>
</html>