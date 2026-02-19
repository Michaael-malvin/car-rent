<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('peminjam');

// proses pengembalian: tampilkan form upload foto + kondisi saat user request, lalu simpan pengembalian
if (isset($_GET['kembali']) && !isset($_POST['submit_return'])) {
    $id_pinjam = mysqli_real_escape_string($conn, $_GET['kembali']);
    // tampilkan form
    $p_q = mysqli_query($conn, "SELECT p.*, a.nama_alat FROM peminjaman p JOIN alat a ON p.id_alat=a.id_alat WHERE p.id_pinjam='$id_pinjam' AND p.id_user='$_SESSION[id_user]'");
    $p = mysqli_fetch_assoc($p_q);
    if (!$p) {
        $_SESSION['error_msg'] = 'Peminjaman tidak ditemukan.';
        header('Location: pengembalian.php'); exit;
    }
    // hitung keterlambatan sementara (untuk estimasi denda)
    $today_ts = strtotime(date('Y-m-d'));
    $due_ts = strtotime($p['tanggal_kembali']);
    $late_days = 0;
    if ($today_ts > $due_ts) {
        $late_days = ceil(($today_ts - $due_ts)/86400);
    }
    $late_denda = $late_days * 5000;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengembalian - Sistem Peminjaman Alat</title>
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
                    <a href="pengembalian.php" class="text-accent font-medium border-b-2 border-primary pb-1">
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
                <a href="dashboard.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
                <a href="daftar_alat.php" class="text-gray-700 hover:text-accent transition-colors flex items-center space-x-2">
                    <i class="fas fa-box"></i>
                    <span>Daftar Alat</span>
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
            <h2 class="text-3xl font-bold text-dark mb-2">Form Pengembalian</h2>
            <p class="text-gray-600">Isi formulir berikut untuk mengembalikan alat yang dipinjam</p>
        </div>

        <!-- Return Form -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 md:p-8">
                <div class="mb-6">
                    <h3 class="text-xl font-semibold text-dark mb-2">Alat yang Dikembalikan</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-lg font-medium"><?= htmlspecialchars($p['nama_alat']) ?></p>
                        <p class="text-sm text-gray-600">ID Peminjaman: <?= $p['id_pinjam'] ?></p>
                    </div>
                </div>

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id_pinjam" value="<?= $p['id_pinjam'] ?>">
                    
                    <div class="mb-6">
                        <label for="foto_pengembalian" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-camera mr-2 text-accent"></i>Foto Pengembalian <span class="text-red-500">*</span>
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition-colors">
                            <input type="file" name="foto_pengembalian" id="foto_pengembalian" accept="image/*" required class="hidden">
                            <label for="foto_pengembalian" class="cursor-pointer">
                                <div id="preview-container" class="mb-2">
                                    <i class="fas fa-cloud-upload-alt text-gray-400 text-4xl"></i>
                                </div>
                                <p class="text-sm text-gray-600">Klik untuk mengunggah foto</p>
                                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG. Maksimal 5MB</p>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Estimasi denda -->
                    <div class="mb-6">
                        <p class="text-sm text-gray-700 mb-2">Estimasi Denda Keterlambatan</p>
                        <div class="bg-gray-50 p-3 rounded-lg mb-2">
                            <p class="font-medium"><?= $late_days; ?> hari — Rp.<?= number_format($late_denda,0,',','.'); ?></p>
                        </div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="pay_now" value="1" class="mr-2">Bayar tunai sekarang (Cash On Spot)
                        </label>
                        <div class="mt-2">
                            <input type="number" name="pembayaran" min="0" value="<?= $late_denda ?>" class="pl-3 pr-3 py-2 border rounded" />
                            <p class="text-xs text-gray-500">Masukkan jumlah yang dibayarkan (default = estimasi denda keterlambatan).</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="kondisi" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clipboard-check mr-2 text-accent"></i>Kondisi Alat <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="relative border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-primary transition-colors">
                                <input type="radio" name="kondisi" value="aman" required class="sr-only peer" checked>
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                    <div>
                                        <p class="font-medium">Baik/Aman</p>
                                        <p class="text-sm text-gray-500">Alat dalam kondisi baik</p>
                                    </div>
                                </div>
                                <div class="absolute inset-0 border-2 border-primary rounded-lg opacity-0 peer-checked:opacity-100 pointer-events-none"></div>
                            </label>
                            
                            <label class="relative border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-primary transition-colors">
                                <input type="radio" name="kondisi" value="rusak" class="sr-only peer">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                                    <div>
                                        <p class="font-medium">Rusak</p>
                                        <p class="text-sm text-gray-500">Terdapat kerusakan</p>
                                    </div>
                                </div>
                                <div class="absolute inset-0 border-2 border-primary rounded-lg opacity-0 peer-checked:opacity-100 pointer-events-none"></div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="submit" name="submit_return" class="bg-primary text-dark font-bold py-3 px-6 rounded-lg hover:bg-opacity-90 transition-colors flex items-center justify-center">
                            <i class="fas fa-paper-plane mr-2"></i>Kirim Pengembalian
                        </button>
                        <a href="pengembalian.php" class="bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-colors flex items-center justify-center text-center">
                            <i class="fas fa-times mr-2"></i>Batal
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
                    <h3 class="text-lg font-semibold text-blue-900">Informasi Pengembalian</h3>
                    <ul class="mt-2 text-sm text-blue-800 space-y-1">
                        <li><i class="fas fa-check mr-2"></i>Foto harus jelas dan menunjukkan kondisi alat saat ini</li>
                        <li><i class="fas fa-check mr-2"></i>Pastikan alat dalam kondisi bersih</li>
                        <li><i class="fas fa-check mr-2"></i>Laporkan dengan jujur jika ada kerusakan</li>
                        <li><i class="fas fa-check mr-2"></i>Pengembalian akan dikonfirmasi oleh petugas</li>
                    </ul>
                </div>
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

        // Image preview
        document.getElementById('foto_pengembalian').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('preview-container');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="max-h-40 mx-auto rounded">`;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
<?php
    exit;
}

    // proses submit pengembalian dari form
    if (isset($_POST['submit_return'])) {
    $id_pinjam = mysqli_real_escape_string($conn, $_POST['id_pinjam']);
    $kondisi = mysqli_real_escape_string($conn, $_POST['kondisi']);

    // hitung keterlambatan lagi di server
    $p_q2 = mysqli_query($conn, "SELECT id_user, tanggal_kembali FROM peminjaman WHERE id_pinjam='".mysqli_real_escape_string($conn, $id_pinjam)."'");
    $p_row = mysqli_fetch_assoc($p_q2);
    if (!$p_row) {
        $_SESSION['error_msg'] = 'Peminjaman tidak ditemukan. Pastikan Anda membuka form pengembalian dari halaman yang benar.';
        header('Location: pengembalian.php');
        exit;
    }
    // pastikan peminjam adalah pemilik peminjaman
    if (intval($p_row['id_user']) !== intval($_SESSION['id_user'])) {
        $_SESSION['error_msg'] = 'Anda tidak berhak mengembalikan peminjaman ini.';
        header('Location: pengembalian.php');
        exit;
    }
    $today_ts = strtotime(date('Y-m-d'));
    $due_ts = strtotime($p_row['tanggal_kembali']);
    $late_days = 0;
    if ($today_ts > $due_ts) {
        $late_days = ceil(($today_ts - $due_ts)/86400);
    }
    $late_denda = $late_days * 5000;

    // handle foto upload
    $foto_filename = null;
    if (isset($_FILES['foto_pengembalian']) && $_FILES['foto_pengembalian']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/uploads';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $tmp = $_FILES['foto_pengembalian']['tmp_name'];
        $ext = pathinfo($_FILES['foto_pengembalian']['name'], PATHINFO_EXTENSION);
        $foto_filename = uniqid('ret_') . '.' . $ext;
        move_uploaded_file($tmp, $uploadDir . '/' . $foto_filename);
    } else {
        $_SESSION['error_msg'] = 'Foto pengembalian wajib di-upload.';
        header('Location: pengembalian.php'); exit;
    }

    // pembayaran oleh peminjam (tunai di tempat)
    $pembayaran = 0;
    $pembayaran_metode = null;
    $pembayaran_diterima = 0;
    if (!empty($_POST['pay_now']) && isset($_POST['pembayaran'])) {
        $pembayaran = intval($_POST['pembayaran']);
        if ($pembayaran > 0) {
            $pembayaran_metode = 'tunai';
            $pembayaran_diterima = 1;
        }
    }

    // jika peminjam melaporkan kerusakan, mereka dapat juga memasukkan estimasi denda kerusakan (opsional)
    $denda_kerusakan = 0;
    if ($kondisi === 'rusak' && isset($_POST['denda_kerusakan'])) {
        $denda_kerusakan = intval($_POST['denda_kerusakan']);
    }

    // simpan record pengembalian dengan info pembayaran dan estimasi denda keterlambatan
    $foto_esc = mysqli_real_escape_string($conn, $foto_filename);
    $kondisi_esc = mysqli_real_escape_string($conn, $kondisi);
    $pembayaran_metode_esc = $pembayaran_metode ? mysqli_real_escape_string($conn, $pembayaran_metode) : '';

    mysqli_query($conn, "INSERT INTO pengembalian (id_pinjam, tanggal_dikembalikan, foto_pengembalian, kondisi, terlambat, denda, denda_kerusakan, konfirmasi_kerusakan, pembayaran, pembayaran_metode, pembayaran_diterima) VALUES ('".$id_pinjam."', NOW(), '".$foto_esc."', '".$kondisi_esc."', '".($late_days>0?1:0)."', '".$late_denda."', '".$denda_kerusakan."', 0, '".$pembayaran."', '".$pembayaran_metode_esc."', '".$pembayaran_diterima."')");

    mysqli_query($conn, "INSERT INTO log_aktivitas (id_user, aktivitas) VALUES ('$_SESSION[id_user]', 'Peminjam mengirim foto pengembalian')");

    $_SESSION['success_msg'] = 'Pengembalian dikirim. Menunggu konfirmasi petugas.';
    header('Location: pengembalian.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian Alat - Sistem Peminjaman Alat</title>
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
                    <a href="pengembalian.php" class="text-accent font-medium border-b-2 border-primary pb-1">
                        <i class="fas fa-undo mr-2"></i>Pengembalian
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
                <a href="pengembalian.php" class="text-accent font-medium flex items-center space-x-2">
                    <i class="fas fa-undo"></i>
                    <span>Pengembalian</span>
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
            <h2 class="text-3xl font-bold text-dark mb-2">Pengembalian Alat</h2>
            <p class="text-gray-600">Kelola pengembalian alat yang telah Anda pinjam</p>
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

        <!-- Tools List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-dark">Alat yang Sedang Dipinjam</h3>
            </div>
            
            <?php
            $q = mysqli_query($conn,"
                SELECT p.id_pinjam, p.id_user, p.id_alat, p.jumlah, p.tanggal_pinjam, p.tanggal_kembali, p.status, a.nama_alat
                FROM peminjaman p
                JOIN alat a ON p.id_alat = a.id_alat
                WHERE p.id_user='$_SESSION[id_user]' 
                AND p.status='disetujui'
                ORDER BY p.tanggal_pinjam DESC
            ");

            if(mysqli_num_rows($q) > 0) {
                echo '<div class="p-6">';
                echo '<div class="space-y-4">';
                
                while($p = mysqli_fetch_assoc($q)){
                    // Check if return date is overdue
                    $today = strtotime(date('Y-m-d'));
                    $tanggal_kembali = strtotime($p['tanggal_kembali']);
                    $is_overdue = ($today > $tanggal_kembali);
                    
                    // Check if return already submitted
                    $chk = mysqli_query($conn, "SELECT * FROM pengembalian WHERE id_pinjam='{$p['id_pinjam']}' ORDER BY id_kembali DESC LIMIT 1");
                    $has_return = mysqli_num_rows($chk) > 0;
                    $return_status = '';
                    if ($has_return) {
                        $rowk = mysqli_fetch_assoc($chk);
                        if ($p['status'] === 'disetujui' && empty($rowk['konfirmasi_kerusakan'])) {
                            $return_status = 'Menunggu konfirmasi petugas';
                            $status_color = 'text-blue-600 bg-blue-100';
                        } else {
                            $return_status = 'Sudah mengembalikan';
                            $status_color = 'text-green-600 bg-green-100';
                        }
                    }
                    
                    echo '<div class="border rounded-lg p-4 ' . ($is_overdue ? 'border-red-200 bg-red-50' : 'border-gray-200') . '">';
                    echo '<div class="flex flex-col md:flex-row md:items-center md:justify-between">';
                    
                    // Tool Info
                    echo '<div class="mb-4 md:mb-0">';
                    echo '<div class="flex items-center mb-2">';
                    echo '<div class="w-12 h-12 bg-primary bg-opacity-20 rounded-full flex items-center justify-center mr-3">';
                    echo '<i class="fas fa-tools text-primary"></i>';
                    echo '</div>';
                    echo '<div>';
                    echo '<h4 class="font-semibold text-lg text-dark">' . htmlspecialchars($p['nama_alat']) . '</h4>';
                    echo '<p class="text-sm text-gray-600">ID Peminjaman: ' . $p['id_pinjam'] . '</p>';
                    echo '</div>';
                    echo '</div>';
                    
                    // Dates and Status
                    echo '<div class="grid grid-cols-2 gap-4 mb-4 md:mb-0">';
                    echo '<div>';
                    echo '<p class="text-xs text-gray-500">Tanggal Pinjam</p>';
                    echo '<p class="font-medium">' . $p['tanggal_pinjam'] . '</p>';
                    echo '</div>';
                    echo '<div>';
                    echo '<p class="text-xs text-gray-500">Tanggal Kembali</p>';
                    echo '<p class="font-medium">' . $p['tanggal_kembali'] . '</p>';
                    if ($is_overdue) {
                        echo '<p class="text-xs text-red-600 mt-1"><i class="fas fa-exclamation-triangle mr-1"></i>Terlambat</p>';
                    }
                    echo '</div>';
                    echo '</div>';
                    
                    // Action Button
                    echo '<div class="flex items-center">';
                    if ($has_return) {
                        echo '<span class="' . $status_color . ' px-3 py-1 rounded-full text-xs font-medium">' . $return_status . '</span>';
                    } else {
                        echo '<a href="?kembali=' . $p['id_pinjam'] . '" class="bg-primary text-dark px-4 py-2 rounded-lg hover:bg-opacity-90 transition-colors font-medium flex items-center">';
                        echo '<i class="fas fa-undo mr-2"></i>Kembalikan';
                        echo '</a>';
                    }
                    echo '</div>';
                    
                    echo '</div>';
                    echo '</div>';
                }
                
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="p-12 text-center">';
                echo '<i class="fas fa-box-open text-gray-300 text-5xl mb-4"></i>';
                echo '<h3 class="text-xl font-medium text-gray-700 mb-2">Tidak Ada Alat yang Dipinjam</h3>';
                echo '<p class="text-gray-500 mb-6">Anda belum meminjam alat apa pun saat ini.</p>';
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
    </script>
</body>
</html>