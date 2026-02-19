<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('admin');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - Sistem Peminjaman Alat</title>
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
                <a href="dashboard.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
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
                <a href="log.php" class="text-accent font-medium flex items-center space-x-2">
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
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-dark mb-2">Log Aktivitas</h2>
            <p class="text-gray-600">Riwayat aktivitas yang terjadi dalam sistem</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Aktivitas</p>
                        <p class="text-3xl font-bold text-dark mt-1">
                            <?php 
                            $count_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM log_aktivitas");
                            $count = mysqli_fetch_assoc($count_q);
                            echo $count['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-history text-blue-500 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Hari Ini</p>
                        <p class="text-3xl font-bold text-dark mt-1">
                            <?php 
                            $today_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM log_aktivitas WHERE DATE(waktu) = CURDATE()");
                            $today = mysqli_fetch_assoc($today_q);
                            echo $today['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-calendar-day text-green-500 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Pengguna Aktif</p>
                        <p class="text-3xl font-bold text-dark mt-1">
                            <?php 
                            $users_q = mysqli_query($conn, "SELECT COUNT(DISTINCT id_user) as total FROM log_aktivitas WHERE DATE(waktu) = CURDATE()");
                            $users = mysqli_fetch_assoc($users_q);
                            echo $users['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-purple-100 p-4 rounded-full">
                        <i class="fas fa-users text-purple-500 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Search -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Cari aktivitas atau pengguna..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex gap-2">
                    <input type="date" id="date-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <button id="filter-btn" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors flex items-center space-x-2">
                        <i class="fas fa-filter"></i>
                        <span>Filter</span>
                    </button>
                    <button id="refresh-btn" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors flex items-center space-x-2">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                    <button id="export-btn" class="bg-accent text-white px-4 py-2 rounded-lg hover:bg-secondary transition-colors flex items-center space-x-2">
                        <i class="fas fa-download"></i>
                        <span>Export</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Activity List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-dark">Riwayat Aktivitas</h3>
            </div>
            
            <div class="p-6">
                <div class="space-y-4" id="activities-container">
                    <?php
                    $q = mysqli_query($conn,"
                        SELECT la.id_log, la.waktu, la.aktivitas, u.nama, u.role 
                        FROM log_aktivitas la 
                        JOIN user u ON la.id_user = u.id_user 
                        ORDER BY la.waktu DESC 
                        LIMIT 50
                    ");
                    
                    if(mysqli_num_rows($q) > 0) {
                        while($l = mysqli_fetch_assoc($q)){
                            $waktu = isset($l['waktu']) ? $l['waktu'] : '-';
                            $aktivitas = isset($l['aktivitas']) ? $l['aktivitas'] : '-';
                            $nama = isset($l['nama']) ? $l['nama'] : 'N/A';
                            $role = isset($l['role']) ? $l['role'] : 'N/A';
                            
                            // Determine activity icon and color based on activity type
                            $icon = 'fas fa-info-circle';
                            $icon_color = 'text-gray-500';
                            
                            if (strpos(strtolower($aktivitas), 'login') !== false) {
                                $icon = 'fas fa-sign-in-alt';
                                $icon_color = 'text-blue-500';
                            } elseif (strpos(strtolower($aktivitas), 'logout') !== false) {
                                $icon = 'fas fa-sign-out-alt';
                                $icon_color = 'text-red-500';
                            } elseif (strpos(strtolower($aktivitas), 'peminjaman') !== false) {
                                $icon = 'fas fa-hand-holding';
                                $icon_color = 'text-green-500';
                            } elseif (strpos(strtolower($aktivitas), 'pengembalian') !== false) {
                                $icon = 'fas fa-undo';
                                $icon_color = 'text-orange-500';
                            } elseif (strpos(strtolower($aktivitas), 'hapus') !== false || strpos(strtolower($aktivitas), 'delete') !== false) {
                                $icon = 'fas fa-trash';
                                $icon_color = 'text-red-500';
                            } elseif (strpos(strtolower($aktivitas), 'edit') !== false || strpos(strtolower($aktivitas), 'update') !== false) {
                                $icon = 'fas fa-edit';
                                $icon_color = 'text-purple-500';
                            }
                            
                            // Role badge color
                            $role_color = '';
                            if ($role === 'admin') {
                                $role_color = 'bg-red-100 text-red-800';
                            } elseif ($role === 'petugas') {
                                $role_color = 'bg-blue-100 text-blue-800';
                            } else {
                                $role_color = 'bg-green-100 text-green-800';
                            }
                        ?>
                        <div class="activity-item border rounded-lg p-4 hover:shadow-md transition-all" data-activity="<?= strtolower($aktivitas) ?>" data-user="<?= strtolower($nama) ?>">
                            <div class="flex items-start space-x-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas <?= $icon ?> <?= $icon_color ?>"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center mb-1">
                                        <p class="font-medium text-dark"><?= htmlspecialchars($nama) ?></p>
                                        <span class="ml-2 px-2 py-1 text-xs rounded-full <?= $role_color ?>">
                                            <?= ucfirst($role) ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($aktivitas) ?></p>
                                </div>
                                <div class="text-sm text-gray-400 flex-shrink-0">
                                    <p><?= date('d M H:i', strtotime($waktu)) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php 
                        }
                    } else {
                        echo '<div class="p-8 text-center">';
                        echo '<i class="fas fa-history text-gray-300 text-5xl mb-4"></i>';
                        echo '<h3 class="text-xl font-medium text-gray-700 mb-2">Tidak Ada Aktivitas</h3>';
                        echo '<p class="text-gray-500">Belum ada aktivitas yang tercatat dalam sistem.</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
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

        // Search functionality
        document.getElementById('search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const items = document.querySelectorAll('.activity-item');
            
            items.forEach(item => {
                const activity = item.getAttribute('data-activity');
                const user = item.getAttribute('data-user');
                
                if (activity.includes(searchTerm) || user.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Date filter
        document.getElementById('filter-btn').addEventListener('click', function() {
            const dateValue = document.getElementById('date-filter').value;
            const items = document.querySelectorAll('.activity-item');
            
            if (dateValue) {
                items.forEach(item => {
                    // In a real implementation, you would compare the date with the item's date
                    // For now, we'll just show all items
                    item.style.display = '';
                });
            } else {
                items.forEach(item => {
                    item.style.display = '';
                });
            }
        });

        // Refresh button
        document.getElementById('refresh-btn').addEventListener('click', function() {
            location.reload();
        });

        // Export button
        document.getElementById('export-btn').addEventListener('click', function() {
            // In a real implementation, this would generate and download a CSV or Excel file
            alert('Fitur export akan segera tersedia!');
        });
    </script>
</body>
</html>