<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama_barang']);
    $jumlah  = (int) $_POST['jumlah'];
    $harga   = (float) str_replace(['.', ','], ['', '.'], $_POST['harga']);
    $tanggal = $_POST['tanggal_masuk'];

    if ($nama && $jumlah > 0 && $harga > 0 && $tanggal) {
        $stmt = $pdo->prepare("INSERT INTO barang (nama_barang, jumlah, harga, tanggal_masuk) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $jumlah, $harga, $tanggal]);
        header('Location: index.php');
        exit;
    } else {
        $error = 'Semua field wajib diisi dengan benar.';
    }
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
    top: -200px;
    left: -200px;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(63,185,80,0.06) 0%, transparent 70%);
    pointer-events: none;
    z-index: 0;
  }

  .container {
    position: relative;
    z-index: 1;
    max-width: 640px;
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
    box-shadow: 0 0 0 3px rgba(63,185,80,0.12);
  }
  input::placeholder { color: #4a5568; }
  input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0.5);
    cursor: pointer;
  }

  .divider { border: none; border-top: 1px solid var(--border); margin: 4px 0; }

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

      <?php if ($error): ?>
      <div class="alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div style="display:flex;flex-direction:column;gap:20px;">

          <div class="form-group">
            <label for="nama_barang">Nama Barang <span>*</span></label>
            <input type="text" id="nama_barang" name="nama_barang"
              placeholder="Contoh: Laptop Asus VivoBook"
              value="<?= htmlspecialchars($_POST['nama_barang'] ?? '') ?>" required>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="jumlah">Jumlah <span>*</span></label>
              <input type="number" id="jumlah" name="jumlah"
                placeholder="0" min="1"
                value="<?= htmlspecialchars($_POST['jumlah'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label for="harga">Harga Satuan (Rp) <span>*</span></label>
              <input type="number" id="harga" name="harga"
                placeholder="0" min="0" step="1"
                value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label for="tanggal_masuk">Tanggal Masuk <span>*</span></label>
            <input type="date" id="tanggal_masuk" name="tanggal_masuk"
              value="<?= htmlspecialchars($_POST['tanggal_masuk'] ?? date('Y-m-d')) ?>" required>
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

</body>
</html>