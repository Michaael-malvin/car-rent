<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('admin');

// build optional filter SQL for admin peminjaman list
function buildAdminFilterSql($conn) {
    $clauses = [];
    if(!empty($_GET['filter_peminjam'])) {
        $val = mysqli_real_escape_string($conn, $_GET['filter_peminjam']);
        $clauses[] = "u.nama LIKE '%$val%'";
    }
    if(!empty($_GET['filter_alat'])) {
        $val = mysqli_real_escape_string($conn, $_GET['filter_alat']);
        $clauses[] = "a.nama_alat LIKE '%$val%'";
    }
    if(!empty($_GET['filter_status'])) {
        $val = mysqli_real_escape_string($conn, $_GET['filter_status']);
        $clauses[] = "p.status='$val'";
    }
    if(!empty($_GET['filter_tgl_pinjam'])) {
        $val = mysqli_real_escape_string($conn, $_GET['filter_tgl_pinjam']);
        $clauses[] = "p.tanggal_pinjam='$val'";
    }
    if(!empty($_GET['filter_tgl_kembali'])) {
        $val = mysqli_real_escape_string($conn, $_GET['filter_tgl_kembali']);
        $clauses[] = "p.tanggal_kembali='$val'";
    }
    return count($clauses) ? ' AND ' . implode(' AND ', $clauses) : '';
}

$filter_sql = buildAdminFilterSql($conn);

// handle CSV export
if (isset($_GET['download_csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Data_Peminjaman_' . date('Ymd_His') . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Peminjam','Alat','Jumlah','Tgl Pinjam','Tgl Kembali','Status']);
    $q = mysqli_query($conn, "
        SELECT p.jumlah, p.tanggal_pinjam, p.tanggal_kembali, p.status, 
               u.nama AS nama_peminjam, a.nama_alat 
        FROM peminjaman p
        JOIN `user` u ON p.id_user = u.id_user
        JOIN alat a ON p.id_alat = a.id_alat
        WHERE 1=1 $filter_sql
        ORDER BY p.tanggal_pinjam DESC
    ");
    while($p = mysqli_fetch_assoc($q)){
        fputcsv($output, [
            $p['nama_peminjam'],
            $p['nama_alat'],
            $p['jumlah'],
            $p['tanggal_pinjam'],
            $p['tanggal_kembali'],
            ucfirst($p['status'])
        ]);
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Peminjaman - Sistem Peminjaman Alat</title>
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
                    <a href="user.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-users mr-2"></i>Users
                    </a>
                    <a href="alat.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-boxes mr-2"></i>Alat
                    </a>
                    <a href="kategori.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-tags mr-2"></i>Kategori
                    </a>
                    <a href="peminjaman.php" class="text-accent font-medium border-b-2 border-primary pb-1">
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
                <a href="peminjaman.php" class="text-accent font-medium flex items-center space-x-2">
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
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-dark mb-2">Data Peminjaman</h2>
            <p class="text-gray-600">Lihat semua data peminjaman alat dalam sistem</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Menunggu Approval</p>
                        <p class="text-3xl font-bold text-dark mt-1">
                            <?php 
                            $count_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='menunggu'");
                            $count = mysqli_fetch_assoc($count_q);
                            echo $count['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-full">
                        <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Disetujui</p>
                        <p class="text-3xl font-bold text-dark mt-1">
                            <?php 
                            $approved_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='disetujui'");
                            $approved = mysqli_fetch_assoc($approved_q);
                            echo $approved['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Sedang Dipinjam</p>
                        <p class="text-3xl font-bold text-dark mt-1">
                            <?php 
                            $active_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='disetujui'");
                            $active = mysqli_fetch_assoc($active_q);
                            echo $active['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-hand-holding text-blue-500 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-gray-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Selesai</p>
                        <p class="text-3xl font-bold text-dark mt-1">
                            <?php 
                            $completed_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='selesai'");
                            $completed = mysqli_fetch_assoc($completed_q);
                            echo $completed['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-gray-100 p-4 rounded-full">
                        <i class="fas fa-flag-checkered text-gray-500 text-2xl"></i>
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

        <!-- Search and Export -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Cari peminjam atau alat..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex gap-2">
                    <?php
                        // build export URL keeping existing filters/search
                        $params = $_GET;
                        $params['download_csv'] = 1;
                        $export_url = 'peminjaman.php?' . http_build_query($params);
                    ?>
                    <a href="<?= htmlspecialchars($export_url) ?>" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors flex items-center space-x-2">
                        <i class="fas fa-download"></i>
                        <span>Export</span>
                    </a>
                    <button id="refresh-btn" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors flex items-center space-x-2">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Loans List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-dark">Daftar Peminjaman</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peminjam</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Pinjam</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Kembali</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $q = mysqli_query($conn,"
                            SELECT p.id_pinjam, p.jumlah, p.tanggal_pinjam, p.tanggal_kembali, p.status, 
                                   u.nama AS nama_peminjam, a.nama_alat 
                            FROM peminjaman p
                            JOIN `user` u ON p.id_user = u.id_user
                            JOIN alat a ON p.id_alat = a.id_alat
                            WHERE 1=1 $filter_sql
                            ORDER BY p.tanggal_pinjam DESC
                        ");

                        if(mysqli_num_rows($q) > 0) {
                            while($p = mysqli_fetch_assoc($q)){
                                $nama_peminjam = isset($p['nama_peminjam']) ? $p['nama_peminjam'] : 'N/A';
                                $nama_alat = isset($p['nama_alat']) ? $p['nama_alat'] : 'N/A';
                                $jumlah = isset($p['jumlah']) ? $p['jumlah'] : '0';
                                $tanggal_pinjam = isset($p['tanggal_pinjam']) ? $p['tanggal_pinjam'] : '-';
                                $tanggal_kembali = isset($p['tanggal_kembali']) ? $p['tanggal_kembali'] : '-';
                                $status = isset($p['status']) ? ucfirst($p['status']) : '-';
                                
                                // Status configuration
                                $status_badge = '';
                                $status_color = '';
                                $status_icon = '';
                                
                                if ($status === 'Menunggu') {
                                    $status_badge = 'bg-yellow-100 text-yellow-800';
                                    $status_icon = 'fas fa-clock';
                                } elseif ($status === 'Disetujui') {
                                    $status_badge = 'bg-green-100 text-green-800';
                                    $status_icon = 'fas fa-check-circle';
                                } elseif ($status === 'Selesai') {
                                    $status_badge = 'bg-gray-100 text-gray-800';
                                    $status_icon = 'fas fa-flag-checkered';
                                }
                        ?>
                        <tr class="loan-row" data-peminjam="<?= strtolower($nama_peminjam) ?>" data-alat="<?= strtolower($nama_alat) ?>" data-status="<?= strtolower($status) ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-gray-500"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($nama_peminjam) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($nama_alat) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $jumlah ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $tanggal_pinjam ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $tanggal_kembali ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_badge ?>">
                                    <i class="<?= $status_icon ?> mr-1"></i><?= $status ?>
                                </span>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo '<tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data peminjaman.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
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

        // Filter functionality
        const filterButtons = document.querySelectorAll('[id^="filter-"]');
        const rows = document.querySelectorAll('.loan-row');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update button styles
                filterButtons.forEach(btn => {
                    btn.classList.remove('bg-primary', 'text-dark');
                    btn.classList.add('text-gray-600');
                });
                
                this.classList.remove('text-gray-600');
                this.classList.add('bg-primary', 'text-dark');
                
                // Filter rows
                const filter = this.id.replace('filter-', '');
                
                rows.forEach(row => {
                    if (filter === 'all' || row.dataset.status === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Search functionality
        document.getElementById('search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            rows.forEach(row => {
                const peminjam = row.getAttribute('data-peminjam');
                const alat = row.getAttribute('data-alat');
                
                if (peminjam.includes(searchTerm) || alat.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
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