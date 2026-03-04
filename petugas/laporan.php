<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('petugas');

// build filter SQL from GET parameters
function buildFilterSql($conn) {
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
    if(!empty($_GET['filter_kondisi'])) {
        $val = mysqli_real_escape_string($conn, $_GET['filter_kondisi']);
        $clauses[] = "k.kondisi='$val'";
    }
    if(!empty($_GET['filter_terlambat'])) {
        $val = mysqli_real_escape_string($conn, $_GET['filter_terlambat']);
        $clauses[] = "k.terlambat='$val'";
    }
    return count($clauses) ? ' AND ' . implode(' AND ', $clauses) : '';
}

$filter_sql = buildFilterSql($conn);

// Download PDF as printable HTML
if (isset($_GET['download_pdf'])) {
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="Laporan_Peminjaman_' . date('Ymd_His') . '.html"');
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Laporan Peminjaman Alat</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 20px;
                line-height: 1.4;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
            }
            .header p {
                margin: 5px 0;
                font-size: 12px;
                color: #666;
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 20px;
                font-size: 11px; 
            }
            th, td { 
                border: 1px solid #000; 
                padding: 8px; 
                text-align: left; 
                vertical-align: top;
            }
            th { 
                background-color: #ddd; 
                font-weight: bold;
            }
            .status-approved { background-color: #d4edda; }
            .status-pending { background-color: #fff3cd; }
            .status-rejected { background-color: #f8d7da; }
            .late { background-color: #f8d7da; }
            .damaged { background-color: #fff3cd; }
            .ontime { background-color: #d4edda; }
            .footer {
                margin-top: 30px;
                font-size: 10px;
                color: #666;
                text-align: center;
                border-top: 1px solid #ccc;
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
            <p>Tanggal: ' . date('d-m-Y H:i') . '</p>
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
            <th>Keterlambatan</th>
            <th>Kondisi</th>
            <th>Denda Keterlambatan</th>
            <th>Denda Kerusakan</th>
            <th>Total Denda</th>
        </tr>
        </thead>
        <tbody>';
    
    $q = mysqli_query($conn,"
        SELECT p.id_pinjam, p.id_user, p.id_alat, p.jumlah, p.tanggal_pinjam, p.tanggal_kembali, p.status,
               k.tanggal_dikembalikan, k.kondisi, k.terlambat, k.denda, k.denda_kerusakan, k.konfirmasi_kerusakan,
               u.nama as nama_peminjam, a.nama_alat
        FROM peminjaman p
        JOIN user u ON p.id_user = u.id_user
        JOIN alat a ON p.id_alat = a.id_alat
        LEFT JOIN pengembalian k ON p.id_pinjam = k.id_pinjam
        WHERE 1=1 $filter_sql
        ORDER BY p.tanggal_pinjam DESC
    ");
    
    $no = 1;
    while($l = mysqli_fetch_assoc($q)){
        $tanggal_dikembalikan = $l['tanggal_dikembalikan'] ?? '-';
        $status_class = '';
        $late_class = '';
        $kondisi_class = '';
        
        // Status styling
        if ($l['status'] === 'disetujui') {
            $status_class = 'status-approved';
        } elseif ($l['status'] === 'selesai') {
            $status_class = 'status-rejected';
        } else {
            $status_class = 'status-pending';
        }
        
        // Keterlambatan styling
        if ($l['terlambat'] == 1) {
            $late_class = 'late';
        }
        
        // Kondisi styling
        if ($l['kondisi'] === 'rusak') {
            $kondisi_class = 'damaged';
        }
        
        // Calculate values
        $terlambat_text = $l['terlambat'] == 1 ? 'Ya (' . $l['denda'] . ')' : 'Tidak';
        $kondisi_text = $l['kondisi'] == 'rusak' ? 'Rusak' : 'Baik';
        $denda_kerusakan_text = $l['denda_kerusakan'] > 0 ? 'Rp.' . number_format($l['denda_kerusakan'], 0, ',', '.') : '-';
        $total_denda = ($l['denda'] ?? 0) + ($l['denda_kerusakan'] ?? 0);
        $total_denda_text = $total_denda > 0 ? 'Rp.' . number_format($total_denda, 0, ',', '.') : '-';
        
        echo '<tr class="' . $status_class . ' ' . $late_class . ' ' . $kondisi_class . '">
            <td>' . $no++ . '</td>
            <td>' . htmlspecialchars($l['nama_peminjam']) . '</td>
            <td>' . htmlspecialchars($l['nama_alat']) . '</td>
            <td style="text-align: center;">' . $l['jumlah'] . '</td>
            <td>' . $l['tanggal_pinjam'] . '</td>
            <td>' . $l['tanggal_kembali'] . '</td>
            <td>' . $tanggal_dikembalikan . '</td>
            <td>' . ucfirst($l['status']) . '</td>
            <td>' . $terlambat_text . '</td>
            <td>' . $kondisi_text . '</td>
            <td>' . ($l['denda'] > 0 ? 'Rp.' . number_format($l['denda'], 0, ',', '.') : '-') . '</td>
            <td>' . $denda_kerusakan_text . '</td>
            <td>' . $total_denda_text . '</td>
        </tr>';
    }
    
    echo '</tbody>
        </table>
        
        <div class="footer">
            <p>Dicetak oleh: ' . $_SESSION['nama'] . ' | ' . date('d-m-Y H:i:s') . '</p>
            <p>File ini dapat dibuka di browser dan di-print/convert ke PDF menggunakan fitur Print to PDF</p>
        </div>
    </body>
    </html>';
    exit;
}

// Download as CSV
if (isset($_GET['download_csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Laporan_Peminjaman_' . date('Ymd_His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Header CSV
    fputcsv($output, ['No', 'Peminjam', 'Alat', 'Jumlah', 'Tgl Pinjam', 'Tgl Kembali', 'Tgl Dikembalikan', 'Status', 'Keterlambatan', 'Kondisi', 'Denda Keterlambatan', 'Denda Kerusakan', 'Total Denda']);
    
    // Data CSV
    $q = mysqli_query($conn,"
        SELECT p.id_pinjam, p.id_user, p.id_alat, p.jumlah, p.tanggal_pinjam, p.tanggal_kembali, p.status,
               k.tanggal_dikembalikan, k.kondisi, k.terlambat, k.denda, k.denda_kerusakan, k.konfirmasi_kerusakan,
               u.nama as nama_peminjam, a.nama_alat
        FROM peminjaman p
        JOIN user u ON p.id_user = u.id_user
        JOIN alat a ON p.id_alat = a.id_alat
        LEFT JOIN pengembalian k ON p.id_pinjam = k.id_pinjam
        ORDER BY p.tanggal_pinjam DESC
    ");
    
    $no = 1;
    while($l = mysqli_fetch_assoc($q)){
        $tanggal_dikembalikan = $l['tanggal_dikembalikan'] ?? '-';
        $terlambat_text = $l['terlambat'] == 1 ? 'Ya (' . $l['denda'] . ')' : 'Tidak';
        $kondisi_text = $l['kondisi'] == 'rusak' ? 'Rusak' : 'Baik';
        $denda_kerusakan_text = $l['denda_kerusakan'] > 0 ? 'Rp.' . number_format($l['denda_kerusakan'], 0, ',', '.') : '-';
        $total_denda = ($l['denda'] ?? 0) + ($l['denda_kerusakan'] ?? 0);
        $total_denda_text = $total_denda > 0 ? 'Rp.' . number_format($total_denda, 0, ',', '.') : '-';
        
        fputcsv($output, [$no++, $l['nama_peminjam'], $l['nama_alat'], $l['jumlah'], $l['tanggal_pinjam'], $l['tanggal_kembali'], $tanggal_dikembalikan, $l['status'], $terlambat_text, $kondisi_text, ($l['denda'] > 0 ? 'Rp.' . number_format($l['denda'], 0, ',', '.') : '-'), $denda_kerusakan_text, $total_denda_text]);
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
        
        /* Print styles */
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
<body class="bg-gray-50">
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
                    <a href="approval.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-check-circle mr-2"></i>Approval Peminjaman
                    </a>
                    <a href="monitoring.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-eye mr-2"></i>Monitoring
                    </a>
                    <a href="laporan.php" class="text-accent font-medium border-b-2 border-primary pb-1">
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
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t no-print">
            <div class="container mx-auto px-4 py-3 flex flex-col space-y-3">
                <a href="dashboard.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
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
                <a href="laporan.php" class="text-accent font-medium flex items-center space-x-2">
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
            
            <div id="filter-panel" class="hidden bg-gray-50 p-4 rounded-lg mb-4">
                <form method="get" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Peminjam</label>
                        <input type="text" name="filter_peminjam" value="<?= htmlspecialchars($_GET['filter_peminjam'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alat</label>
                        <input type="text" name="filter_alat" value="<?= htmlspecialchars($_GET['filter_alat'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="filter_status" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="">Semua</option>
                            <option value="menunggu" <?= (($_GET['filter_status'] ?? '')=='menunggu'?'selected':'') ?>>Menunggu</option>
                            <option value="disetujui" <?= (($_GET['filter_status'] ?? '')=='disetujui'?'selected':'') ?>>Disetujui</option>
                            <option value="selesai" <?= (($_GET['filter_status'] ?? '')=='selesai'?'selected':'') ?>>Selesai</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tgl Pinjam</label>
                        <input type="date" name="filter_tgl_pinjam" value="<?= htmlspecialchars($_GET['filter_tgl_pinjam'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tgl Kembali</label>
                        <input type="date" name="filter_tgl_kembali" value="<?= htmlspecialchars($_GET['filter_tgl_kembali'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="bg-primary text-dark px-4 py-2 rounded-lg">Terapkan</button>
                        <a href="laporan.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">Reset</a>
                    </div>
                </form>
            </div>

            <div class="flex flex-wrap gap-2">
                <?php
                    $params = $_GET;
                    $params['download_pdf'] = 1;
                    $pdf_url = 'laporan.php?' . http_build_query($params);
                    $params['download_csv'] = 1;
                    unset($params['download_pdf']);
                    $csv_url = 'laporan.php?' . http_build_query($params);
                ?>
                <a href="<?= htmlspecialchars($pdf_url) ?>" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i>Download PDF
                </a>
                <a href="<?= htmlspecialchars($csv_url) ?>" class="bg-cyan-600 text-white px-4 py-2 rounded-lg hover:bg-cyan-700 transition-colors flex items-center">
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
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Pinjam</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Kembali</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Dikembalikan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterlambatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kondisi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Denda Keterlambatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Denda Kerusakan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Denda</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $q = mysqli_query($conn,"
                            SELECT p.id_pinjam, p.id_user, p.id_alat, p.jumlah, p.tanggal_pinjam, p.tanggal_kembali, p.status,\n                                   k.tanggal_dikembalikan, k.kondisi, k.terlambat, k.denda, k.denda_kerusakan, k.konfirmasi_kerusakan,\n                                   u.nama as nama_peminjam, a.nama_alat\n                            FROM peminjaman p\n                            JOIN user u ON p.id_user = u.id_user\n                            JOIN alat a ON p.id_alat = a.id_alat\n                            LEFT JOIN pengembalian k ON p.id_pinjam = k.id_pinjam\n                            WHERE 1=1 $filter_sql\n                            ORDER BY p.tanggal_pinjam DESC\n                        ");

                        if(mysqli_num_rows($q) > 0) {
                            $no = 1;
                            while($l = mysqli_fetch_assoc($q)){
                                $tanggal_dikembalikan = $l['tanggal_dikembalikan'] ?? '-';
                                $row_class = '';
                                $status_badge = '';
                                $late_badge = '';
                                $kondisi_badge = '';
                                
                                // Calculate values
                                $terlambat_text = $l['terlambat'] == 1 ? 'Ya (' . $l['denda'] . ')' : 'Tidak';
                                $kondisi_text = $l['kondisi'] == 'rusak' ? 'Rusak' : 'Baik';
                                $denda_kerusakan_text = $l['denda_kerusakan'] > 0 ? 'Rp.' . number_format($l['denda_kerusakan'], 0, ',', '.') : '-';
                                $total_denda = ($l['denda'] ?? 0) + ($l['denda_kerusakan'] ?? 0);
                                $total_denda_text = $total_denda > 0 ? 'Rp.' . number_format($total_denda, 0, ',', '.') : '-';
                                
                                if($l['status'] === 'disetujui') {
                                    $status_badge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"><i class="fas fa-hand-holding mr-1"></i>Sedang Dipinjam</span>';
                                } elseif($l['status'] === 'selesai') {
                                    $status_badge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><i class="fas fa-check mr-1"></i>Selesai</span>';
                                } else {
                                    $status_badge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i>Menunggu</span>';
                                    $row_class = 'bg-yellow-50';
                                }
                                
                                if ($l['terlambat'] == 1) {
                                    $late_badge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"><i class="fas fa-exclamation-triangle mr-1"></i>Terlambat</span>';
                                }
                                
                                if ($l['kondisi'] === 'rusak') {
                                    $kondisi_badge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"><i class="fas fa-exclamation-triangle mr-1"></i>Rusak</span>';
                                }
                        ?>
                        <tr class="<?= $row_class ?> report-row" data-peminjam="<?= strtolower($l['nama_peminjam']) ?>" data-alat="<?= strtolower($l['nama_alat']) ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900"><?= $no++; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($l['nama_peminjam']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($l['nama_alat']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900"><?= $l['jumlah']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $l['tanggal_pinjam']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $l['tanggal_kembali']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $tanggal_dikembalikan; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?= $status_badge; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?= $late_badge; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?= $kondisi_badge; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= ($l['denda'] > 0 ? 'Rp.' . number_format($l['denda'], 0, ',', '.') : '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $denda_kerusakan_text; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $total_denda_text; ?></td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo '<tr><td colspan="14" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data peminjaman.</td></tr>';
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
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Filter panel toggle
        document.getElementById('filter-btn').addEventListener('click', function() {
            document.getElementById('filter-panel').classList.toggle('hidden');
        });

        // Search functionality
        document.getElementById('search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.report-row');
            
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