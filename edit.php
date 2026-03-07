<?php
include 'koneksi.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM barang WHERE id=?");
$stmt->execute([$id]);
$data = $stmt->fetch();
?>

<h2>Edit Barang</h2>

<form method="POST">

<input type="hidden" name="id" value="<?= $data['id'] ?>">

Nama Barang <br>
<input type="text" name="nama_barang" value="<?= $data['nama_barang'] ?>"><br><br>

Jumlah <br>
<input type="number" name="jumlah" value="<?= $data['jumlah'] ?>"><br><br>

Harga <br>
<input type="number" name="harga" value="<?= $data['harga'] ?>"><br><br>

Tanggal Masuk <br>
<input type="date" name="tanggal_masuk" value="<?= $data['tanggal_masuk'] ?>"><br><br>

<button type="submit" name="update">Update</button>

</form>

<?php

if(isset($_POST['update'])){

$id = $_POST['id'];
$nama = $_POST['nama_barang'];
$jumlah = $_POST['jumlah'];
$harga = $_POST['harga'];
$tanggal = $_POST['tanggal_masuk'];

$sql = "UPDATE barang 
        SET nama_barang=?, jumlah=?, harga=?, tanggal_masuk=? 
        WHERE id=?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$nama,$jumlah,$harga,$tanggal,$id]);

header("Location:index.php");

}

?>