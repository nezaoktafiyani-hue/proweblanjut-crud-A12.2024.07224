<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM barang WHERE id = ?");
$stmt->execute([$id]);
$barang = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$barang) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama_barang']);
    $jumlah  = (int) $_POST['jumlah'];
    $harga   = (float) $_POST['harga'];
    $tanggal = $_POST['tanggal_masuk'];

    if ($nama && $jumlah > 0 && $harga > 0 && $tanggal) {
        $stmt = $pdo->prepare("UPDATE barang SET nama_barang=?, jumlah=?, harga=?, tanggal_masuk=? WHERE id=?");
        $stmt->execute([$nama, $jumlah, $harga, $tanggal, $id]);
        header('Location: index.php');
        exit;
    } else {
        $error = 'Semua field wajib diisi dengan benar.';
    }
}

// Gunakan POST data jika ada, fallback ke DB
$val = [
    'nama_barang'  => $_POST['nama_barang']  ?? $barang['nama_barang'],
    'jumlah'       => $_POST['jumlah']        ?? $barang['jumlah'],
    'harga'        => $_POST['harga']         ?? $barang['harga'],
    'tanggal_masuk'=> $_POST['tanggal_masuk'] ?? $barang['tanggal_masuk'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Barang — Inventaris</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #04616E;
    --surface: #161b22;
    --surface2: #1c2333;
    --border: #30363d;
    --accent: #58a6ff;
    --accent-hover: #79b8ff;
    --text: #e6edf3;
    --muted: #8b949e;
    --red: #f0291f;
    --green: #3fb950;
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

  /* Grid background */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(rgba(88,166,255,0.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(88,166,255,0.03) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
    z-index: 0;
  }

  body::after {
    content: '';
    position: fixed;
    bottom: -200px;
    right: -200px;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(88,166,255,0.06) 0%, transparent 70%);
    pointer-events: none;
    z-index: 0;
  }

  .container {
    position: relative;
    z-index: 1;
    max-width: 640px;
    margin: 0 auto;
  }

  /* Back link */
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

  /* Page heading */
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
    width: 20px;
    height: 2px;
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

  /* ID badge */
  .id-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 4px 12px;
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 32px;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    animation: slideDown 0.5s 0.08s ease both;
  }

  .id-badge span { color: var(--accent); }

  /* Card */
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
    justify-content: space-between;
    background: var(--surface2);
  }

  .card-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
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

  .edit-badge {
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--accent);
    background: rgba(88,166,255,0.1);
    border: 1px solid rgba(88,166,255,0.25);
    padding: 3px 10px;
    border-radius: 999px;
  }

  .card-body {
    padding: 28px;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  /* Alert */
  .alert-error {
    background: rgba(248,81,73,0.1);
    border: 1px solid rgba(248,81,73,0.3);
    color: var(--red);
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .alert-error::before { content: '⚠'; }

  /* Form */
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

  label span { color: var(--red); margin-left: 2px; }

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
    box-shadow: 0 0 0 3px rgba(88,166,255,0.12);
  }

  input::placeholder { color: #4a5568; }

  input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0.5);
    cursor: pointer;
  }

  /* Modified indicator */
  input:not(:placeholder-shown) {
    border-color: rgba(88,166,255,0.35);
  }

  .divider {
    border: none;
    border-top: 1px solid var(--border);
    margin: 4px 0;
  }

  /* Actions */
  .form-actions {
    display: flex;
    gap: 12px;
    align-items: center;
  }

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
    box-shadow: 0 8px 24px rgba(88,166,255,0.3);
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

  .btn-cancel:hover {
    border-color: var(--text);
    color: var(--text);
  }

  /* Danger zone */
  .danger-zone {
    margin-top: 8px;
    border: 1px solid rgba(248,81,73,0.2);
    border-radius: 10px;
    overflow: hidden;
    animation: fadeUp 0.5s 0.2s ease both;
  }

  .danger-header {
    background: rgba(248,81,73,0.07);
    padding: 12px 20px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--red);
    border-bottom: 1px solid rgba(248,81,73,0.2);
  }

  .danger-body {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }

  .danger-body p {
    font-size: 13px;
    color: var(--muted);
  }

  .btn-danger {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    border-radius: 7px;
    border: 1px solid rgba(248,81,73,0.35);
    background: rgba(248,81,73,0.08);
    color: var(--red);
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.2s;
  }

  .btn-danger:hover {
    background: rgba(248,81,73,0.18);
    border-color: var(--red);
  }

  /* Animations */
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
    .danger-body { flex-direction: column; align-items: flex-start; }
  }
</style>
</head>
<body>

<div class="container">

  <a href="index.php" class="back-link">Kembali ke Inventaris</a>

  <div class="page-tag">Inventaris Barang</div>
  <h1>Edit Barang</h1>
  <p class="subtitle">Perbarui informasi barang yang sudah tersimpan.</p>

  <div class="id-badge">ID Barang: <span>#<?= str_pad($id, 3, '0', STR_PAD_LEFT) ?></span></div>

  <div class="card">
    <div class="card-header">
      <div class="card-header-left">
        <span class="card-header-dot"></span>
        <span class="card-header-title"><?= htmlspecialchars($barang['nama_barang']) ?></span>
      </div>
      <span class="edit-badge">Mode Edit</span>
    </div>
    <div class="card-body">

      <?php if($error): ?>
      <div class="alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="">

        <div style="display:flex;flex-direction:column;gap:20px;">

          <div class="form-group">
            <label for="nama_barang">Nama Barang <span>*</span></label>
            <input type="text" id="nama_barang" name="nama_barang"
              placeholder="Nama barang"
              value="<?= htmlspecialchars($val['nama_barang']) ?>" required>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="jumlah">Jumlah <span>*</span></label>
              <input type="number" id="jumlah" name="jumlah"
                placeholder="0" min="1"
                value="<?= htmlspecialchars($val['jumlah']) ?>" required>
            </div>
            <div class="form-group">
              <label for="harga">Harga Satuan (Rp) <span>*</span></label>
              <input type="number" id="harga" name="harga"
                placeholder="0" min="0" step="1"
                value="<?= htmlspecialchars($val['harga']) ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label for="tanggal_masuk">Tanggal Masuk <span>*</span></label>
            <input type="date" id="tanggal_masuk" name="tanggal_masuk"
              value="<?= htmlspecialchars($val['tanggal_masuk']) ?>" required>
          </div>

          <hr class="divider">

          <div class="form-actions">
            <a href="index.php" class="btn-cancel">Batal</a>
            <button type="submit" class="btn-submit">✓ Simpan Perubahan</button>
          </div>

        </div>

      </form>
    </div>
  </div>

  <!-- Danger Zone -->
  <div class="danger-zone">
    <div class="danger-header">⚠ Zona Berbahaya</div>
    <div class="danger-body">
      <p>Hapus barang ini secara permanen dari inventaris. Tindakan ini tidak dapat dibatalkan.</p>
      <a href="hapus.php?id=<?= $id ?>" class="btn-danger"
         onclick="return confirm('Yakin ingin menghapus barang ini secara permanen?')">
        ✕ Hapus Barang
      </a>
    </div>
  </div>

</div>

</body>
</html>