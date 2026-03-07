<?php
include 'koneksi.php';

$stmt = $pdo->query("SELECT * FROM barang");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>Inventaris Barang</title>
</head>
<body>

<h2>Data Inventaris</h2>

<a href="tambah.php">Tambah Barang</a>

<table border="1" cellpadding="10">
<tr>
<th>ID</th>
<th>Nama Barang</th>
<th>Jumlah</th>
<th>Harga</th>
<th>Tanggal Masuk</th>
<th>Aksi</th>
</tr>

<?php foreach($data as $row): ?>

<tr>
<td><?= $row['id'] ?></td>
<td><?= $row['nama_barang'] ?></td>
<td><?= $row['jumlah'] ?></td>
<td><?= $row['harga'] ?></td>
<td><?= $row['tanggal_masuk'] ?></td>
<td>
<a href="edit.php?id=<?= $row['id'] ?>">Edit</a>
<a href="hapus.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus data?')">Hapus</a>
</td>
</tr>

<?php endforeach; ?>

</table>

</body>
</html>