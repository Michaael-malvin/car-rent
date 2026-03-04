<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('peminjam');

// Prefill selected alat when coming from daftar_alat.php?id=...
$prefill_alat = null;
if (isset($_GET['id'])) {
    $tmp_id = mysqli_real_escape_string($conn, $_GET['id']);
    if (is_numeric($tmp_id)) {
        $tmp_q = mysqli_query($conn, "SELECT id_alat FROM alat WHERE id_alat='".$tmp_id."' AND stok > 0 LIMIT 1");
        if ($tmp_q && mysqli_num_rows($tmp_q) > 0) {
            $prefill_alat = intval($tmp_id);
        } else {
            $_SESSION['error_msg'] = 'Alat yang dipilih tidak tersedia atau tidak ditemukan.';
        }
    }
}

if (isset($_POST['ajukan'])) {
    $id_alat = $_POST['alat'];
    $jumlah = $_POST['jumlah'];
    $tanggal_pinjam = $_POST['tgl_pinjam'];
    $tanggal_kembali = $_POST['tgl_kembali'];
    
    // Validasi tanggal: kembali boleh sama hari atau setelah pinjam, tapi tidak boleh sebelum
    if (strtotime($tanggal_kembali) < strtotime($tanggal_pinjam)) {
        $_SESSION['error_msg'] = "Tanggal kembali tidak boleh sebelum tanggal pinjam!";
    } else {
        // Validasi stok: cek apakah stok tersedia sesuai jumlah yang diminta
        $stok_q = mysqli_query($conn,"SELECT stok FROM alat WHERE id_alat='$id_alat'");
        $stok_data = mysqli_fetch_assoc($stok_q);
        
        if (!$stok_data || $stok_data['stok'] < $jumlah) {
            $_SESSION['error_msg'] = "Stok alat tidak cukup! Stok tersedia: " . ($stok_data ? $stok_data['stok'] : 0);
        } else {
            // pastikan user sudah memberikan konfirmasi peminjaman (peringatan)
            if (!isset($_POST['confirm_peminjaman'])) {
                $_SESSION['error_msg'] = 'Anda harus menyetujui peringatan sebelum mengajukan peminjaman.';
            } else {
            mysqli_query($conn,"INSERT INTO peminjaman (id_user, id_alat, jumlah, tanggal_pinjam, tanggal_kembali, status) 
                VALUES ('$_SESSION[id_user]', $id_alat, $jumlah, '$tanggal_pinjam', '$tanggal_kembali', 'menunggu')");
            mysqli_query($conn,"INSERT INTO log_aktivitas (id_user, aktivitas) VALUES ('$_SESSION[id_user]', 'Peminjam mengajukan peminjaman alat')");
            $_SESSION['success_msg'] = "Pengajuan berhasil dikirim!";
            header("Location: ajukan.php");
            exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Peminjaman - Sistem Peminjaman Alat</title>
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
                    <a href="ajukan.php" class="text-accent font-medium border-b-2 border-primary pb-1">
                        <i class="fas fa-plus-circle mr-2"></i>Ajukan Peminjaman
                    </a>
                    <a href="pengembalian.php" class="text-gray-700 hover:text-accent transition-colors">
                        <i class="fas fa-undo mr-2"></i>Pengembalian Alat
                    </a>
                    <a href="aktivitas.php" class="text-gray-700 hover:text-accent transition-colors">
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
                <a href="dashboard.php" class="text-accent font-medium flex items-center space-x-2">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
                <a href="daftar_alat.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-box"></i>
                    <span>Daftar Alat</span>
                </a>
                <a href="ajukan.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-plus-circle"></i>
                    <span>Ajukan Peminjaman</span>
                </a>
                <a href="pengembalian.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-undo"></i>
                    <span>Pengembalian Alat</span>
                </a>
                <a href="aktivitas.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
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
            <h2 class="text-3xl font-bold text-dark mb-2">Ajukan Peminjaman</h2>
            <p class="text-gray-600">Isi formulir berikut untuk mengajukan peminjaman alat</p>
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

        <!-- Loan Application Form -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 md:p-8">
                <form method="post" id="loan-form">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tool Selection -->
                        <div class="md:col-span-2">
                            <label for="alat" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tools mr-2 text-accent"></i>Pilih Alat
                            </label>
                            <select name="alat" id="alat" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" onchange="updateMaxJumlah()">
                                <option value="">-- Pilih Alat --</option>
                                <?php
                                $q = mysqli_query($conn,"SELECT id_alat, nama_alat, kategori, stok FROM alat WHERE stok > 0 ORDER BY nama_alat");
                                while($a = mysqli_fetch_assoc($q)){
                                    $kategori = isset($a['kategori']) ? $a['kategori'] : 'Umum';
                                    $sel = ($prefill_alat !== null && intval($prefill_alat) === intval($a['id_alat'])) ? ' selected' : '';
                                    echo "<option value='{$a['id_alat']}' data-stok='{$a['stok']}'{$sel}>{$a['nama_alat']} - {$kategori} (Stok: {$a['stok']})</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <!-- Quantity -->
                        <div>
                            <label for="jumlah" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-cubes mr-2 text-accent"></i>Jumlah Alat
                            </label>
                            <div class="relative">
                                <input type="number" name="jumlah" id="jumlah" min="1" value="1" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <div id="max_info" class="absolute -bottom-5 left-0 text-xs text-gray-500 hidden">
                                    Maksimal yang bisa dipinjam: <span id="max_jumlah"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Loan Date -->
                        <div>
                            <label for="tgl_pinjam" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-2 text-accent"></i>Tanggal Peminjaman
                            </label>
                            <input type="date" name="tgl_pinjam" id="tgl_pinjam" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" min="<?= date('Y-m-d'); ?>" onchange="updateMinTglKembali()">
                        </div>
                        
                        <!-- Return Date -->
                        <div>
                            <label for="tgl_kembali" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-check mr-2 text-accent"></i>Tanggal Pengembalian
                            </label>
                            <input type="date" name="tgl_kembali" id="tgl_kembali" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    
                    <input type="hidden" name="confirm_peminjaman" id="confirm_peminjaman" value="0">
                    
                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <button type="submit" name="ajukan" class="bg-primary text-dark font-bold py-3 px-6 rounded-lg hover:bg-opacity-90 transition-colors flex items-center justify-center">
                            <i class="fas fa-paper-plane mr-2"></i>Ajukan Peminjaman
                        </button>
                        <a href="dashboard.php" class="bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-colors flex items-center justify-center text-center">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Information Card -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-blue-900">Informasi Peminjaman</h3>
                    <ul class="mt-2 text-sm text-blue-800 space-y-1">
                        <li><i class="fas fa-check mr-2"></i>Pastikan alat dikembalikan sesuai tanggal yang ditentukan</li>
                        <li><i class="fas fa-check mr-2"></i>Jaga barang dengan baik dan gunakan sesuai fungsinya</li>
                        <li><i class="fas fa-check mr-2"></i>Keterlambatan pengembalian akan dikenakan denda Rp 5.000 per hari</li>
                        <li><i class="fas fa-check mr-2"></i>Kerusakan atau kehilangan akan dikenakan denda sesuai keputusan petugas</li>
                    </ul>
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

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl"></i>
                </div>
                <h3 class="ml-3 text-xl font-bold text-dark">Konfirmasi Peminjaman</h3>
            </div>
            
            <div class="mb-6">
                <p class="text-gray-700 mb-4">Sebelum melanjutkan, harap perhatikan peringatan berikut:</p>
                <ul class="space-y-2 text-sm text-gray-700 pl-5">
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                        <span>Jaga barang dengan baik.</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                        <span>Jika rusak/hilang Anda akan dikenakan denda.</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                        <span>Keterlambatan per hari akan dikenakan denda Rp. 5.000 per hari.</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                        <span>Denda kerusakan bergantung pada keputusan petugas.</span>
                    </li>
                </ul>
            </div>
            
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" id="modal_ack" class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">Ya, saya paham dan menyetujui syarat dan ketentuan</span>
                </label>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" id="modal_cancel" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Batal
                </button>
                <button type="button" id="modal_confirm" class="px-4 py-2 bg-accent text-white rounded-lg hover:bg-secondary transition-colors">
                    Setuju & Ajukan
                </button>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        function updateMinTglKembali() {
            const tglPinjam = document.getElementById('tgl_pinjam').value;
            if (tglPinjam) {
                // kembali bisa hari yang sama
                const minDate = tglPinjam;
                document.getElementById('tgl_kembali').min = minDate;
                document.getElementById('tgl_kembali').value = minDate; // Set automatic default
            }
        }

        function updateMaxJumlah() {
            const alatSelect = document.getElementById('alat');
            const selectedOption = alatSelect.options[alatSelect.selectedIndex];
            const stok = selectedOption.getAttribute('data-stok');
            
            if (stok) {
                const maxBisaPinjam = parseInt(stok);
                if (maxBisaPinjam > 0) {
                    document.getElementById('jumlah').max = maxBisaPinjam;
                    document.getElementById('max_jumlah').textContent = maxBisaPinjam;
                    document.getElementById('max_info').style.display = 'block';
                } else {
                    document.getElementById('max_info').style.display = 'none';
                }
            }
        }

        // Jalankan saat load
        window.addEventListener('load', function() {
            updateMinTglKembali();
            updateMaxJumlah();
        });

        // Intercept form submit to show confirmation modal
        const form = document.getElementById('loan-form');
        const modal = document.getElementById('confirmModal');
        const modalAck = document.getElementById('modal_ack');
        const modalCancel = document.getElementById('modal_cancel');
        const modalConfirm = document.getElementById('modal_confirm');
        const hiddenConfirm = document.getElementById('confirm_peminjaman');

        if (form) {
            form.addEventListener('submit', function(e) {
                // if user already confirmed via modal (hidden input set), allow submit
                if (hiddenConfirm && hiddenConfirm.value === '1') {
                    return; // allow
                }
                e.preventDefault();
                // show modal
                if (modal) modal.classList.remove('hidden');
            });
        }

        if (modalCancel) modalCancel.addEventListener('click', function(){ 
            if(modal) modal.classList.add('hidden'); 
        });

        if (modalConfirm) modalConfirm.addEventListener('click', function(){
            if (!modalAck.checked) {
                alert('Silakan centang "Ya, saya paham" untuk melanjutkan.');
                return;
            }
            // set hidden confirm
            if (hiddenConfirm) hiddenConfirm.value = '1';
            // ensure submit button name is sent when submitting programmatically
            if (form) {
                var submitMarker = form.querySelector('input[name="ajukan"]');
                if (!submitMarker) {
                    submitMarker = document.createElement('input');
                    submitMarker.type = 'hidden';
                    submitMarker.name = 'ajukan';
                    submitMarker.value = '1';
                    form.appendChild(submitMarker);
                }
            }
            if (modal) modal.classList.add('hidden');
            form.submit();
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        });
    </script>
</body>
</html>