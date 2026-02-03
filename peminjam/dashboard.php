<?php
include '../config/auth.php';
cekRole('peminjam');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Peminjam</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">
    <h2>Dashboard Peminjam</h2>
    <p>Halo, <b><?= $_SESSION['nama']; ?></b></p>

    <div class="card">
        <ul>
            <li><a href="daftar_alat.php">Lihat Daftar Alat</a></li>
            <li><a href="ajukan.php">Ajukan Peminjaman</a></li>
            <li><a href="pengembalian.php">Pengembalian Alat</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </div>
</div>

</body>
</html>
