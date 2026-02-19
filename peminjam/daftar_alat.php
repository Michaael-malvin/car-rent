<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('peminjam');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Alat - Sistem Peminjaman Alat</title>
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
                    <a href="daftar_alat.php" class="text-accent font-medium border-b-2 border-primary pb-1">
                        <i class="fas fa-box mr-2"></i>Daftar Alat
                    </a>
                    <a href="ajukan.php" class="text-gray-700 hover:text-accent transition-colors">
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
            <h2 class="text-3xl font-bold text-dark mb-2">Daftar Alat Tersedia</h2>
            <p class="text-gray-600">Temukan dan pinjam alat yang Anda butuhkan</p>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Cari alat..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex gap-2">
                    <select id="category-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Semua Kategori</option>
                        <option value="Pertukangan">Pertukangan</option>
                        <option value="Elektronik">Elektronik</option>
                        <option value="Olahraga">Olahraga</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <button id="filter-btn" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors flex items-center space-x-2">
                        <i class="fas fa-filter"></i>
                        <span>Filter</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tool Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8" id="tools-container">
            <?php
            $q = mysqli_query($conn,"SELECT * FROM alat");
            while($a = mysqli_fetch_assoc($q)){
            ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow tool-card" data-category="<?= isset($a['kategori']) ? $a['kategori'] : '' ?>" data-name="<?= $a['nama_alat'] ?>">
                <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                    <?php if (!empty($a['gambar'])): ?>
                        <img src="<?= '../assets/uploads/' . htmlspecialchars($a['gambar']) ?>" alt="<?= htmlspecialchars($a['nama_alat']) ?>" class="h-full w-full object-cover">
                    <?php else: ?>
                        <div class="text-center">
                            <i class="fas fa-tools text-gray-400 text-4xl mb-2"></i>
                            <p class="text-gray-500">Tidak ada gambar</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg text-dark mb-1"><?= $a['nama_alat']; ?></h3>
                    <div class="flex items-center mb-2">
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            <?= isset($a['kategori']) ? $a['kategori'] : 'Tidak dikategorikan'; ?>
                        </span>
                    </div>
                    <div class="flex items-center mb-3">
                        <i class="fas fa-cube text-gray-400 mr-2"></i>
                        <span class="text-sm text-gray-600">Stok: <span class="font-semibold"><?= isset($a['stok']) ? $a['stok'] : '0'; ?></span></span>
                    </div>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?= !empty($a['deskripsi']) ? htmlspecialchars($a['deskripsi']) : 'Tidak ada deskripsi'; ?></p>
                    <div class="flex justify-end">
                        <?php if (isset($a['stok']) && $a['stok'] > 0): ?>
                            <a href="ajukan.php?id=<?= $a['id_alat']; ?>" class="bg-primary text-dark px-3 py-1 rounded-lg hover:bg-opacity-90 transition-colors text-sm font-medium">
                                <i class="fas fa-hand-holding mr-1"></i>Pinjam
                            </a>
                        <?php else: ?>
                            <button disabled class="bg-gray-300 text-gray-500 px-3 py-1 rounded-lg text-sm font-medium cursor-not-allowed">
                                <i class="fas fa-ban mr-1"></i>Tidak Tersedia
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- No Results Message -->
        <div id="no-results" class="hidden bg-white rounded-lg shadow-md p-8 text-center">
            <i class="fas fa-search text-gray-400 text-5xl mb-4"></i>
            <h3 class="text-xl font-semibold text-dark mb-2">Tidak ada hasil ditemukan</h3>
            <p class="text-gray-600">Coba ubah kata kunci pencarian atau filter yang Anda gunakan</p>
        </div>

        <!-- Back Button -->
        <div class="mt-8">
            <a href="dashboard.php" class="inline-flex items-center text-accent hover:text-secondary transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
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
            const cards = document.querySelectorAll('.tool-card');
            let hasResults = false;

            cards.forEach(card => {
                const name = card.getAttribute('data-name').toLowerCase();
                if (name.includes(searchTerm)) {
                    card.style.display = 'block';
                    hasResults = true;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show no results message if needed
            document.getElementById('no-results').classList.toggle('hidden', hasResults);
            document.getElementById('tools-container').classList.toggle('hidden', !hasResults);
        });

        // Filter functionality
        document.getElementById('category-filter').addEventListener('change', function() {
            const selectedCategory = this.value;
            const cards = document.querySelectorAll('.tool-card');
            let hasResults = false;

            cards.forEach(card => {
                const category = card.getAttribute('data-category');
                if (selectedCategory === '' || category === selectedCategory) {
                    card.style.display = 'block';
                    hasResults = true;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show no results message if needed
            document.getElementById('no-results').classList.toggle('hidden', hasResults);
            document.getElementById('tools-container').classList.toggle('hidden', !hasResults);
        });

        // Filter button click event
        document.getElementById('filter-btn').addEventListener('click', function() {
            document.getElementById('category-filter').dispatchEvent(new Event('change'));
        });
    </script>
</body>
</html>