<?php
include '../config/auth.php';
cekRole('peminjam');
// koneksi database
include '../config/database.php';

// ambil id user dari session
$id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null;

// inisialisasi nilai default
$alat_dipinjam = 0;
$pengembalian_today = 0;
$total_peminjaman = 0;
$activities = [];

if ($id_user) {
    // Alat Dipinjam: peminjaman yang sudah disetujui dan belum dikembalikan
    $q = "SELECT COUNT(*) as cnt FROM peminjaman p LEFT JOIN pengembalian k ON p.id_pinjam=k.id_pinjam WHERE p.id_user='".mysqli_real_escape_string($conn, $id_user)."' AND p.status='disetujui' AND k.id_kembali IS NULL";
    $res = mysqli_query($conn, $q);
    if ($res) {
        $r = mysqli_fetch_assoc($res);
        $alat_dipinjam = (int)$r['cnt'];
    }

    // Pengembalian hari ini untuk user ini
    $q2 = "SELECT COUNT(*) as cnt FROM pengembalian k JOIN peminjaman p ON k.id_pinjam=p.id_pinjam WHERE p.id_user='".mysqli_real_escape_string($conn, $id_user)."' AND DATE(k.tanggal_dikembalikan)=CURDATE()";
    $res2 = mysqli_query($conn, $q2);
    if ($res2) {
        $r2 = mysqli_fetch_assoc($res2);
        $pengembalian_today = (int)$r2['cnt'];
    }

    // Total peminjaman oleh user
    $q3 = "SELECT COUNT(*) as cnt FROM peminjaman WHERE id_user='".mysqli_real_escape_string($conn, $id_user)."'";
    $res3 = mysqli_query($conn, $q3);
    if ($res3) {
        $r3 = mysqli_fetch_assoc($res3);
        $total_peminjaman = (int)$r3['cnt'];
    }

    // Ambil aktivitas terkini dari peminjaman (status: menunggu, disetujui, selesai)
    $q4 = "SELECT p.id_pinjam, p.status, a.nama_alat, p.tanggal_pinjam, p.tanggal_kembali, k.id_kembali, k.tanggal_dikembalikan FROM peminjaman p JOIN alat a ON p.id_alat=a.id_alat LEFT JOIN pengembalian k ON p.id_pinjam=k.id_pinjam WHERE p.id_user='".mysqli_real_escape_string($conn, $id_user)."' ORDER BY p.tanggal_pinjam DESC LIMIT 5";
    $res4 = mysqli_query($conn, $q4);
    if ($res4) {
        while ($row = mysqli_fetch_assoc($res4)) {
            $activities[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Peminjam - Sistem Peminjaman Alat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FFD700', // kuning
                        secondary: '#0033A0', // biru tua
                        accent: '#0066CC', // biru muda
                        dark: '#1a1a1a', // hitam pekat
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header Navigation -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <nav class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="bg-primary p-2 rounded-lg">
                        <i class="fas fa-tools text-dark text-xl"></i>
                    </div>
                    <h1 class="text-xl font-bold text-dark">Sistem Peminjaman Alat</h1>
                </div>
                
                <div class="hidden md:flex items-center space-x-6">
                    <a href="dashboard.php" class="text-accent font-medium border-b-2 border-primary pb-1">
                        <i class="fas fa-home mr-2"></i>Beranda
                    </a>
                    <a href="daftar_alat.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-box mr-2"></i>Daftar Alat
                    </a>
                    <a href="ajukan.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-plus-circle mr-2"></i>Ajukan Peminjaman
                    </a>
                    <a href="pengembalian.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-undo mr-2"></i>Pengembalian Alat
                    </a>
                    <a href="aktivitas.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-history mr-2"></i>Aktivitas
                    </a>
                </div>
                
                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex items-center space-x-2 text-sm">
                        <div class="w-8 h-8 bg-accent rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <span class="font-medium text-dark"><?= $_SESSION['nama']; ?></span>
                    </div>
                    <a href="../auth/logout.php" class="hidden md:flex items-center text-red-600 hover:text-red-700 transition-colors">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                    <button id="mobile-menu-button" class="md:hidden text-dark">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </nav>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
            <div class="container mx-auto px-4 py-3 flex flex-col space-y-3">
                <a href="dashboard.php" class="text-accent font-medium flex items-center space-x-2">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
                <a href="daftar_alat.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-box"></i>
                    <span>Daftar Alat</span>
                </a>
                <a href="ajukan.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-plus-circle"></i>
                    <span>Ajukan Peminjaman</span>
                </a>
                <a href="pengembalian.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-undo"></i>
                    <span>Pengembalian Alat</span>
                </a>
                <a href="aktivitas.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-history"></i>
                    <span>Aktivitas</span>
                </a>
                <div class="border-t pt-3 flex items-center space-x-2">
                    <div class="w-8 h-8 bg-accent rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <span class="font-medium text-dark"><?= $_SESSION['nama']; ?></span>
                </div>
                <a href="../auth/logout.php" class="text-red-600 hover:text-red-700 transition-colors flex items-center space-x-2">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <section class="mb-8">
            <div class="bg-gradient-to-r from-accent to-secondary rounded-2xl p-8 text-white shadow-lg">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-3xl font-bold mb-2">Selamat Datang, <?= $_SESSION['nama']; ?>!</h2>
                        <p class="text-blue-100">Apa yang ingin Anda lakukan hari ini?</p>
                    </div>
                    <div class="bg-white bg-opacity-20 p-4 rounded-xl backdrop-blur-sm">
                        <p class="text-sm text-blue-100">Tanggal</p>
                        <p class="text-xl font-bold"><?= date('d F Y'); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Cards -->
        <section class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-md p-6 card-hover border-l-4 border-primary">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Alat Dipinjam</p>
                            <p class="text-3xl font-bold text-dark mt-1"><?= $alat_dipinjam; ?></p>
                            <p class="text-xs text-gray-400 mt-2">Aktif saat ini</p>
                        </div>
                        <div class="bg-primary bg-opacity-20 p-4 rounded-full">
                            <i class="fas fa-hand-holding text-primary text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 card-hover border-l-4 border-accent">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Pengembalian</p>
                            <p class="text-3xl font-bold text-dark mt-1"><?= $pengembalian_today; ?></p>
                            <p class="text-xs text-gray-400 mt-2">Hari ini</p>
                        </div>
                        <div class="bg-accent bg-opacity-20 p-4 rounded-full">
                            <i class="fas fa-undo text-accent text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 card-hover border-l-4 border-secondary">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Peminjaman</p>
                            <p class="text-3xl font-bold text-dark mt-1"><?= $total_peminjaman; ?></p>
                            <p class="text-xs text-gray-400 mt-2">Semua waktu</p>
                        </div>
                        <div class="bg-secondary bg-opacity-20 p-4 rounded-full">
                            <i class="fas fa-chart-line text-secondary text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="mb-8">
            <h3 class="text-xl font-bold text-dark mb-6">Aksi Cepat</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="daftar_alat.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all group">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-primary bg-opacity-20 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-box-open text-primary text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-dark">Lihat Daftar Alat</h4>
                        <p class="text-sm text-gray-500 mt-2">Jelajahi alat yang tersedia</p>
                    </div>
                </a>
                
                <a href="ajukan.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all group">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-accent bg-opacity-20 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-plus-circle text-accent text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-dark">Ajukan Peminjaman</h4>
                        <p class="text-sm text-gray-500 mt-2">Pinjam alat yang dibutuhkan</p>
                    </div>
                </a>
                
                <a href="pengembalian.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all group">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-secondary bg-opacity-20 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-clipboard-check text-secondary text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-dark">Pengembalian Alat</h4>
                        <p class="text-sm text-gray-500 mt-2">Kembalikan alat yang dipinjam</p>
                    </div>
                </a>
                
                <a href="aktivitas.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all group">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-purple-100 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-history text-purple-600 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-dark">Aktivitas Saya</h4>
                        <p class="text-sm text-gray-500 mt-2">Lihat riwayat peminjaman</p>
                    </div>
                </a>
            </div>
        </section>

        <!-- Recent Activity -->
        <section>
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-dark">Aktivitas Terkini</h3>
                    <a href="aktivitas.php" class="text-sm text-accent hover:text-secondary transition-colors">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php if (!empty($activities)): ?>
                            <?php foreach ($activities as $act):
                                $nama_alat = htmlspecialchars($act['nama_alat']);
                                $status = $act['status'];
                                $tgl_pinjam = date('d M Y', strtotime($act['tanggal_pinjam']));
                                $tgl_kembali = date('d M Y', strtotime($act['tanggal_kembali']));
                                
                                // tentukan icon, warna, dan label berdasarkan status
                                $icon = 'fa-info-circle';
                                $bg = 'bg-gray-100';
                                $txt = 'text-gray-600';
                                $status_label = 'Pending';
                                
                                if ($status === 'menunggu') {
                                    $icon = 'fa-hourglass-half';
                                    $bg = 'bg-yellow-100';
                                    $txt = 'text-yellow-600';
                                    $status_label = 'Menunggu Persetujuan';
                                } elseif ($status === 'disetujui') {
                                    if ($act['id_kembali'] === null) {
                                        // sedang digunakan
                                        $icon = 'fa-hand-holding';
                                        $bg = 'bg-blue-100';
                                        $txt = 'text-blue-600';
                                        $status_label = 'Sedang Digunakan';
                                    }
                                } elseif ($status === 'selesai' || $act['id_kembali'] !== null) {
                                    // sudah dikembalikan
                                    $icon = 'fa-check-circle';
                                    $bg = 'bg-green-100';
                                    $txt = 'text-green-600';
                                    $status_label = 'Sudah Dikembalikan';
                                }
                            ?>
                                <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 <?= $bg; ?> rounded-full flex items-center justify-center">
                                            <i class="fas <?= $icon; ?> <?= $txt; ?>"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-dark"><?= $status_label; ?></p>
                                            <p class="text-sm text-gray-500"><?= $nama_alat; ?> (<?= $tgl_pinjam; ?> - <?= $tgl_kembali; ?>)</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-3 text-sm text-gray-500">Belum ada aktivitas peminjaman.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-12">
        <div class="container mx-auto px-4 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <div class="bg-primary p-2 rounded-lg">
                        <i class="fas fa-tools text-dark text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold">Sistem Peminjaman Alat</h3>
                </div>
                <p class="text-gray-400 text-sm">&copy;2026 Sistem Peminjaman Alat.All rights reserved</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Add subtle animations to cards
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.card-hover').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>