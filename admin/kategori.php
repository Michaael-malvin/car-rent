<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('admin');

if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    mysqli_query($conn,"INSERT INTO kategori VALUES(NULL,'$nama')");
    
    $_SESSION['success_msg'] = "Kategori berhasil ditambahkan!";
    header("Location: kategori.php");
    exit;
}

// edit kategori
if (isset($_POST['edit'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id_kategori']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    
    mysqli_query($conn, "UPDATE kategori SET nama_kategori='$nama' WHERE id_kategori='$id'");
    
    $_SESSION['success_msg'] = "Kategori berhasil diperbarui!";
    header("Location: kategori.php");
    exit;
}

// delete kategori
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori='$id'");
    
    $_SESSION['success_msg'] = "Kategori berhasil dihapus!";
    header("Location: kategori.php");
    exit;
}

 $edit_kategori = null;
if (isset($_GET['edit'])) {
    $id = mysqli_real_escape_string($conn, $_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM kategori WHERE id_kategori='$id'");
    $edit_kategori = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Sistem Peminjaman Alat</title>
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
                    <a href="kategori.php" class="text-accent font-medium border-b-2 border-primary pb-1">
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
                <a href="kategori.php" class="text-accent font-medium flex items-center space-x-2">
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
            <h2 class="text-3xl font-bold text-dark mb-2">Manajemen Kategori</h2>
            <p class="text-gray-600">Kelola kategori untuk pengelompokan alat</p>
        </div>

        <!-- Success Message -->
        <?php if(isset($_SESSION['success_msg'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                <span><?= $_SESSION['success_msg']; ?></span>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <!-- Category Form -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-dark">
                    <?= $edit_kategori ? 'Edit Kategori' : 'Tambah Kategori Baru' ?>
                </h3>
            </div>
            
            <div class="p-6">
                <form method="post" class="space-y-6">
                    <?php if ($edit_kategori): ?>
                        <input type="hidden" name="id_kategori" value="<?= $edit_kategori['id_kategori'] ?>">
                    <?php endif; ?>
                    
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag mr-2 text-accent"></i>Nama Kategori
                        </label>
                        <input type="text" id="nama" name="nama" 
                               value="<?= $edit_kategori ? htmlspecialchars($edit_kategori['nama_kategori']) : '' ?>" 
                               placeholder="Masukkan nama kategori" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3 pt-4">
                        <button type="submit" name="<?= $edit_kategori ? 'edit' : 'tambah' ?>" 
                                class="bg-primary text-dark font-bold py-3 px-6 rounded-lg hover:bg-opacity-90 transition-colors flex items-center justify-center">
                            <i class="fas fa-<?= $edit_kategori ? 'save' : 'plus' ?> mr-2"></i>
                            <?= $edit_kategori ? 'Update Kategori' : 'Tambah Kategori' ?>
                        </button>
                        <?php if ($edit_kategori): ?>
                            <a href="kategori.php" class="bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-colors flex items-center justify-center text-center">
                                <i class="fas fa-times mr-2"></i>Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Categories List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-dark">Daftar Kategori</h3>
                <div class="flex items-center space-x-2">
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Cari kategori..." 
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    <button id="refresh-btn" class="bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="categories-container">
                    <?php
                    $q = mysqli_query($conn,"SELECT * FROM kategori");
                    if(mysqli_num_rows($q) > 0) {
                        while($k = mysqli_fetch_assoc($q)){
                            // Count tools in this category
                            $count_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM alat WHERE kategori='" . $k['nama_kategori'] . "'");
                            $count_result = mysqli_fetch_assoc($count_q);
                            $tool_count = $count_result['total'];

                            // build list of alat names for modal
                            $tools = [];
                            $tq = mysqli_query($conn, "SELECT nama_alat FROM alat WHERE kategori='" . mysqli_real_escape_string($conn, $k['nama_kategori']) . "'");
                            while($tr = mysqli_fetch_assoc($tq)) {
                                $tools[] = $tr['nama_alat'];
                            }
                            $data_tools = htmlspecialchars(json_encode($tools), ENT_QUOTES, 'UTF-8');
                        ?>
                        <div class="border rounded-lg p-4 hover:shadow-md transition-all category-card" data-name="<?= strtolower($k['nama_kategori']) ?>" data-tools="<?= $data_tools ?>">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-tags text-purple-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-lg text-dark"><?= htmlspecialchars($k['nama_kategori']) ?></h4>
                                        <p class="text-sm text-gray-500"><?= $tool_count ?> alat</p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="kategori.php?edit=<?= $k['id_kategori'] ?>" class="text-accent hover:text-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmDelete('<?= $k['id_kategori'] ?>', '<?= htmlspecialchars($k['nama_kategori']) ?>')" 
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php 
                        }
                    } else {
                        echo '<div class="col-span-full text-center py-8">';
                        echo '<i class="fas fa-tags text-gray-300 text-5xl mb-4"></i>';
                        echo '<h3 class="text-xl font-medium text-gray-700 mb-2">Tidak Ada Kategori</h3>';
                        echo '<p class="text-gray-500">Belum ada kategori yang ditambahkan.</p>';
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

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Hapus Kategori</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Apakah Anda yakin ingin menghapus kategori <span id="delete-category-name" class="font-semibold"></span>? Tindakan ini tidak dapat dibatalkan.
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

    <!-- Tools List Modal -->
    <div id="tools-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg overflow-hidden w-11/12 max-w-md">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 id="tools-modal-title" class="text-lg font-semibold"></h3>
                <button id="tools-modal-close" class="text-gray-600 hover:text-gray-900 text-2xl leading-none">&times;</button>
            </div>
            <div class="p-6" id="tools-modal-content"></div>
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
            const cards = document.querySelectorAll('.category-card');
            
            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                
                if (name.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Refresh button
        document.getElementById('refresh-btn').addEventListener('click', function() {
            location.reload();
        });

        // Delete confirmation
        let deleteCategoryId = null;
        
        function confirmDelete(categoryId, categoryName) {
            deleteCategoryId = categoryId;
            document.getElementById('delete-category-name').textContent = categoryName;
            document.getElementById('delete-modal').classList.remove('hidden');
        }
        
        document.getElementById('confirm-delete').addEventListener('click', function() {
            if (deleteCategoryId) {
                window.location.href = `kategori.php?delete=${deleteCategoryId}`;
            }
        });
        
        document.getElementById('cancel-delete').addEventListener('click', function() {
            document.getElementById('delete-modal').classList.add('hidden');
            deleteCategoryId = null;
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === document.getElementById('delete-modal')) {
                document.getElementById('delete-modal').classList.add('hidden');
            }
        });

        // Show tools modal when a category card is clicked
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', function() {
                const tools = JSON.parse(this.dataset.tools || '[]');
                const name = this.dataset.name;
                let html = '';
                if (tools.length) {
                    html = '<ul class="list-disc list-inside">';
                    tools.forEach(t => { html += '<li>' + t + '</li>'; });
                    html += '</ul>';
                } else {
                    html = '<p class="text-gray-500">Tidak ada alat di kategori ini.</p>';
                }
                document.getElementById('tools-modal-title').textContent = 'Alat dalam kategori ' + name;
                document.getElementById('tools-modal-content').innerHTML = html;
                document.getElementById('tools-modal').classList.remove('hidden');
            });
        });

        document.getElementById('tools-modal-close').addEventListener('click', function() {
            document.getElementById('tools-modal').classList.add('hidden');
        });
        document.getElementById('tools-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    </script>
</body>
</html>