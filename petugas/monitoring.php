<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('petugas');

// (Konfirmasi pengembalian kini dilakukan via confirm_return.php)
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Pengembalian - Sistem Peminjaman Alat</title>
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
                    <a href="dashboard.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-home mr-2"></i>Beranda
                    </a>
                    <a href="approval.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-check-circle mr-2"></i>Approval Peminjaman
                    </a>
                    <a href="monitoring.php" class="text-accent font-medium border-b-2 border-primary pb-1">
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
                <a href="dashboard.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
                <a href="approval.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-check-circle"></i>
                    <span>Approval Peminjaman</span>
                </a>
                <a href="monitoring.php" class="text-accent font-medium flex items-center space-x-2">
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
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-dark mb-2">Monitoring Pengembalian Alat</h2>
            <p class="text-gray-600">Pantau dan konfirmasi pengembalian alat</p>
        </div>

        <!-- Success Messages -->
        <?php if(isset($_SESSION['success_msg'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <span><?= $_SESSION['success_msg']; ?></span>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-xl shadow-md p-2 mb-6 inline-flex">
            <?php
            $filter = $_GET['filter'] ?? 'all';
            $all_active = ($filter === 'all') ? 'bg-primary text-dark' : 'text-gray-600';
            $dipinjam_active = ($filter === 'dipinjam') ? 'bg-primary text-dark' : 'text-gray-600';
            $dikembalikan_active = ($filter === 'dikembalikan') ? 'bg-primary text-dark' : 'text-gray-600';
            ?>
            
            <a href="?filter=all" class="px-4 py-2 rounded-lg font-medium text-sm <?= $all_active ?> transition-colors">
                <i class="fas fa-list mr-2"></i>Semua
            </a>
            <a href="?filter=dipinjam" class="px-4 py-2 rounded-lg font-medium text-sm <?= $dipinjam_active ?> transition-colors">
                <i class="fas fa-hand-holding mr-2"></i>Sedang Dipinjam
            </a>
            <a href="?filter=dikembalikan" class="px-4 py-2 rounded-lg font-medium text-sm <?= $dikembalikan_active ?> transition-colors">
                <i class="fas fa-undo mr-2"></i>Dikembalikan
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Sedang Dipinjam</p>
                        <p class="text-2xl font-bold text-dark mt-1">
                            <?php 
                            $count_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='disetujui'");
                            $count = mysqli_fetch_assoc($count_q);
                            echo $count['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-hand-holding text-blue-500 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Menunggu Konfirmasi</p>
                        <p class="text-2xl font-bold text-dark mt-1">
                            <?php 
                            $confirm_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman p JOIN pengembalian k ON p.id_pinjam=k.id_pinjam WHERE p.status='disetujui' AND k.konfirmasi_kerusakan=0");
                            $confirm = mysqli_fetch_assoc($confirm_q);
                            echo $confirm['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-clock text-green-500 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Terlambat</p>
                        <p class="text-2xl font-bold text-dark mt-1">
                            <?php 
                            $today = date('Y-m-d');
                            $overdue_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='disetujui' AND tanggal_kembali < '$today'");
                            $overdue = mysqli_fetch_assoc($overdue_q);
                            echo $overdue['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-red-100 p-4 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monitoring List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-dark">Daftar Peminjaman</h3>
                <div class="flex items-center space-x-2">
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Cari peminjam atau alat..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    <button id="refresh-btn" class="bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <div class="space-y-4" id="monitoring-list">
                    <?php
                    $filter = $_GET['filter'] ?? 'all';
                    $where = "";

                    if($filter === 'dipinjam') {
                        $where = "AND p.status='disetujui'";
                    } elseif($filter === 'dikembalikan') {
                        $where = "AND p.status='selesai'";
                    }

                    $q = mysqli_query($conn,"
                            SELECT p.id_pinjam, p.id_user, p.id_alat, p.jumlah, p.tanggal_pinjam, p.tanggal_kembali, p.status,
                                k.tanggal_dikembalikan, k.foto_pengembalian, k.kondisi, k.denda_kerusakan, k.denda, u.nama as nama_peminjam, a.nama_alat
                        FROM peminjaman p
                        JOIN user u ON p.id_user = u.id_user
                        JOIN alat a ON p.id_alat = a.id_alat
                        LEFT JOIN pengembalian k ON p.id_pinjam = k.id_pinjam
                        WHERE p.status IN ('disetujui', 'selesai')
                        $where
                        ORDER BY p.tanggal_pinjam DESC
                    ");

                    if(mysqli_num_rows($q) > 0) {
                        while($m = mysqli_fetch_assoc($q)){
                            // Check if overdue
                            $today = strtotime(date('Y-m-d'));
                            $tanggal_kembali = strtotime($m['tanggal_kembali']);
                            $is_overdue = ($today > $tanggal_kembali && $m['status'] === 'disetujui');
                            
                            // Card styling based on status
                            $card_class = 'border rounded-lg p-4 transition-all hover:shadow-md';
                            if ($is_overdue) {
                                $card_class .= ' border-red-200 bg-red-50';
                            } else {
                                $card_class .= ' border-gray-200';
                            }
                            
                            echo '<div class="' . $card_class . ' monitoring-card" data-peminjam="' . strtolower($m['nama_peminjam']) . '" data-alat="' . strtolower($m['nama_alat']) . '">';
                            
                            // Card Header
                            echo '<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">';
                            echo '<div class="flex items-center mb-2 md:mb-0">';
                            echo '<div class="w-12 h-12 bg-primary bg-opacity-20 rounded-full flex items-center justify-center mr-3">';
                            echo '<i class="fas fa-user text-primary"></i>';
                            echo '</div>';
                            echo '<div>';
                            echo '<h4 class="font-semibold text-lg text-dark">' . htmlspecialchars($m['nama_peminjam']) . '</h4>';
                            echo '<p class="text-sm text-gray-600">ID Peminjaman: ' . $m['id_pinjam'] . '</p>';
                            echo '</div>';
                            echo '</div>';
                            
                            echo '<div class="flex items-center">';
                            if($m['status'] === 'selesai') {
                                echo '<span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium flex items-center">';
                                echo '<i class="fas fa-check-circle mr-1"></i>Selesai';
                                echo '</span>';
                            } else {
                                echo '<span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-medium flex items-center">';
                                echo '<i class="fas fa-hand-holding mr-1"></i>Sedang Dipinjam';
                                echo '</span>';
                                if($is_overdue) {
                                    echo '<span class="ml-2 bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">';
                                    echo '<i class="fas fa-exclamation-triangle mr-1"></i>Terlambat';
                                    echo '</span>';
                                }
                            }
                            echo '</div>';
                            
                            echo '</div>';
                            
                            // Card Body
                            echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">';
                            
                            echo '<div>';
                            echo '<p class="text-xs text-gray-500">Alat yang Dipinjam</p>';
                            echo '<p class="font-medium">' . htmlspecialchars($m['nama_alat']) . '</p>';
                            echo '</div>';
                            
                            echo '<div>';
                            echo '<p class="text-xs text-gray-500">Jumlah</p>';
                            echo '<p class="font-medium">' . $m['jumlah'] . ' unit</p>';
                            echo '</div>';
                            
                            echo '<div>';
                            echo '<p class="text-xs text-gray-500">Tanggal Pinjam</p>';
                            echo '<p class="font-medium">' . $m['tanggal_pinjam'] . '</p>';
                            echo '</div>';
                            
                            echo '<div>';
                            echo '<p class="text-xs text-gray-500">Tanggal Kembali</p>';
                            echo '<p class="font-medium">' . $m['tanggal_kembali'] . '</p>';
                            echo '</div>';
                            
                            echo '</div>';
                            
                            // Return Information
                            if($m['status'] === 'selesai' || $m['tanggal_dikembalikan']) {
                                echo '<div class="border-t pt-3 mt-3">';
                                echo '<h5 class="font-medium text-sm mb-2">Informasi Pengembalian</h5>';
                                echo '<div class="grid grid-cols-1 md:grid-cols-3 gap-4">';
                                
                                echo '<div>';
                                echo '<p class="text-xs text-gray-500">Tanggal Dikembalikan</p>';
                                echo '<p class="font-medium">' . $m['tanggal_dikembalikan'] . '</p>';
                                echo '</div>';
                                
                                echo '<div>';
                                echo '<p class="text-xs text-gray-500">Kondisi</p>';
                                echo '<p class="font-medium">' . (!empty($m['kondisi']) ? htmlspecialchars($m['kondisi']) : '-') . '</p>';
                                echo '</div>';
                                
                                echo '<div>';
                                echo '<p class="text-xs text-gray-500">Foto</p>';
                                if (!empty($m['foto_pengembalian'])) {
                                    echo '<button onclick="showImageModal(\'' . htmlspecialchars($m['foto_pengembalian']) . '\')" class="text-accent hover:text-secondary">';
                                    echo '<i class="fas fa-image mr-1"></i>Lihat Foto';
                                    echo '</button>';
                                } else {
                                    echo '<span class="text-gray-400">-</span>';
                                }
                                echo '</div>';
                                
                                echo '</div>';
                                echo '</div>';
                            }
                            
                            // Action Button
                            echo '<div class="border-t pt-3 mt-3 flex justify-end">';
                            if($m['tanggal_dikembalikan'] && $m['status'] === 'disetujui') {
                                echo '<a href="confirm_return.php?id=' . $m['id_pinjam'] . '" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center">';
                                echo '<i class="fas fa-check mr-2"></i>Konfirmasi Pengembalian';
                                echo '</a>';
                            }
                            echo '</div>';
                            
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="p-12 text-center">';
                        echo '<i class="fas fa-clipboard-list text-gray-300 text-5xl mb-4"></i>';
                        echo '<h3 class="text-xl font-medium text-gray-700 mb-2">Tidak Ada Data Peminjaman</h3>';
                        echo '<p class="text-gray-500">Tidak ada data peminjaman untuk ditampilkan dengan filter yang dipilih.</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-8">
            <a href="dashboard.php" class="inline-flex items-center text-accent hover:text-secondary transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>
    </main>

    <!-- Image Modal -->
    <div id="image-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-auto max-w-lg shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-dark">Foto Pengembalian</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex justify-center">
                <img id="modal-image" src="" alt="Foto Pengembalian" class="max-w-full rounded-lg">
            </div>
        </div>
    </div>

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

        // Search functionality
        document.getElementById('search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.monitoring-card');
            let hasResults = false;

            cards.forEach(card => {
                const peminjam = card.getAttribute('data-peminjam');
                const alat = card.getAttribute('data-alat');
                
                if (peminjam.includes(searchTerm) || alat.includes(searchTerm)) {
                    card.style.display = 'block';
                    hasResults = true;
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Refresh button
        document.getElementById('refresh-btn').addEventListener('click', function() {
            location.reload();
        });

        // Image modal
        function showImageModal(imageSrc) {
            document.getElementById('modal-image').src = '../assets/uploads/' + imageSrc;
            document.getElementById('image-modal').classList.remove('hidden');
        }

        document.getElementById('close-modal').addEventListener('click', function() {
            document.getElementById('image-modal').classList.add('hidden');
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === document.getElementById('image-modal')) {
                document.getElementById('image-modal').classList.add('hidden');
            }
        });
    </script>
</body>
</html>