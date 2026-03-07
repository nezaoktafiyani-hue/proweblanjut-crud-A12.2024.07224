<?php
include 'koneksi.php';

$nama_barang = $_POST['nama_barang'];
$jumlah = $_POST['jumlah'];
$harga = $_POST['harga'];
$tanggal = $_POST['tanggal_masuk'];

$sql = "INSERT INTO barang (nama_barang,jumlah,harga,tanggal_masuk)
        VALUES (?,?,?,?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([$nama_barang,$jumlah,$harga,$tanggal]);

header("Location:index.php");
?>