-- Database Setup Script untuk Peminjaman Alat
-- Run semua query di phpMyAdmin untuk membuat table yang diperlukan

-- Disable foreign key temporarily untuk drop table
SET FOREIGN_KEY_CHECKS = 0;

-- Drop table lama jika ada konflik (urutan penting! drop yang punya FK dulu)
DROP TABLE IF EXISTS log_aktivitas;
DROP TABLE IF EXISTS pengembalian;
DROP TABLE IF EXISTS peminjaman;
DROP TABLE IF EXISTS alat;
DROP TABLE IF EXISTS `user`;

-- Enable foreign key constraints kembali
SET FOREIGN_KEY_CHECKS = 1;

-- Buat table USER (dengan backticks karena "user" adalah reserved keyword)
CREATE TABLE `user` (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'petugas', 'peminjam') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Buat table ALAT
CREATE TABLE alat (
  id_alat INT AUTO_INCREMENT PRIMARY KEY,
  nama_alat VARCHAR(100) NOT NULL,
  kategori VARCHAR(50),
  stok INT DEFAULT 0,
  gambar VARCHAR(255) DEFAULT NULL,
  deskripsi TEXT DEFAULT NULL,
  UNIQUE KEY unique_alat (nama_alat)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Buat table PEMINJAMAN (struktur baru dengan id_pinjam, id_user, id_alat)
CREATE TABLE peminjaman (
  id_pinjam INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  id_alat INT NOT NULL,
  jumlah INT DEFAULT 1,
  tanggal_pinjam DATE NOT NULL,
  tanggal_kembali DATE NOT NULL,
  status ENUM('menunggu', 'disetujui', 'selesai') DEFAULT 'menunggu',
  FOREIGN KEY (id_user) REFERENCES `user`(id_user) ON DELETE CASCADE,
  FOREIGN KEY (id_alat) REFERENCES alat(id_alat) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Buat table PENGEMBALIAN (struktur baru dengan id_pinjam)
CREATE TABLE pengembalian (
  id_kembali INT AUTO_INCREMENT PRIMARY KEY,
  id_pinjam INT NOT NULL,
  tanggal_dikembalikan DATETIME DEFAULT CURRENT_TIMESTAMP,
  foto_pengembalian VARCHAR(255) DEFAULT NULL,
  kondisi ENUM('aman','rusak') DEFAULT 'aman',
  terlambat BOOLEAN DEFAULT FALSE,
  denda INT DEFAULT 0,
  denda_kerusakan INT DEFAULT 0,
  konfirmasi_kerusakan BOOLEAN DEFAULT FALSE,
  konfirmasi_kerusakan_oleh INT DEFAULT NULL,
  pembayaran INT DEFAULT 0,
  pembayaran_metode VARCHAR(50) DEFAULT NULL,
  pembayaran_diterima BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (id_pinjam) REFERENCES peminjaman(id_pinjam) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Buat table LOG_AKTIVITAS
CREATE TABLE log_aktivitas (
  id_log INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT,
  aktivitas TEXT,
  waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES `user`(id_user) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert data default
INSERT INTO `user` (id_user, nama, username, password, role) VALUES
(NULL, 'Admin', 'admin', 'admin123', 'admin'),
(NULL, 'Petugas', 'petugas', 'petugas123', 'petugas'),
(NULL, 'Michael', 'michael', '123', 'peminjam');

-- Insert data alat sample
INSERT INTO alat (id_alat, nama_alat, kategori, stok) VALUES
(NULL, 'Laptop', 'Elektronik', 5),
(NULL, 'Proyektor', 'Elektronik', 3),
(NULL, 'Meja', 'Furniture', 10),
(NULL, 'Kursi', 'Furniture', 15),
(NULL, 'Kamera', 'Elektronik', 2),
(NULL, 'Sound System', 'Elektronik', 4);

-- Verifikasi
SELECT 'USER TABLE:' as '';
SELECT * FROM `user`;

SELECT '' as '';
SELECT 'ALAT TABLE:' as '';
SELECT * FROM alat;

SELECT '' as '';
SELECT 'Table dibuat dengan sukses!' as hasil;
