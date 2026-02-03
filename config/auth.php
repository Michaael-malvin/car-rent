<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: /peminjaman-alat/auth/login.php");
    exit;
}

function cekRole($role) {
    if (is_array($role)) {
        if (!in_array($_SESSION['role'], $role)) {
            aksesDitolak();
        }
    } else {
        if ($_SESSION['role'] !== $role) {
            aksesDitolak();
        }
    }
}

function aksesDitolak() {
    echo "<h2>Akses ditolak!</h2>";
    echo "<p>Kamu tidak punya izin membuka halaman ini.</p>";
    exit;
}
?>
