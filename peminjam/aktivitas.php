<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('peminjam');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivitas Saya - Sistem Peminjaman Alat</title>
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
        /* Custom CSS untuk footer yang tetap di bawah */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        main {
            flex: 1;
            padding-bottom: 3rem; /* Memberikan ruang untuk footer yang tipis */
        }
        
        footer {
            background-color: #1a1a1a;
            color: white;
            padding: 1.5rem 0; /* Footer yang lebih tipis */
            margin-top: auto; /* Memastikan footer berada di bawah */
            border-top: 1px solid #333333; /* Border tipis untuk elegansi */
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            main {
                padding-bottom: 2.5rem;
            }
            footer {
                padding: 1rem 0;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header Navigation -->
    <header class="bg-white shadow-md sticky top-0 z-40">
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
                    <a href="daftar_alat.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-box mr-2"></i>Daftar Alat
                    </a>
                    <a href="ajukan.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-plus-circle mr-2"></i>Ajukan
                    </a>
                    <a href="pengembalian.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-undo mr-2"></i>Pengembalian Alat
                    </a>
                    <a href="aktivitas.php" class="text-accent font-medium border-b-2 border-primary pb-1">
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
                <a href="dashboard.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
                <a href="daftar_alat.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-box"></i>
                    <span>Daftar Alat</span>
                </a>
                <a href="pengembalian.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-undo"></i>
                    <span>Pengembalian</span>
                </a>
                <a href="aktivitas.php" class="text-accent font-medium flex items-center space-x-2">
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
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-dark mb-2">Aktivitas Peminjaman Saya</h2>
            <p class="text-gray-600">Lihat riwayat dan status peminjaman alat Anda</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-primary">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Peminjaman</p>
                        <p class="text-2xl font-bold text-dark mt-1">
                            <?php 
                            $count_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE id_user='$_SESSION[id_user]'");
                            $count = mysqli_fetch_assoc($count_q);
                            echo $count['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-primary bg-opacity-20 p-4 rounded-full">
                        <i class="fas fa-chart-line text-primary text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-accent">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Sedang Dipinjam</p>
                        <p class="text-2xl font-bold text-dark mt-1">
                            <?php 
                            $active_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE id_user='$_SESSION[id_user]' AND status='disetujui'");
                            $active = mysqli_fetch_assoc($active_q);
                            echo $active['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-accent bg-opacity-20 p-4 rounded-full">
                        <i class="fas fa-hand-holding text-accent text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Denda</p>
                        <p class="text-2xl font-bold text-dark mt-1">
                            <?php 
                            $denda_q = mysqli_query($conn, "SELECT SUM(p.denda + p.denda_kerusakan) as total FROM pengembalian p JOIN peminjaman pm ON p.id_pinjam = pm.id_pinjam WHERE pm.id_user='$_SESSION[id_user]'");
                            $denda = mysqli_fetch_assoc($denda_q);
                            echo 'Rp.' . number_format($denda['total'] ?? 0, 0, ',', '.');
                            ?>
                        </p>
                    </div>
                    <div class="bg-red-100 p-4 rounded-full">
                        <i class="fas fa-money-bill-wave text-red-500 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-xl shadow-md p-2 mb-6 inline-flex">
            <button id="filter-all" class="px-4 py-2 rounded-lg font-medium text-sm bg-primary text-dark transition-colors">
                <i class="fas fa-list mr-2"></i>Semua
            </button>
            <button id="filter-menunggu" class="px-4 py-2 rounded-lg font-medium text-sm text-gray-600 hover:text-dark transition-colors">
                <i class="fas fa-clock mr-2"></i>Menunggu
            </button>
            <button id="filter-disetujui" class="px-4 py-2 rounded-lg font-medium text-sm text-gray-600 hover:text-dark transition-colors">
                <i class="fas fa-check-circle mr-2"></i>Disetujui
            </button>
            <button id="filter-selesai" class="px-4 py-2 rounded-lg font-medium text-sm text-gray-600 hover:text-dark transition-colors">
                <i class="fas fa-flag-checkered mr-2"></i>Selesai
            </button>
        </div>

        <!-- Activities List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <?php
            $q = mysqli_query($conn, "
                SELECT p.id_pinjam, p.id_alat, p.jumlah, p.tanggal_pinjam, p.tanggal_kembali, p.status,
                       a.nama_alat, k.denda, k.denda_kerusakan, k.terlambat, k.kondisi
                FROM peminjaman p
                JOIN alat a ON p.id_alat = a.id_alat
                LEFT JOIN pengembalian k ON p.id_pinjam = k.id_pinjam
                WHERE p.id_user = '$_SESSION[id_user]'
                ORDER BY p.tanggal_pinjam DESC
            ");

            if (mysqli_num_rows($q) > 0) {
                echo '<div class="p-6">';
                echo '<div class="space-y-4">';
                
                while($row = mysqli_fetch_assoc($q)) {
                    $denda_terlambat = intval($row['denda']) ?? 0;
                    $denda_kerusakan = intval($row['denda_kerusakan']) ?? 0;
                    $total_denda = $denda_terlambat + $denda_kerusakan;
                    
                    // Status configuration
                    $status_badge = '';
                    $status_color = '';
                    $status_icon = '';
                    
                    if ($row['status'] === 'menunggu') {
                        $status_badge = 'Menunggu Approval';
                        $status_color = 'bg-yellow-100 text-yellow-800';
                        $status_icon = 'fas fa-clock';
                    } elseif ($row['status'] === 'disetujui') {
                        $status_badge = 'Sedang Dipinjam';
                        $status_color = 'bg-blue-100 text-blue-800';
                        $status_icon = 'fas fa-check-circle';
                    } elseif ($row['status'] === 'selesai') {
                        $status_badge = 'Selesai';
                        $status_color = 'bg-green-100 text-green-800';
                        $status_icon = 'fas fa-flag-checkered';
                    }
                    
                    // Check for overdue
                    $today = strtotime(date('Y-m-d'));
                    $due_date = strtotime($row['tanggal_kembali']);
                    $is_overdue = ($today > $due_date && $row['status'] === 'disetujui');
                    
                    // Card styling based on status
                    $card_class = 'border rounded-lg p-4 transition-all hover:shadow-md';
                    if ($is_overdue) {
                        $card_class .= ' border-red-200 bg-red-50';
                    } else {
                        $card_class .= ' border-gray-200';
                    }
                    
                    echo '<div class="' . $card_class . ' activity-card" data-status="' . $row['status'] . '">';
                    
                    // Card Header
                    echo '<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">';
                    echo '<div class="flex items-center mb-2 md:mb-0">';
                    echo '<div class="w-12 h-12 bg-primary bg-opacity-20 rounded-full flex items-center justify-center mr-3">';
                    echo '<i class="fas fa-tools text-primary"></i>';
                    echo '</div>';
                    echo '<div>';
                    echo '<h4 class="font-semibold text-lg text-dark">' . htmlspecialchars($row['nama_alat']) . '</h4>';
                    echo '<p class="text-sm text-gray-600">ID Peminjaman: ' . $row['id_pinjam'] . '</p>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<div class="flex items-center">';
                    echo '<span class="' . $status_color . ' px-3 py-1 rounded-full text-xs font-medium flex items-center">';
                    echo '<i class="' . $status_icon . ' mr-1"></i>' . $status_badge;
                    echo '</span>';
                    echo '</div>';
                    
                    echo '</div>';
                    
                    // Card Body
                    echo '<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">';
                    
                    echo '<div>';
                    echo '<p class="text-xs text-gray-500">Tanggal Pinjam</p>';
                    echo '<p class="font-medium text-sm">' . $row['tanggal_pinjam'] . '</p>';
                    echo '</div>';
                    
                    echo '<div>';
                    echo '<p class="text-xs text-gray-500">Tanggal Kembali</p>';
                    echo '<p class="font-medium text-sm">' . $row['tanggal_kembali'] . '</p>';
                    if ($is_overdue) {
                        echo '<p class="text-xs text-red-600 mt-1"><i class="fas fa-exclamation-triangle mr-1"></i>Terlambat</p>';
                    }
                    echo '</div>';
                    
                    echo '<div>';
                    echo '<p class="text-xs text-gray-500">Jumlah</p>';
                    echo '<p class="font-medium text-sm">' . $row['jumlah'] . '</p>';
                    echo '</div>';
                    
                    echo '<div>';
                    echo '<p class="text-xs text-gray-500">Total Denda</p>';
                    echo '<p class="font-medium text-sm ' . ($total_denda > 0 ? 'text-red-600' : '') . '">';
                    echo $total_denda > 0 ? 'Rp.' . number_format($total_denda, 0, ',', '.') : '-';
                    echo '</p>';
                    echo '</div>';
                    
                    echo '</div>';
                    
                    // Denda Details (only show if there are fines)
                    if ($total_denda > 0) {
                        echo '<div class="border-t pt-3 mt-3">';
                        echo '<div class="grid grid-cols-2 gap-4 text-sm">';
                        
                        if ($denda_terlambat > 0) {
                            echo '<div>';
                            echo '<p class="text-xs text-gray-500">Denda Keterlambatan</p>';
                            echo '<p class="font-medium">Rp.' . number_format($denda_terlambat, 0, ',', '.') . '</p>';
                            echo '</div>';
                        }
                        
                        if ($denda_kerusakan > 0) {
                            echo '<div>';
                            echo '<p class="text-xs text-gray-500">Denda Kerusakan</p>';
                            echo '<p class="font-medium">Rp.' . number_format($denda_kerusakan, 0, ',', '.') . '</p>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
                
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="p-12 text-center">';
                echo '<i class="fas fa-history text-gray-300 text-5xl mb-4"></i>';
                echo '<h3 class="text-xl font-medium text-gray-700 mb-2">Tidak Ada Riwayat Peminjaman</h3>';
                echo '<p class="text-gray-500 mb-6">Anda belum pernah meminjam alat apa pun.</p>';
                echo '<a href="daftar_alat.php" class="bg-accent text-white px-4 py-2 rounded-lg hover:bg-secondary transition-colors inline-flex items-center">';
                echo '<i class="fas fa-plus mr-2"></i>Pinjam Alat';
                echo '</a>';
                echo '</div>';
            }
            ?>
        </div>

        <!-- Back Button -->
        <div class="mt-8">
            <a href="dashboard.php" class="inline-flex items-center text-accent hover:text-secondary transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>
    </main>

    <!-- Footer yang Tipis dan Elegan -->
    <footer class="bg-dark text-white">
        <div class="container mx-auto px-4 py-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <div class="bg-primary p-2 rounded-lg">
                        <i class="fas fa-tools text-dark text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold">Sistem Peminjaman Alat</h3>
                </div>
                <div class="flex items-center space-x-4 text-sm text-gray-400">
                    <span>&copy; 2026 Sistem Peminjaman Alat. All rights reserved.</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Filter functionality
        const filterButtons = document.querySelectorAll('[id^="filter-"]');
        const activityCards = document.querySelectorAll('.activity-card');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update button styles
                filterButtons.forEach(btn => {
                    btn.classList.remove('bg-primary', 'text-dark');
                    btn.classList.add('text-gray-600');
                });
                
                this.classList.remove('text-gray-600');
                this.classList.add('bg-primary', 'text-dark');
                
                // Filter cards
                const filter = this.id.replace('filter-', '');
                
                activityCards.forEach(card => {
                    if (filter === 'all' || card.dataset.status === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>