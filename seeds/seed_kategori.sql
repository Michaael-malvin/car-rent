-- Seeder: kategori sample data for demo
-- Membuat tabel kategori jika belum ada, lalu memasukkan pilihan kategori
-- Run in phpMyAdmin atau mysql CLI terhadap database `peminjaman_alat`

USE peminjaman_alat;

CREATE TABLE IF NOT EXISTS kategori (
  id_kategori INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO kategori (nama_kategori) VALUES
('Elektronik'),
('Multimedia'),
('Furniture'),
('Peraga'),
('Pertukangan'),
('Olahraga'),
('Keselamatan'),
('Kebersihan'),
('Laboratorium'),
('Lainnya');

-- End of seeder
