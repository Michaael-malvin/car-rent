<?php
include '../config/auth.php';
cekRole('petugas');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Petugas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">
    <h2>Dashboard Petugas</h2>
    <p>Selamat bekerja, <b><?= $_SESSION['nama']; ?></b></p>

    <div class="card">
        <ul>
            <li><a href="approval.php">Menyetujui Peminjaman</a></li>
            <li><a href="monitoring.php">Memantau Pengembalian</a></li>
            <li><a href="laporan.php">Cetak Laporan</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </div>
</div>

</body>
</html>
