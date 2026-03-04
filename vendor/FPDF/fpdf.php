<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('admin');

// Fungsi untuk generate PDF
function generatePDF($data) {
    // Generate HTML yang bisa di-print
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Laporan Peminjaman Alat</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 15px; 
                line-height: 1.4;
                color: #333;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #ddd;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
                color: #1a1a1a;
            }
            .header p {
                margin: 5px 0;
                font-size: 14px;
                color: #666;
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 20px;
                font-size: 12px; 
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            th, td { 
                border: 1px solid #ddd; 
                padding: 8px; 
                text-align: left; 
                vertical-align: top;
            }
            th { 
                background-color: #f8f9fa; 
                font-weight: bold;
                color: #495057;
                vertical-align: top;
            }
            .status-approved { 
                background-color: #d4edda; 
                color: #155724; 
            }
            .status-pending { 
                background-color: #fff3cd; 
                color: #856404; 
            }
            .status-completed { 
                background-color: #d1ecf1; 
                color: #0c5460; 
            }
            .footer {
                margin-top: 30px;
                font-size: 10px;
                color: #666;
                text-align: center;
                border-top: 1px solid #eee;
                padding-top: 10px;
            }
            @media print { 
                body { margin: 0; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>LAPORAN PEMINJAMAN ALAT</h1>
            <p>Sistem Peminjaman Alat</p>
            <p>Tanggal: ' . date('d F Y H:i') . '</p>
        </div>
        
        <table>
        <thead>
        <tr>
            <th>No</th>
            <th>Peminjam</th>
            <th>Alat</th>
            <th>Jumlah</th>
            <th>Tgl Pinjam</th>
            <th>Tgl Kembali</th>
            <th>Tgl Dikembalikan</th>
            <th>Status</th>
            <th>Denda</th>
        </tr>
        </thead>
        <tbody>
    <?php
    $no = 1;
    foreach($data as $item) {
        $status_class = '';
        if ($item['status'] == 'Disetujui') {
            $status_class = 'status-approved';
        } elseif ($item['status'] == 'Menunggu') {
            $status_class = 'status-pending';
        } elseif ($item['status'] == 'Selesai') {
            $status_class = 'status-completed';
        }
        
        echo '<tr class="' . $status_class . '">
            <td>' . $no++ . '</td>
            <td>' . htmlspecialchars($item['nama_peminjam']) . '</td>
            <td>' . htmlspecialchars($item['nama_alat']) . '</td>
            <td>' . $item['jumlah'] . '</td>
            <td>' . $item['tanggal_pinjam'] . '</td>
            <td>' . $item['tanggal_kembali'] . '</td>
            <td>' . ($item['tanggal_dikembalikan'] ?? '-') . '</td>
            <td>' . ucfirst($item['status']) . '</td>
            <td>' . ($item['total_denda'] > 0 ? 'Rp. ' . number_format($item['total_denda'], 0, ',', '.') : '-') . '</td>
        </tr>';
    }
    ?>
        </tbody>
        </table>
        
        <div class="footer">
            <p>Dicetak oleh: ' . $_SESSION['nama'] . ' | ' . date('d F Y H:i:s') . '</p>
            <p>Generated: ' . date('d F Y H:i:s') . '</p>
        </div>
    </body>
    </html>
    <?php
    
    return ob_get_clean();
}

// Download PDF function
if (isset($_GET['download_pdf'])) {
    // Ambil data peminjaman
    $q = mysqli_query($conn,"
        SELECT p.id_pinjam, p.jumlah, p.tanggal_pinjam, p.tanggal_kembali, p.status,
               k.tanggal_dikembalikan, k.total_denda, u.nama as nama_peminjam, a.nama_alat
        FROM peminjaman p
        JOIN user u ON p.id_user = u.id_user
        JOIN alat a ON p.id_alat = a.id_alat
        LEFT JOIN pengembalian k ON p.id_pinjam = k.id_pinjam
        ORDER BY p.tanggal_pinjam DESC
    ");
    
    $data = [];
    while($row = mysqli_fetch_assoc($q)) {
        $data[] = $row;
    }
    
    // Generate HTML
    $html = generatePDF($data);
    
    // Output HTML
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="Laporan_Peminjaman_' . date('Ymd_His') . '.html"');
    echo $html;
    exit;
}

// Download CSV function
if (isset($_GET['download_csv'])) {
    // Ambil data peminjaman
    $q = mysqli_query($conn,"
        SELECT p.id_pinjam, p.jumlah, p.tanggal_pinjam, p.tanggal_kembali, p.status,
               k.tanggal_dikembalikan, k.total_denda, u.nama as nama_peminjam, a.nama_alat
        FROM peminjaman p
        JOIN user u ON p.id_user = u.id_user
        JOIN alat a ON p.id_alat = a.id_alat
        LEFT JOIN pengembalian k ON p.id_pinjam = k.id_pinjam
        ORDER BY p.tanggal_pinjam DESC
    ");
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Laporan_Peminjaman_' . date('Ymd_His') . '.csv"');
    
    // Output CSV
    $output = fopen('php://output', 'w');
    
    // Header CSV
    fputcsv($output, ['No', 'Peminjam', 'Alat', 'Jumlah', 'Tgl Pinjam', 'Tgl Kembali', 'Tgl Dikembalikan', 'Status', 'Total Denda']);
    
    // Data CSV
    $no = 1;
    while($row = mysqli_fetch_assoc($q)){
        fputcsv($output, [$no++, $row['nama_peminjam'], $row['nama_alat'], $row['jumlah'], $row['tanggal_pinjam'], $row['tanggal_kembali'], $row['tanggal_dikembalikan'] ?? '-', $row['status'], $row['total_denda']]);
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
    <title>Laporan - Sistem Peminjaman Alat</title>
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
        @media print {
            body * {
                visibility: hidden;
            }
            #print-area, #print-area * {
                visibility: visible;
            }
            #print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header Navigation -->
    <header class="bg-white shadow-md sticky top-0 z-50 no-print">
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
                    <a href="laporan.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-file-alt mr-2"></i>Laporan
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
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t no-print">
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
                <a href="laporan.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-file-alt"></i>
                    <span>Laporan</span>
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
            <h2 class="text-3xl font-bold text-dark mb-2">Laporan Peminjaman Alat</h2>
            <p class="text-gray-600">Generate dan unduh laporan peminjaman alat</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Peminjaman</p>
                        <p class="text-2xl font-bold text-dark mt-1">
                            <?php 
                            $count_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman");
                            $count = mysqli_fetch_assoc($count_q);
                            echo $count['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-chart-line text-blue-500 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Selesai</p>
                        <p class="text-2xl font-bold text-dark mt-1">
                            <?php 
                            $selesai_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='selesai'");
                            $selesai = mysqli_fetch_assoc($selesai_q);
                            echo $selesai['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Menunggu</p>
                        <p class="text-2xl font-bold text-dark mt-1">
                            <?php 
                            $menunggu_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='menunggu'");
                            $menunggu = mysqli_fetch_assoc($menunggu_q);
                            echo $menunggu['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-full">
                        <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Sedang Dipinjam</p>
                        <p class="text-2xl font-bold text-dark mt-1">
                            <?php 
                            $dipinjam_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='disetujui'");
                            $dipinjam = mysqli_fetch_assoc($dipinjam_q);
                            echo $dipinjam['total'];
                            ?>
                        </p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-hand-holding text-blue-500 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Export Options -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8 no-print">
            <div class="flex flex-col md:flex-row gap-4 mb-4">
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
            
            <div class="flex flex-wrap gap-2">
                <a href="?download_pdf=1" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i>Download PDF
                </a>
                <a href="?download_csv=1" class="bg-cyan-600 text-white px-4 py-2 rounded-lg hover:bg-cyan-700 transition-colors flex items-center">
                    <i class="fas fa-file-csv mr-2"></i>Download CSV
                </a>
                <button onclick="window.print()" class="bg-accent text-white px-4 py-2 rounded-lg hover:bg-secondary transition-colors flex items-center">
                    <i class="fas fa-print mr-2"></i>Cetak
                </button>
            </div>
        </div>

        <!-- Report Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden" id="print-area">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-dark">Data Peminjaman</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peminjam</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Pinjam</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Kembali</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Dikembalikan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Denda</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $q = mysqli_query($conn,"
                            SELECT p.id_pinjam, p.jumlah, p.tanggal_pinjam, p.tanggal_kembali, p.status,
                                   k.tanggal_dikembalikan, k.total_denda, u.nama as nama_peminjam, a.nama_alat
                            FROM peminjaman p
                            JOIN user u ON p.id_user = u.id_user
                            JOIN alat a ON p.id_alat = a.id_alat
                            LEFT JOIN pengembalian k ON p.id_pinjam = k.id_pinjam
                            ORDER BY p.tanggal_pinjam DESC
                        ");

                        if(mysqli_num_rows($q) > 0) {
                            $no = 1;
                            while($p = mysqli_fetch_assoc($q)){
                                $status_badge = '';
                                if($p['status'] === 'Disetujui') {
                                    $status_badge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Sedang Dipinjam</span>';
                                } elseif($p['status'] === 'Selesai') {
                                    $status_badge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"><i class="fas fa-check mr-1"></i>Selesai</span>';
                                } else {
                                    $status_badge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i>Menunggu</span>';
                                }
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900"><?= $no++; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($p['nama_peminjam']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($p['nama_alat']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900"><?= $p['jumlah'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $p['tanggal_pinjam'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $p['tanggal_kembali'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $p['tanggal_dikembalikan'] ?? '-' ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?= $status_badge ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= ($p['total_denda'] > 0 ? 'Rp. ' . number_format($p['total_denda'], 0, ',', '.') : '-') ?></td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo '<tr><td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data peminjaman.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-8 no-print">
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
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
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