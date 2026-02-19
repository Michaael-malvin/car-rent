<?php
include '../config/auth.php';
cekRole('admin');
include '../config/database.php';

// Stats untuk dashboard
 $total_users = 0;
 $total_tools = 0;
 $total_categories = 0;
 $total_loans = 0;
 $active_loans = 0;
 $pending_approvals = 0;
 $total_returns = 0;
 $overdue_loans = 0;

// Total Users
 $uq = mysqli_query($conn, "SELECT COUNT(*) as total FROM user");
if ($uq) { $u = mysqli_fetch_assoc($uq); $total_users = (int)$u['total']; }

// Total Tools
 $tq = mysqli_query($conn, "SELECT COUNT(*) as total FROM alat");
if ($tq) { $t = mysqli_fetch_assoc($tq); $total_tools = (int)$t['total']; }

// Total Categories
 $cq = mysqli_query($conn, "SELECT COUNT(*) as total FROM kategori");
if ($cq) { $c = mysqli_fetch_assoc($cq); $total_categories = (int)$c['total']; }

// Total Loans
 $lq = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman");
if ($lq) { $l = mysqli_fetch_assoc($lq); $total_loans = (int)$l['total']; }

// Active Loans
 $aq = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='disetujui'");
if ($aq) { $a = mysqli_fetch_assoc($aq); $active_loans = (int)$a['total']; }

// Pending Approvals
 $pq = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='menunggu'");
if ($pq) { $p = mysqli_fetch_assoc($pq); $pending_approvals = (int)$p['total']; }

// Total Returns
 $rq = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengembalian");
if ($rq) { $r = mysqli_fetch_assoc($rq); $total_returns = (int)$r['total']; }

// Overdue Loans
 $oq = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='disetujui' AND tanggal_kembali < CURDATE()");
if ($oq) { $o = mysqli_fetch_assoc($oq); $overdue_loans = (int)$o['total']; }

// Recent activities
 $recent_activities = [];
 $activities_query = mysqli_query($conn, "
    SELECT la.aktivitas, la.waktu, u.nama 
    FROM log_aktivitas la 
    JOIN user u ON la.id_user = u.id_user 
    ORDER BY la.waktu DESC 
    LIMIT 5
");
if ($activities_query) {
    while($row = mysqli_fetch_assoc($activities_query)) {
        $recent_activities[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Peminjaman Alat</title>
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
                    <a href="user.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-users mr-2"></i>Users
                    </a>
                    <a href="alat.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-boxes mr-2"></i>Alat
                    </a>
                    <a href="kategori.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-tags mr-2"></i>Kategori
                    </a>
                    <a href="peminjaman.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-exchange-alt mr-2"></i>Peminjaman
                    </a>
                </div>
                
                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex items-center space-x-2 text-sm">
                        <div class="w-8 h-8 bg-accent rounded-full flex items-center justify-center">
                            <i class="fas fa-user-shield text-white"></i>
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
                <a href="user.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="alat.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-boxes"></i>
                    <span>Alat</span>
                </a>
                <a href="kategori.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-tags"></i>
                    <span>Kategori</span>
                </a>
                <a href="peminjaman.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Peminjaman</span>
                </a>
                <a href="pengembalian.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-undo"></i>
                    <span>Pengembalian</span>
                </a>
                <a href="log.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-history"></i>
                    <span>Log Aktivitas</span>
                </a>
                <div class="border-t pt-3 flex items-center space-x-2">
                    <div class="w-8 h-8 bg-accent rounded-full flex items-center justify-center">
                        <i class="fas fa-user-shield text-white"></i>
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
                        <p class="text-blue-100">Dashboard Administrasi Sistem Peminjaman Alat</p>
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-md p-6 card-hover border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Users</p>
                            <p class="text-3xl font-bold text-dark mt-1"><?= $total_users; ?></p>
                            <p class="text-xs text-gray-400 mt-2">Terdaftar</p>
                        </div>
                        <div class="bg-blue-100 p-4 rounded-full">
                            <i class="fas fa-users text-blue-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 card-hover border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Alat</p>
                            <p class="text-3xl font-bold text-dark mt-1"><?= $total_tools; ?></p>
                            <p class="text-xs text-gray-400 mt-2"><?= $total_categories; ?> Kategori</p>
                        </div>
                        <div class="bg-green-100 p-4 rounded-full">
                            <i class="fas fa-boxes text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 card-hover border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Peminjaman</p>
                            <p class="text-3xl font-bold text-dark mt-1"><?= $total_loans; ?></p>
                            <p class="text-xs text-gray-400 mt-2"><?= $active_loans; ?> Aktif</p>
                        </div>
                        <div class="bg-purple-100 p-4 rounded-full">
                            <i class="fas fa-exchange-alt text-purple-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 card-hover border-l-4 border-orange-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Pengembalian</p>
                            <p class="text-3xl font-bold text-dark mt-1"><?= $total_returns; ?></p>
                            <p class="text-xs text-gray-400 mt-2"><?= $overdue_loans; ?> Terlambat</p>
                        </div>
                        <div class="bg-orange-100 p-4 rounded-full">
                            <i class="fas fa-undo text-orange-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="mb-8">
            <h3 class="text-xl font-bold text-dark mb-6">Manajemen Sistem</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="user.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all group">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-blue-100 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-users text-blue-500 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-dark">Kelola User</h4>
                        <p class="text-sm text-gray-500 mt-2">Manajemen pengguna</p>
                    </div>
                </a>
                
                <a href="alat.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all group">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-green-100 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-boxes text-green-500 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-dark">Kelola Alat</h4>
                        <p class="text-sm text-gray-500 mt-2">Inventaris alat</p>
                    </div>
                </a>
                
                <a href="kategori.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all group">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-purple-100 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-tags text-purple-500 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-dark">Kelola Kategori</h4>
                        <p class="text-sm text-gray-500 mt-2">Kategori alat</p>
                    </div>
                </a>
                
                <a href="log.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all group">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-orange-100 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-history text-orange-500 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-dark">Log Aktivitas</h4>
                        <p class="text-sm text-gray-500 mt-2">Riwayat sistem</p>
                    </div>
                </a>
            </div>
        </section>

        <!-- Recent Activities -->
        <section>
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-dark">Aktivitas Terkini</h3>
                    <a href="log.php" class="text-sm text-accent hover:text-secondary transition-colors">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php if (!empty($recent_activities)): ?>
                            <?php foreach($recent_activities as $activity): ?>
                                <div class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg transition-colors">
                                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-dark"><?= htmlspecialchars($activity['nama']) ?></p>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($activity['aktivitas']) ?></p>
                                    </div>
                                    <div class="text-sm text-gray-400 flex-shrink-0">
                                        <p><?= date('d M H:i', strtotime($activity['waktu'])) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-8 text-center">
                                <i class="fas fa-history text-gray-300 text-5xl mb-4"></i>
                                <h3 class="text-xl font-medium text-gray-700 mb-2">Tidak Ada Aktivitas</h3>
                                <p class="text-gray-500">Belum ada aktivitas sistem yang tercatat.</p>
                            </div>
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