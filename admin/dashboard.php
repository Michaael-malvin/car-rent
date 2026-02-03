<?php
include '../config/auth.php';
cekRole('admin');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">
    <h2>Dashboard Admin</h2>
    <p>Selamat datang, <b><?= $_SESSION['nama']; ?></b></p>

    <div class="card">
        <ul>
            <li><a href="user.php">Kelola User</a></li>
            <li><a href="alat.php">Kelola Alat</a></li>
            <li><a href="kategori.php">Kelola Kategori</a></li>
            <li><a href="peminjaman.php">Data Peminjaman</a></li>
            <li><a href="pengembalian.php">Data Pengembalian</a></li>
            <li><a href="log.php">Log Aktivitas</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </div>
</div>

</body>
</html>
