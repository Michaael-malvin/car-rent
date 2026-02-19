<?php
session_start();

// kalau sudah login, arahkan sesuai role
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];

    switch ($role) {
        case 'admin':
            header("Location: admin/dashboard.php");
            exit;

        case 'petugas':
            header("Location: petugas/dashboard.php");
            exit;

        case 'peminjam':
            header("Location: peminjam/dashboard.php");
            exit;

        default:
            session_destroy();
            header("Location: auth/login.php");
            exit;
    }
}

// kalau belum login
header("Location: auth/login.php");
exit;
