<?php
session_start();

// Proteksi halaman - cek sesi login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

$stmt = $pdo->query("SELECT * FROM barang");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalBarang = count($data);
$totalStok   = array_sum(array_column($data, 'jumlah'));
$totalNilai  = array_sum(array_map(fn($r) => $r['harga'] * $r['jumlah'], $data));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inventaris Barang</title>
<link rel="stylesheet" href="style.css">
<style>
    .topbar-right {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .welcome-text {
        font-size: 13px;
        color: #888;
    }
    .welcome-text span {
        font-weight: 600;
        color: #c9a84c;
    }
    .btn-logout {
        padding: 8px 16px;
        background: transparent;
        border: 1px solid #c0392b;
        color: #c0392b;
        border-radius: 4px;
        font-size: 13px;
        text-decoration: none;
        transition: background 0.2s, color 0.2s;
    }
    .btn-logout:hover {
        background: #c0392b;
        color: #fff;
    }

    /* ── Kolom Foto ── */
    .foto-thumb {
        width: 52px;
        height: 52px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #30363d;
        display: block;
    }
    .foto-placeholder {
        width: 52px;
        height: 52px;
        border-radius: 8px;
        background: #1c2333;
        border: 1px dashed #30363d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #555;
    }
</style>
</head>
<body>

<div class="container">

  <div class="topbar">
    <div class="brand">
      <div class="brand-icon">📦</div>
      <div class="brand-text">
        <h1>Inventaris Barang</h1>
        <p>Sistem Manajemen Stok & Gudang</p>
      </div>
    </div>

    <div class="topbar-right">
      <div class="welcome-text">
        Selamat datang, <span><?= htmlspecialchars($_SESSION['username']) ?></span>
      </div>
      <a href="tambah.php" class="btn-add">Tambah Barang</a>
      <a href="logout.php" class="btn-logout">Logout</a>
    </div>
  </div>

  <div class="stats">
    <div class="stat">
      <div class="stat-label">Jenis Barang</div>
      <div class="stat-value"><?= $totalBarang ?></div>
      <div class="stat-sub">item terdaftar</div>
    </div>
    <div class="stat">
      <div class="stat-label">Total Stok</div>
      <div class="stat-value"><?= number_format($totalStok,0,',','.') ?></div>
      <div class="stat-sub">unit tersedia</div>
    </div>
    <div class="stat">
      <div class="stat-label">Total Nilai</div>
      <div class="stat-value">Rp <?= number_format($totalNilai,0,',','.') ?></div>
      <div class="stat-sub">nilai inventaris</div>
    </div>
  </div>

  <div class="card">
    <div class="card-head">
      <div class="card-title">
        <span class="dot"></span>
        Daftar Inventaris
      </div>
      <span class="badge"><?= $totalBarang ?> data</span>
    </div>

    <?php if(empty($data)): ?>
      <div class="empty">📭 Belum ada data barang</div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Foto</th>
            <th>Nama Barang</th>
            <th>Jumlah</th>
            <th>Harga</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($data as $row): ?>
          <tr>
            <td>#<?= $row['id'] ?></td>

            <!-- ── Kolom Foto ── -->
            <td>
              <?php
                // Cek kolom thumb dulu, kalau tidak ada pakai kolom gambar
                $foto = $row['thumb'] ?? $row['gambar'] ?? null;
              ?>
              <?php if (!empty($foto) && file_exists(__DIR__ . '/' . $foto)): ?>
                <img src="<?= htmlspecialchars($foto) ?>"
                     alt="<?= htmlspecialchars($row['nama_barang']) ?>"
                     class="foto-thumb">
              <?php else: ?>
                <div class="foto-placeholder">📦</div>
              <?php endif; ?>
            </td>

            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
            <td><?= number_format($row['jumlah']) ?></td>
            <td>Rp <?= number_format($row['harga']) ?></td>
            <td><?= date('d M Y', strtotime($row['tanggal_masuk'])) ?></td>
            <td>
              <a href="edit.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
              <a href="hapus.php?id=<?= $row['id'] ?>" class="btn-del"
                 onclick="return confirm('Yakin hapus data?')">Hapus</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="footer"></div>

</div>
</body>
</html>