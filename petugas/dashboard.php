<?php
include '../config/auth.php';
cekRole('petugas');
include '../config/database.php';

// Stats untuk dashboard
 $waiting_count = 0;
 $ongoing_count = 0;
 $returns_today = 0;
 $late_count = 0;
 $activities = [];

// Menunggu Approval
 $cq = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='menunggu'");
if ($cq) { $c = mysqli_fetch_assoc($cq); $waiting_count = (int)$c['total']; }

// Sedang Dipinjam (disetujui & belum dikembalikan)
 $oq = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman p LEFT JOIN pengembalian k ON p.id_pinjam=k.id_pinjam WHERE p.status='disetujui' AND k.id_kembali IS NULL");
if ($oq) { $o = mysqli_fetch_assoc($oq); $ongoing_count = (int)$o['total']; }

// Pengembalian Hari Ini
 $rq = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengembalian WHERE DATE(tanggal_dikembalikan)=CURDATE()");
if ($rq) { $r = mysqli_fetch_assoc($rq); $returns_today = (int)$r['total']; }

// Keterlambatan (disetujui & tanggal_kembali < hari ini)
 $lq = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='disetujui' AND tanggal_kembali < CURDATE()");
if ($lq) { $l = mysqli_fetch_assoc($lq); $late_count = (int)$l['total']; }

// Ambil aktivitas: hanya peminjaman yang sudah disetujui (on going) dan pengembalian
 $combined = [];

 $q1 = mysqli_query($conn, "SELECT 'peminjaman' as type, p.id_pinjam as id, p.status, p.tanggal_pinjam as waktu, u.nama as user, a.nama_alat, p.jumlah FROM peminjaman p JOIN user u ON p.id_user=u.id_user JOIN alat a ON p.id_alat=a.id_alat WHERE p.status='disetujui' ORDER BY p.tanggal_pinjam DESC LIMIT 6");
if ($q1) {
    while($row = mysqli_fetch_assoc($q1)) {
        $row['timestamp'] = strtotime($row['waktu']);
        $combined[] = $row;
    }
}

 $q2 = mysqli_query($conn, "SELECT 'pengembalian' as type, k.id_kembali as id, NULL as status, k.tanggal_dikembalikan as waktu, u.nama as user, a.nama_alat, p.jumlah, k.denda, k.denda_kerusakan, k.terlambat FROM pengembalian k JOIN peminjaman p ON k.id_pinjam=p.id_pinjam JOIN user u ON p.id_user=u.id_user JOIN alat a ON p.id_alat=a.id_alat ORDER BY k.tanggal_dikembalikan DESC LIMIT 6");
if ($q2) {
    while($row = mysqli_fetch_assoc($q2)) {
        $row['timestamp'] = strtotime($row['waktu']);
        $combined[] = $row;
    }
}

// sort combined by timestamp desc and take top 6
usort($combined, function($a,$b){ return $b['timestamp'] <=> $a['timestamp']; });
 $activities = array_slice($combined, 0, 6);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - Sistem Peminjaman Alat</title>
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
                    <a href="approval.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-check-circle mr-2"></i>Approval Peminjaman
                    </a>
                    <a href="monitoring.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-eye mr-2"></i>Monitoring
                    </a>
                    <a href="laporan.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-file-alt mr-2"></i>Laporan
                    </a>
                </div>
                
                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex items-center space-x-2 text-sm">
                        <div class="w-8 h-8 bg-accent rounded-full flex items-center justify-center">
                            <i class="fas fa-user-tie text-white"></i>
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
                <a href="approval.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-check-circle"></i>
                    <span>Approval Peminjaman</span>
                </a>
                <a href="monitoring.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-eye"></i>
                    <span>Monitoring</span>
                </a>
                <a href="laporan.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-file-alt"></i>
                    <span>Laporan</span>
                </a>
                <div class="border-t pt-3 flex items-center space-x-2">
                    <div class="w-8 h-8 bg-accent rounded-full flex items-center justify-center">
                        <i class="fas fa-user-tie text-white"></i>
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
                        <h2 class="text-3xl font-bold mb-2">Selamat Bekerja, <?= $_SESSION['nama']; ?>!</h2>
                        <p class="text-blue-100">Dashboard Manajemen Peminjaman Alat</p>
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-md p-6 card-hover border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Menunggu Approval</p>
                            <p class="text-3xl font-bold text-dark mt-1"><?= $waiting_count; ?></p>
                            <p class="text-xs text-gray-400 mt-2">Perlu ditinjau</p>
                        </div>
                        <div class="bg-yellow-100 p-4 rounded-full">
                            <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 card-hover border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Sedang Dipinjam</p>
                            <p class="text-3xl font-bold text-dark mt-1"><?= $ongoing_count; ?></p>
                            <p class="text-xs text-gray-400 mt-2">Aktif saat ini</p>
                        </div>
                        <div class="bg-blue-100 p-4 rounded-full">
                            <i class="fas fa-hand-holding text-blue-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 card-hover border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Pengembalian Hari Ini</p>
                            <p class="text-3xl font-bold text-dark mt-1"><?= $returns_today; ?></p>
                            <p class="text-xs text-gray-400 mt-2">Perlu diproses</p>
                        </div>
                        <div class="bg-green-100 p-4 rounded-full">
                            <i class="fas fa-undo text-green-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 card-hover border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Keterlambatan</p>
                            <p class="text-3xl font-bold text-dark mt-1"><?= $late_count; ?></p>
                            <p class="text-xs text-gray-400 mt-2">Perlu perhatian</p>
                        </div>
                        <div class="bg-red-100 p-4 rounded-full">
                            <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="mb-8">
            <h3 class="text-xl font-bold text-dark mb-6">Aksi Cepat</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="approval.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all group">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-yellow-100 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-check-circle text-yellow-500 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-dark">Approval Peminjaman</h4>
                        <p class="text-sm text-gray-500 mt-2">Setujui/tolak pengajuan</p>
                    </div>
                </a>
                
                <a href="monitoring.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all group">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-blue-100 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-eye text-blue-500 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-dark">Monitoring</h4>
                        <p class="text-sm text-gray-500 mt-2">Pantau pengembalian</p>
                    </div>
                </a>
                
                <a href="laporan.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all group">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-green-100 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-file-alt text-green-500 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-dark">Cetak Laporan</h4>
                        <p class="text-sm text-gray-500 mt-2">Generate laporan</p>
                    </div>
                </a>
                
                <a href="confirm_return.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-all group">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-purple-100 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-tools text-purple-500 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-dark">Konfirmasi Pengembalian</h4>
                        <p class="text-sm text-gray-500 mt-2">Manajemen inventaris</p>
                    </div>
                </a>
            </div>
        </section>

        <!-- Recent Activities -->
        <section>
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-dark">Aktivitas Terkini</h3>
                    <a href="approval.php" class="text-sm text-accent hover:text-secondary transition-colors">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php if (!empty($activities)): ?>
                            <?php foreach($activities as $act):
                                $type = $act['type'];
                                $time = isset($act['waktu']) ? date('d M Y H:i', strtotime($act['waktu'])) : '';
                                $user = isset($act['user']) ? htmlspecialchars($act['user']) : '';
                                $alat = isset($act['nama_alat']) ? htmlspecialchars($act['nama_alat']) : '';
                                $jumlah = isset($act['jumlah']) ? $act['jumlah'] : '';
                                $denda = isset($act['denda']) ? (int)$act['denda'] : 0;
                                $denda_kerusakan = isset($act['denda_kerusakan']) ? (int)$act['denda_kerusakan'] : 0;
                                $terlambat = isset($act['terlambat']) ? (int)$act['terlambat'] : 0;

                                // desain mirip peminjam: icon + label + detail
                                if ($type === 'peminjaman') {
                                    $icon='fa-hand-holding'; 
                                    $bg='bg-blue-100'; 
                                    $txt='text-blue-600';
                                    $status_label = 'Alat Dipinjam';
                                    $detail = $alat . ' - ' . $user . ' (' . $jumlah . ' unit)';
                                } else { // pengembalian
                                    $icon='fa-undo'; 
                                    $bg='bg-green-100'; 
                                    $txt='text-green-600';
                                    $status_label = 'Alat Dikembalikan';
                                    $detail = $alat . ' - ' . $user . ' (' . $jumlah . ' unit)';
                                    $denda_text = '';
                                    if ($denda > 0 || $denda_kerusakan > 0 || $terlambat) {
                                        $parts = [];
                                        if ($terlambat) $parts[] = 'Terlambat';
                                        if ($denda > 0) $parts[] = 'Denda: Rp.' . number_format($denda,0,',','.');
                                        if ($denda_kerusakan > 0) $parts[] = 'Denda Kerusakan: Rp.' . number_format($denda_kerusakan,0,',','.');
                                        $denda_text = ' — ' . implode(' / ', $parts);
                                    }
                                }
                            ?>
                                <div class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg transition-colors">
                                    <div class="w-10 h-10 <?= $bg; ?> rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas <?= $icon; ?> <?= $txt; ?>"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-dark"><?= $status_label ?></p>
                                        <p class="text-sm text-gray-500"><?= $detail ?><?php if(isset($denda_text)) echo $denda_text; ?></p>
                                    </div>
                                    <div class="text-sm text-gray-400 flex-shrink-0">
                                        <p><?= $time ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-8 text-center">
                                <i class="fas fa-history text-gray-300 text-5xl mb-4"></i>
                                <h3 class="text-xl font-medium text-gray-700 mb-2">Tidak Ada Aktivitas</h3>
                                <p class="text-gray-500">Belum ada aktivitas peminjaman atau pengembalian.</p>
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