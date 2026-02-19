<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('admin');

if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $stok = mysqli_real_escape_string($conn, $_POST['stok']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');

    // handle gambar upload
    $gambar_filename = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/uploads';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $tmp = $_FILES['gambar']['tmp_name'];
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar_filename = uniqid('img_') . '.' . $ext;
        move_uploaded_file($tmp, $uploadDir . '/' . $gambar_filename);
    }

    mysqli_query($conn, "INSERT INTO alat (id_alat, nama_alat, kategori, stok, gambar, deskripsi) VALUES(NULL,'$nama','$kategori','$stok', " . ($gambar_filename ? "'" . mysqli_real_escape_string($conn, $gambar_filename) . "'" : "NULL") . ", '" . $deskripsi . "')");
    
    $_SESSION['success_msg'] = "Alat berhasil ditambahkan!";
    header("Location: alat.php");
    exit;
}

// edit alat
if (isset($_POST['edit'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id_alat']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $stok = mysqli_real_escape_string($conn, $_POST['stok']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');

    // handle gambar upload (optional)
    $gambar_sql = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/uploads';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $tmp = $_FILES['gambar']['tmp_name'];
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar_filename = uniqid('img_') . '.' . $ext;
        move_uploaded_file($tmp, $uploadDir . '/' . $gambar_filename);
        $gambar_sql = ", gambar='" . mysqli_real_escape_string($conn, $gambar_filename) . "'";
    }

    mysqli_query($conn, "UPDATE alat SET nama_alat='$nama', kategori='$kategori', stok='$stok', deskripsi='" . $deskripsi . "' $gambar_sql WHERE id_alat='$id'");
    
    $_SESSION['success_msg'] = "Alat berhasil diperbarui!";
    header("Location: alat.php");
    exit;
}

// delete alat
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM alat WHERE id_alat='$id'");
    
    $_SESSION['success_msg'] = "Alat berhasil dihapus!";
    header("Location: alat.php");
    exit;
}

 $edit_alat = null;
if (isset($_GET['edit'])) {
    $id = mysqli_real_escape_string($conn, $_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM alat WHERE id_alat='$id'");
    $edit_alat = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Alat - Sistem Peminjaman Alat</title>
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
                    <a href="alat.php" class="text-accent font-medium border-b-2 border-primary pb-1">
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
                <a href="alat.php" class="text-accent font-medium flex items-center space-x-2">
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
            <h2 class="text-3xl font-bold text-dark mb-2">Manajemen Alat</h2>
            <p class="text-gray-600">Kelola data alat yang tersedia untuk dipinjam</p>
        </div>

        <!-- Success Message -->
        <?php if(isset($_SESSION['success_msg'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <span><?= $_SESSION['success_msg']; ?></span>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <!-- Tool Form -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-dark">
                    <?= $edit_alat ? 'Edit Alat' : 'Tambah Alat Baru' ?>
                </h3>
            </div>
            
            <div class="p-6">
                <form method="post" enctype="multipart/form-data" class="space-y-6">
                    <?php if ($edit_alat): ?>
                        <input type="hidden" name="id_alat" value="<?= $edit_alat['id_alat'] ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tag mr-2 text-accent"></i>Nama Alat
                            </label>
                            <input type="text" id="nama" name="nama" 
                                   value="<?= $edit_alat ? htmlspecialchars($edit_alat['nama_alat']) : '' ?>" 
                                   placeholder="Masukkan nama alat" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        
                        <div>
                            <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-folder mr-2 text-accent"></i>Kategori
                            </label>
                            <select id="kategori" name="kategori" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">-- Pilih Kategori --</option>
                                <?php
                                // Ambil daftar kategori dari tabel kategori
                                $kat_q = mysqli_query($conn, "SELECT * FROM kategori");
                                while($kat = mysqli_fetch_assoc($kat_q)) {
                                    $selected = ($edit_alat && $edit_alat['kategori'] == $kat['nama_kategori']) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($kat['nama_kategori']) . "' $selected>" . htmlspecialchars($kat['nama_kategori']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="stok" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-cubes mr-2 text-accent"></i>Stok
                        </label>
                        <input type="number" id="stok" name="stok" 
                               value="<?= $edit_alat ? $edit_alat['stok'] : '' ?>" 
                               placeholder="Masukkan jumlah stok" required min="0"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    
                    <div>
                        <label for="gambar" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-image mr-2 text-accent"></i>Gambar Alat
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition-colors">
                            <input type="file" id="gambar" name="gambar" accept="image/*" class="hidden" onchange="previewImage(this)">
                            <label for="gambar" class="cursor-pointer">
                                <div id="preview-container" class="mb-2">
                                    <?php if ($edit_alat && !empty($edit_alat['gambar'])): ?>
                                        <img src="<?= '../assets/uploads/' . htmlspecialchars($edit_alat['gambar']) ?>" alt="Gambar saat ini" class="max-h-40 mx-auto rounded">
                                    <?php else: ?>
                                        <i class="fas fa-cloud-upload-alt text-gray-400 text-4xl"></i>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-600">Klik untuk mengunggah gambar</p>
                                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG. Maksimal 5MB</p>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-align-left mr-2 text-accent"></i>Deskripsi
                        </label>
                        <textarea id="deskripsi" name="deskripsi" rows="4" 
                                  placeholder="Masukkan deskripsi alat"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"><?= $edit_alat ? htmlspecialchars($edit_alat['deskripsi']) : '' ?></textarea>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3 pt-4">
                        <button type="submit" name="<?= $edit_alat ? 'edit' : 'tambah' ?>" 
                                class="bg-primary text-dark font-bold py-3 px-6 rounded-lg hover:bg-opacity-90 transition-colors flex items-center justify-center">
                            <i class="fas fa-<?= $edit_alat ? 'save' : 'plus' ?> mr-2"></i>
                            <?= $edit_alat ? 'Update Alat' : 'Tambah Alat' ?>
                        </button>
                        <?php if ($edit_alat): ?>
                            <a href="alat.php" class="bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-colors flex items-center justify-center text-center">
                                <i class="fas fa-times mr-2"></i>Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tools List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-dark">Daftar Alat</h3>
                <div class="flex items-center space-x-2">
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Cari alat..." 
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    <button id="refresh-btn" class="bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Alat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gambar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $q = mysqli_query($conn,"SELECT * FROM alat");
                        while($a = mysqli_fetch_assoc($q)){
                            $kategori = isset($a['kategori']) ? $a['kategori'] : '-';
                            $stok = isset($a['stok']) ? $a['stok'] : '0';
                            $nama = isset($a['nama_alat']) ? $a['nama_alat'] : '-';
                            $desc = $a['deskripsi'] ? htmlspecialchars($a['deskripsi']) : '-';
                            
                            // Stock status
                            $stock_status = '';
                            if ($stok == 0) {
                                $stock_status = 'bg-red-100 text-red-800';
                                $stock_text = 'Habis';
                            } elseif ($stok < 5) {
                                $stock_status = 'bg-yellow-100 text-yellow-800';
                                $stock_text = 'Terbatas';
                            } else {
                                $stock_status = 'bg-green-100 text-green-800';
                                $stock_text = 'Tersedia';
                            }
                        ?>
                        <tr class="tool-row" data-name="<?= strtolower($nama) ?>" data-kategori="<?= strtolower($kategori) ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                        <i class="fas fa-tools text-gray-500"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($nama) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($kategori) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $stock_status ?>">
                                    <?= $stok ?> - <?= $stock_text ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($a['gambar']): ?>
                                    <button onclick="showImageModal('<?= htmlspecialchars($a['gambar']) ?>')" class="text-accent hover:text-secondary">
                                        <i class="fas fa-image text-xl"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-xs truncate">
                                    <?= $desc ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="alat.php?edit=<?= $a['id_alat'] ?>" class="text-accent hover:text-secondary mr-3">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="confirmDelete('<?= $a['id_alat'] ?>', '<?= htmlspecialchars($nama) ?>')" 
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
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

    <!-- Image Modal -->
    <div id="image-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-auto max-w-lg shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-dark">Gambar Alat</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex justify-center">
                <img id="modal-image" src="" alt="Gambar Alat" class="max-w-full rounded-lg">
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Hapus Alat</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Apakah Anda yakin ingin menghapus alat <span id="delete-tool-name" class="font-semibold"></span>? Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="flex justify-center gap-3 mt-4">
                    <button id="confirm-delete" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-trash mr-2"></i>Hapus
                    </button>
                    <button id="cancel-delete" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Search functionality
        document.getElementById('search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.tool-row');
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const kategori = row.getAttribute('data-kategori');
                
                if (name.includes(searchTerm) || kategori.includes(searchTerm)) {
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

        // Image preview
        function previewImage(input) {
            const preview = document.getElementById('preview-container');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="max-h-40 mx-auto rounded">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Image modal
        function showImageModal(imageSrc) {
            document.getElementById('modal-image').src = '../assets/uploads/' + imageSrc;
            document.getElementById('image-modal').classList.remove('hidden');
        }

        document.getElementById('close-modal').addEventListener('click', function() {
            document.getElementById('image-modal').classList.add('hidden');
        });

        // Delete confirmation
        let deleteToolId = null;
        
        function confirmDelete(toolId, toolName) {
            deleteToolId = toolId;
            document.getElementById('delete-tool-name').textContent = toolName;
            document.getElementById('delete-modal').classList.remove('hidden');
        }
        
        document.getElementById('confirm-delete').addEventListener('click', function() {
            if (deleteToolId) {
                window.location.href = `alat.php?delete=${deleteToolId}`;
            }
        });
        
        document.getElementById('cancel-delete').addEventListener('click', function() {
            document.getElementById('delete-modal').classList.add('hidden');
            deleteToolId = null;
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === document.getElementById('image-modal')) {
                document.getElementById('image-modal').classList.add('hidden');
            }
            if (event.target === document.getElementById('delete-modal')) {
                document.getElementById('delete-modal').classList.add('hidden');
                deleteToolId = null;
            }
        });
    </script>
</body>
</html>