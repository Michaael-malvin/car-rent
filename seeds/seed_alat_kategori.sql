-- Seeder: alat & kategori sample data for demo
-- Run in phpMyAdmin or mysql CLI against database `peminjaman_alat`

USE peminjaman_alat;

INSERT INTO alat (nama_alat, kategori, stok, gambar, deskripsi) VALUES
('Laptop ASUS VivoBook', 'Elektronik', 10, NULL, 'Laptop untuk presentasi dan tugas.'),
('Proyektor Epson X200', 'Elektronik', 5, NULL, 'Proyektor untuk ruangan pertemuan.'),
('Kamera DSLR Canon 200D', 'Elektronik', 4, NULL, 'Kamera untuk dokumentasi acara.'),
('Mikrofon Kondensor', 'Elektronik', 8, NULL, 'Mikrofon untuk rekaman dan presentasi.'),
('Speaker Portable JBL', 'Elektronik', 6, NULL, 'Speaker untuk acara kecil.'),
('HDMI Cable 5m', 'Elektronik', 25, NULL, 'Kabel HDMI untuk koneksi proyektor.'),
('Extension Cord 10m', 'Elektronik', 20, NULL, 'Kabel perpanjangan listrik.'),
('Kamera Action GoPro-style', 'Elektronik', 3, NULL, 'Kamera kecil untuk dokumentasi lapangan.'),
('Sound System Portable', 'Multimedia', 2, NULL, 'Sistem audio untuk acara besar.'),
('Meja Lipat 120x60cm', 'Furniture', 10, NULL, 'Meja portabel untuk kegiatan.'),
('Kursi Lipat', 'Furniture', 20, NULL, 'Kursi lipat untuk tamu.'),
('Tangga Lipat 4-step', 'Furniture', 6, NULL, 'Tangga untuk perawatan fasilitas.'),
('Papan Tulis Putih', 'Peraga', 4, NULL, 'Whiteboard untuk presentasi.'),
('Laser Pointer', 'Peraga', 8, NULL, 'Pointer untuk presentasi.'),
('Whiteboard Marker Set', 'Peraga', 30, NULL, 'Paket spidol papan tulis.'),
('Bor Tangan (Cordless)', 'Pertukangan', 7, NULL, 'Bor untuk kebutuhan perbaikan.'),
('Gergaji Besi', 'Pertukangan', 5, NULL, 'Gergaji tangan untuk pekerjaan.'),
('Palu', 'Pertukangan', 12, NULL, 'Palu untuk perbaikan ringan.'),
('Kunci Inggris Set', 'Pertukangan', 10, NULL, 'Set kunci untuk perawatan.'),
('Obeng Set', 'Pertukangan', 15, NULL, 'Obeng berbagai ukuran.'),
('Stopwatch Digital', 'Olahraga', 12, NULL, 'Stopwatch untuk kegiatan olahraga.'),
('Bola Sepak', 'Olahraga', 10, NULL, 'Bola untuk latihan/pertandingan.'),
('Raket Tenis', 'Olahraga', 6, NULL, 'Raket untuk olahraga raket.'),
('Matras Yoga', 'Olahraga', 18, NULL, 'Matras untuk olahraga dan latihan.'),
('First Aid Kit', 'Keselamatan', 5, NULL, 'Kotak pertolongan pertama.'),
('APAR (Alat Pemadam Api Ringan)', 'Keselamatan', 3, NULL, 'Pemadam api untuk keselamatan.'),
('Vacuum Cleaner', 'Kebersihan', 2, NULL, 'Alat pembersih ruangan.'),
('Multimeter Digital', 'Laboratorium', 8, NULL, 'Pengukur tegangan dan arus.'),
('Oscilloscope Mini', 'Laboratorium', 1, NULL, 'Untuk pemeriksaan sinyal elektronika.'),
('Microcontroller Kit (Arduino Uno)', 'Laboratorium', 6, NULL, 'Kit pembelajaran elektronika.');

-- End of seeder
