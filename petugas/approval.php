<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('petugas');

if (isset($_GET['approve'])) {
    $id_pinjam = $_GET['approve'];

    // ambil data peminjaman
    $q = mysqli_query($conn,"SELECT * FROM peminjaman WHERE id_pinjam='$id_pinjam'");
    $p = mysqli_fetch_assoc($q);

    $id_alat = $p['id_alat'];
    $jumlah = $p['jumlah'];

    // cek stok alat
    $cek = mysqli_query($conn,"SELECT stok FROM alat WHERE id_alat='$id_alat'");
    $a = mysqli_fetch_assoc($cek);

    // Validasi: stok harus >= jumlah yang diminta
    if ($a['stok'] >= $jumlah) {
        // kurangi stok sesuai jumlah yang dipinjam
        mysqli_query($conn,"
            UPDATE alat 
            SET stok = stok - $jumlah
            WHERE id_alat='$id_alat'
        ");

        // set status peminjaman
        mysqli_query($conn,"
            UPDATE peminjaman 
            SET status='disetujui' 
            WHERE id_pinjam='$id_pinjam'
        ");
        
        mysqli_query($conn,"INSERT INTO log_aktivitas (id_user, aktivitas) VALUES ('$_SESSION[id_user]', 'Petugas menyetujui peminjaman alat')");

        $_SESSION['success_msg'] = "Peminjaman disetujui & stok berkurang $jumlah";
        header("Location: approval.php");
        exit;
    } else {
        $_SESSION['error_msg'] = "Stok tidak cukup! Stok tersedia: " . $a['stok'];
        header("Location: approval.php");
        exit;
    }
}

if (isset($_GET['reject'])) {
    $id_pinjam = $_GET['reject'];
    
    mysqli_query($conn,"
        UPDATE peminjaman 
        SET status='selesai' 
        WHERE id_pinjam='$id_pinjam'
    ");
    
    mysqli_query($conn,"INSERT INTO log_aktivitas (id_user, aktivitas) VALUES ('$_SESSION[id_user]', 'Petugas menolak peminjaman alat')");

    $_SESSION['success_msg'] = "Peminjaman berhasil ditolak";
    header("Location: approval.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Peminjaman - Sistem Peminjaman Alat</title>
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
                    <a href="approval.php" class="text-accent font-medium border-b-2 border-primary pb-1">
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
                <a href="dashboard.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
                <a href="approval.php" class="text-accent font-medium flex items-center space-x-2">
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
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-dark mb-2">Approval Peminjaman Alat</h2>
            <p class="text-gray-600">Kelola persetujuan pengajuan peminjaman alat</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if(isset($_SESSION['success_msg'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <span><?= $_SESSION['success_msg']; ?></span>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['error_msg'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <span><?= $_SESSION['error_msg']; ?></span>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Cari peminjam atau alat..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button id="filter-btn" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors flex items-center space-x-2">
                        <i class="fas fa-filter"></i>
                        <span>Filter</span>
                    </button>
                    <button id="refresh-btn" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors flex items-center space-x-2">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Pending Requests -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-dark">Pengajuan Menunggu Persetujuan</h3>
                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                    <i class="fas fa-clock mr-1"></i>
                    <?php 
                    $count_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='menunggu'");
                    $count = mysqli_fetch_assoc($count_q);
                    echo $count['total'] . ' Pengajuan';
                    ?>
                </span>
            </div>
            
            <?php
            $q = mysqli_query($conn,"
                SELECT p.id_pinjam, p.id_user, p.id_alat, p.jumlah, p.tanggal_pinjam, p.tanggal_kembali, p.status,
                       u.nama as nama_peminjam, a.nama_alat, a.stok
                FROM peminjaman p
                JOIN user u ON p.id_user = u.id_user
                JOIN alat a ON p.id_alat = a.id_alat
                WHERE p.status='menunggu'
                ORDER BY p.id_pinjam DESC
            ");

            if(mysqli_num_rows($q) > 0) {
                echo '<div class="p-6">';
                echo '<div class="space-y-4">';
                
                while($p = mysqli_fetch_assoc($q)){
                    // Check stok
                    $is_stock_sufficient = $p['stok'] >= $p['jumlah'];
                    $stock_percentage = $p['stok'] > 0 ? ($p['jumlah'] / $p['stok']) * 100 : 100;
                    
                    // Card styling based on stock status
                    $card_class = 'border rounded-lg p-4 transition-all hover:shadow-md';
                    if (!$is_stock_sufficient) {
                        $card_class .= ' border-red-200 bg-red-50';
                    } else {
                        $card_class .= ' border-gray-200';
                    }
                    
                    echo '<div class="' . $card_class . ' request-card" data-peminjam="' . strtolower($p['nama_peminjam']) . '" data-alat="' . strtolower($p['nama_alat']) . '">';
                    
                    // Card Header
                    echo '<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">';
                    echo '<div class="flex items-center mb-2 md:mb-0">';
                    echo '<div class="w-12 h-12 bg-primary bg-opacity-20 rounded-full flex items-center justify-center mr-3">';
                    echo '<i class="fas fa-user text-primary"></i>';
                    echo '</div>';
                    echo '<div>';
                    echo '<h4 class="font-semibold text-lg text-dark">' . htmlspecialchars($p['nama_peminjam']) . '</h4>';
                    echo '<p class="text-sm text-gray-600">ID Peminjaman: ' . $p['id_pinjam'] . '</p>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<div class="flex items-center">';
                    echo '<span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-medium flex items-center">';
                    echo '<i class="fas fa-clock mr-1"></i>Menunggu Persetujuan';
                    echo '</span>';
                    echo '</div>';
                    
                    echo '</div>';
                    
                    // Card Body
                    echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">';
                    
                    echo '<div>';
                    echo '<p class="text-xs text-gray-500">Alat yang Diminta</p>';
                    echo '<p class="font-medium">' . htmlspecialchars($p['nama_alat']) . '</p>';
                    echo '</div>';
                    
                    echo '<div>';
                    echo '<p class="text-xs text-gray-500">Jumlah</p>';
                    echo '<p class="font-medium">' . $p['jumlah'] . ' unit</p>';
                    echo '</div>';
                    
                    echo '<div>';
                    echo '<p class="text-xs text-gray-500">Tanggal Pinjam</p>';
                    echo '<p class="font-medium">' . $p['tanggal_pinjam'] . '</p>';
                    echo '</div>';
                    
                    echo '<div>';
                    echo '<p class="text-xs text-gray-500">Tanggal Kembali</p>';
                    echo '<p class="font-medium">' . $p['tanggal_kembali'] . '</p>';
                    echo '</div>';
                    
                    echo '</div>';
                    
                    // Stock Status
                    echo '<div class="border-t pt-3 mt-3">';
                    echo '<div class="flex items-center justify-between mb-2">';
                    echo '<p class="text-sm font-medium">Status Stok</p>';
                    echo '<span class="text-sm ' . ($is_stock_sufficient ? 'text-green-600' : 'text-red-600') . '">';
                    echo $is_stock_sufficient ? '✓ Cukup' : '✗ Tidak Cukup';
                    echo '</span>';
                    echo '</div>';
                    
                    echo '<div class="w-full bg-gray-200 rounded-full h-2.5">';
                    echo '<div class="h-2.5 rounded-full ' . ($is_stock_sufficient ? 'bg-green-600' : 'bg-red-600') . '" style="width: ' . min($stock_percentage, 100) . '%"></div>';
                    echo '</div>';
                    
                    echo '<p class="text-xs text-gray-500 mt-1">Stok tersedia: ' . $p['stok'] . ' unit, Diminta: ' . $p['jumlah'] . ' unit</p>';
                    echo '</div>';
                    
                    // Action Buttons
                    echo '<div class="border-t pt-3 mt-3 flex justify-end space-x-2">';
                    if ($is_stock_sufficient) {
                        echo '<a href="?approve=' . $p['id_pinjam'] . '" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center" onclick="return confirm(\'Approve peminjaman ini?\')">';
                        echo '<i class="fas fa-check mr-2"></i>Approve';
                        echo '</a>';
                    } else {
                        echo '<button disabled class="bg-gray-300 text-gray-500 px-4 py-2 rounded-lg cursor-not-allowed font-medium flex items-center">';
                        echo '<i class="fas fa-check mr-2"></i>Approve';
                        echo '</button>';
                    }
                    
                    echo '<a href="?reject=' . $p['id_pinjam'] . '" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors font-medium flex items-center" onclick="return confirm(\'Tolak peminjaman ini?\')">';
                    echo '<i class="fas fa-times mr-2"></i>Tolak';
                    echo '</a>';
                    echo '</div>';
                    
                    echo '</div>';
                }
                
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="p-12 text-center">';
                echo '<i class="fas fa-clipboard-check text-gray-300 text-5xl mb-4"></i>';
                echo '<h3 class="text-xl font-medium text-gray-700 mb-2">Tidak Ada Pengajuan Menunggu</h3>';
                echo '<p class="text-gray-500">Semua pengajuan peminjaman telah diproses.</p>';
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

    <!-- Footer yang Tetap di Bawah -->
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
                    <span class="hidden md:inline">|</span>
                    <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                    <span class="hidden md:inline">|</span>
                    <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                </div>
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
            const cards = document.querySelectorAll('.request-card');
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
    </script>
</body>
</html>