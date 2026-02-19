<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('petugas');

if (!isset($_GET['id'])) {
    header('Location: monitoring.php');
    exit;
}

 $id_pinjam = mysqli_real_escape_string($conn, $_GET['id']);

// ambil peminjaman dan pengembalian terakhir
 $p_q = mysqli_query($conn, "SELECT p.*, u.nama as peminjam, a.nama_alat FROM peminjaman p JOIN user u ON p.id_user=u.id_user JOIN alat a ON p.id_alat=a.id_alat WHERE p.id_pinjam='$id_pinjam'");
 $p = mysqli_fetch_assoc($p_q);

 $k_q = mysqli_query($conn, "SELECT * FROM pengembalian WHERE id_pinjam='$id_pinjam' ORDER BY id_kembali DESC LIMIT 1");
 $k = mysqli_fetch_assoc($k_q);

if (!$p || !$k) {
    $_SESSION['error_msg'] = 'Data peminjaman/pengembalian tidak ditemukan.';
    header('Location: monitoring.php');
    exit;
}

// hitung keterlambatan
 $returned_ts = !empty($k['tanggal_dikembalikan']) ? strtotime($k['tanggal_dikembalikan']) : time();
 $due_ts = strtotime($p['tanggal_kembali']);
 $late_days = 0;
if ($returned_ts > $due_ts) {
    $late_days = ceil(($returned_ts - $due_ts)/86400);
}
 $late_denda = $late_days * 5000;

if (isset($_POST['confirm'])) {
    $denda_kerusakan = intval($_POST['denda_kerusakan']);
    $pembayaran_diterima = isset($_POST['pembayaran_diterima']) ? 1 : 0;
    $konfirmasi_kerusakan = ($denda_kerusakan > 0) ? 1 : 0;

    // update pengembalian
    $k_id = $k['id_kembali'];
    $petugas_id = intval($_SESSION['id_user']);
    // if petugas marks payment received but there was no metode recorded, mark metode as 'tunai'
    $pembayaran_metode_update = '';
    if ($pembayaran_diterima === 1 && empty($k['pembayaran_metode'])) {
        $pembayaran_metode_update = ", pembayaran_metode='tunai'";
    }
    mysqli_query($conn, "UPDATE pengembalian SET terlambat=" . ($late_days>0?1:0) . ", denda=$late_denda, denda_kerusakan=$denda_kerusakan, konfirmasi_kerusakan=$konfirmasi_kerusakan, konfirmasi_kerusakan_oleh=$petugas_id, pembayaran_diterima=$pembayaran_diterima" . $pembayaran_metode_update . " WHERE id_kembali='$k_id'");

    // kembalikan stok
    mysqli_query($conn, "UPDATE alat SET stok = stok + $p[jumlah] WHERE id_alat='$p[id_alat]'");

    // update status peminjaman
    mysqli_query($conn, "UPDATE peminjaman SET status='selesai' WHERE id_pinjam='$id_pinjam'");

    $log_msg = 'Petugas mengkonfirmasi pengembalian dan menetapkan denda';
    if ($pembayaran_diterima === 1) {
        $log_msg .= ' dan menandai pembayaran diterima';
    }
    mysqli_query($conn, "INSERT INTO log_aktivitas (id_user, aktivitas) VALUES ('$_SESSION[id_user]', '" . mysqli_real_escape_string($conn, $log_msg) . "')");

    $msg = "Pengembalian dikonfirmasi. Denda keterlambatan: Rp." . number_format($late_denda,0,',','.');
    if ($denda_kerusakan > 0) $msg .= ", denda kerusakan: Rp." . number_format($denda_kerusakan,0,',','.');
    if ($pembayaran_diterima === 1) $msg .= ", pembayaran diterima";

    $_SESSION['success_msg'] = $msg;
    header('Location: monitoring.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pengembalian - Sistem Peminjaman Alat</title>
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
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-dark mb-2">Konfirmasi Pengembalian</h2>
            <p class="text-gray-600">Periksa dan konfirmasi pengembalian alat</p>
        </div>

        <!-- Return Details -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-dark">Detail Peminjaman</h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-1">Peminjam</p>
                            <p class="font-medium text-lg"><?= htmlspecialchars($p['peminjam']) ?></p>
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-1">Alat yang Dipinjam</p>
                            <p class="font-medium text-lg"><?= htmlspecialchars($p['nama_alat']) ?></p>
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-1">Jumlah</p>
                            <p class="font-medium text-lg"><?= $p['jumlah'] ?> unit</p>
                        </div>
                    </div>
                    
                    <div>
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-1">Tanggal Pinjam</p>
                            <p class="font-medium text-lg"><?= $p['tanggal_pinjam'] ?></p>
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-1">Tanggal Kembali</p>
                            <p class="font-medium text-lg"><?= $p['tanggal_kembali'] ?></p>
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-1">Tanggal Dikembalikan</p>
                            <p class="font-medium text-lg"><?= $k['tanggal_dikembalikan'] ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="border-t pt-4 mt-4">
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 mb-1">Kondisi yang Dilaporkan</p>
                        <div class="flex items-center">
                            <?php if ($k['kondisi'] === 'rusak'): ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Rusak
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Baik/Aman
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($k['foto_pengembalian'])): ?>
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 mb-2">Foto Pengembalian</p>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                            <img src="<?= '../assets/uploads/' . htmlspecialchars($k['foto_pengembalian']) ?>" alt="Foto Pengembalian" class="max-w-full rounded-lg cursor-pointer hover:opacity-90 transition-opacity" onclick="showImageModal()">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Fine Calculation -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-dark">Perhitungan Denda</h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-1">Keterlambatan</p>
                            <p class="font-medium text-lg">
                                <?php if ($late_days > 0): ?>
                                    <span class="text-red-600"><?= $late_days ?> hari</span>
                                <?php else: ?>
                                    <span class="text-green-600">Tidak terlambat</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-1">Denda Keterlambatan</p>
                            <p class="font-medium text-lg">
                                <?php if ($late_days > 0): ?>
                                    <span class="text-red-600">Rp.<?= number_format($late_denda,0,',','.') ?></span>
                                    <span class="text-sm text-gray-500 block">(Rp.5.000 per hari)</span>
                                <?php else: ?>
                                    <span class="text-green-600">Rp.0</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <?php if ($k['kondisi'] === 'rusak'): ?>
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-1">Denda Kerusakan</p>
                            <p class="font-medium text-lg text-red-600">Rp.<?= number_format(intval($k['denda_kerusakan']),0,',','.') ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-1">Total Denda</p>
                            <p class="font-bold text-xl text-red-600">
                                Rp.<?= number_format($late_denda + intval($k['denda_kerusakan']),0,',','.') ?>
                            </p>
                        </div>
                        <?php if (!empty($k['pembayaran']) && intval($k['pembayaran'])>0): ?>
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-1">Pembayaran Oleh Peminjam</p>
                            <p class="font-medium text-lg">Rp.<?= number_format(intval($k['pembayaran']),0,',','.') ?> (Metode: <?= htmlspecialchars($k['pembayaran_metode'] ?: '—') ?>)
                            <?php if (intval($k['pembayaran_diterima'])===1): ?>
                                <span class="ml-2 inline-block px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Sudah diterima</span>
                            <?php else: ?>
                                <span class="ml-2 inline-block px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Belum diterima</span>
                            <?php endif; ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Form -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-dark">Konfirmasi Pengembalian</h3>
            </div>
            
            <div class="p-6">
                <form method="post" class="space-y-6">
                    <?php if ($k['kondisi'] === 'rusak'): ?>
                    <div>
                        <label for="denda_kerusakan" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-exclamation-triangle mr-2 text-yellow-500"></i>
                            Denda Kerusakan (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-gray-500">Rp.</span>
                            <input type="number" name="denda_kerusakan" id="denda_kerusakan" 
                                   value="<?= intval($k['denda_kerusakan']) ?>" min="0" required
                                   class="pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary w-full">
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Masukkan jumlah denda kerusakan jika diperlukan</p>
                    </div>
                    <?php else: ?>
                    <input type="hidden" name="denda_kerusakan" value="0">
                    <?php endif; ?>
                    <?php if (!empty($k['pembayaran']) && intval($k['pembayaran'])>0): ?>
                    <div class="mb-4">
                        <?php if (intval($k['pembayaran_diterima']) === 0): ?>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="pembayaran_diterima" value="1" class="mr-2">
                            <span class="text-sm">Tandai pembayaran diterima (Rp.<?= number_format(intval($k['pembayaran']),0,',','.') ?>)</span>
                        </label>
                        <?php else: ?>
                        <input type="hidden" name="pembayaran_diterima" value="1">
                        <p class="text-sm text-green-600 mt-2">Pembayaran oleh peminjam sudah diterima.</p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="flex flex-col sm:flex-row gap-3 pt-4">
                        <button type="submit" name="confirm" class="bg-primary text-dark font-bold py-3 px-6 rounded-lg hover:bg-opacity-90 transition-colors flex items-center justify-center">
                            <i class="fas fa-check mr-2"></i>Konfirmasi & Selesaikan
                        </button>
                        <a href="monitoring.php" class="bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-colors flex items-center justify-center text-center">
                            <i class="fas fa-times mr-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
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
                <img src="<?= '../assets/uploads/' . htmlspecialchars($k['foto_pengembalian']) ?>" alt="Foto Pengembalian" class="max-w-full rounded-lg">
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

        // Image modal
        function showImageModal() {
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

        // Update total fine when damage fine changes
        document.getElementById('denda_kerusakan')?.addEventListener('input', function() {
            const damageFine = parseInt(this.value) || 0;
            const lateFine = <?= $late_denda ?>;
            const total = damageFine + lateFine;
            
            // Find and update the total fine element
            const totalFineElements = document.querySelectorAll('.font-bold.text-xl');
            totalFineElements.forEach(el => {
                if (el.textContent.includes('Rp.')) {
                    el.textContent = `Rp.${total.toLocaleString('id-ID')}`;
                }
            });
        });
    </script>
</body>
</html>