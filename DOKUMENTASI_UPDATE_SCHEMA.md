🎯 DOKUMENTASI UPDATE DATABASE SCHEMA
======================================

Database Schema telah diperbarui sesuai dengan request. Berikut perubahan-perubahan yang dilakukan:

📝 PERUBAHAN STRUKTUR DATABASE
==============================

1. TABEL USER
   ✓ Ganti dari table "users" ke "user"
   ✓ Field: id_user (PK), nama, username, password, role (ENUM: admin, petugas, peminjam)

2. TABEL ALAT
   ✓ Field: id_alat (PK), nama_alat, id_kategori (FK), stok, kondisi

3. TABEL PEMINJAMAN (PERUBAHAN UTAMA!)
   ✗ OLD: id, nama_peminjam (VARCHAR), nama_alat (VARCHAR), tgl_pinjam, tgl_kembali, status
   ✓ NEW: id_pinjam (PK), id_user (FK), id_alat (FK), jumlah, tanggal_pinjam, tanggal_kembali, status
   
   Status baru (ENUM):
   ✗ OLD: 'Menunggu', 'Disetujui', 'Dikembalikan', 'Ditolak'
   ✓ NEW: 'menunggu', 'disetujui', 'selesai'

4. TABEL PENGEMBALIAN (PERUBAHAN STRUKTUR)
   ✗ OLD: id, id_peminjaman (FK), nama_peminjam, tgl_kembali
   ✓ NEW: id_kembali (PK), id_pinjam (FK), tanggal_dikembalikan, terlambat, denda

5. TABEL LOG_AKTIVITAS (BARU!)
   ✓ id_log, id_user (FK), aktivitas (TEXT), waktu (TIMESTAMP)

6. TABEL KATEGORI
   ✓ Sudah ada: id_kategori (PK), nama_kategori


🔧 PERUBAHAN CODING DI APLIKASI
================================

✅ peminjam/ajukan.php
   • Ganti $_SESSION['nama'] → $_SESSION['id_user']
   • Ganti $_POST['alat'] (nama) → $_POST['alat'] (id_alat) - sekarang ambil ID
   • Ganti 'INSERT INTO peminjaman (nama_peminjam, nama_alat, ...)' → '(id_user, id_alat, ...)'
   • Ganti column names: tgl_pinjam → tanggal_pinjam, tgl_kembali → tanggal_kembali
   • Ganti status 'Menunggu' → 'menunggu'
   • Tambah: INSERT log aktivitas saat ada pengajuan

✅ peminjam/pengembalian.php
   • Ganti query: WHERE nama_peminjam → WHERE id_user
   • Ganti status 'Disetujui' → 'disetujui'
   • Ganti column names: tgl_pinjam → tanggal_pinjam, tgl_kembali → tanggal_kembali
   • JOIN alat untuk ambil nama_alat (sebelumnya disimpan langsung)
   • Ganti 'INSERT INTO pengembalian (id_peminjaman, ...)' → '(id_pinjam, ...)'
   • Ganti column: tgl_kembali → tanggal_dikembalikan
   • Ganti status 'Dikembalikan' → 'selesai'
   • Tambah: INSERT log aktivitas

✅ petugas/approval.php
   • Ganti query: WHERE id → WHERE id_pinjam
   • Ganti parameter: $p['nama_alat'] → $p['id_alat']
   • Ganti query stok: WHERE nama_alat → WHERE id_alat
   • Update stok: WHERE nama_alat → WHERE id_alat
   • Ganti status 'Disetujui' → 'disetujui'
   • Ganti status 'Ditolak' → 'selesai' (dalam schema baru hanya ada 3 status)
   • Tambah JOIN dengan user & alat table
   • Tambah: INSERT log aktivitas

✅ petugas/monitoring.php
   • Ganti query: WHERE id_peminjaman → WHERE id_pinjam
   • Ganti status enum values: 'Disetujui' → 'disetujui', 'Dikembalikan' → 'selesai'
   • Tambah: Query JOIN user & alat table
   • Tambah: Restore stok saat konfirmasi pengembalian
   • Ganti column names: tgl_pinjam → tanggal_pinjam, tgl_kembali → tanggal_kembali, tgl_kembali → tanggal_dikembalikan
   • Tambah: INSERT log aktivitas

✅ petugas/laporan.php
   • Semua query: Ganti column names (tgl_* → tanggal_*, tgl_dikembalikan → tanggal_dikembalikan)
   • Tambah JOIN user & alat table
   • Update status enum handling: 'menunggu', 'disetujui', 'selesai'
   • CSV export: Update field references

✅ auth/login_process.php
   • Ganti table: users → user
   • Ganti session: $_SESSION['id'] → $_SESSION['id_user']
   • Ganti field: id → id_user
   • Tambah: INSERT log aktivitas saat login


📌 SUMMARY PERUBAHAN SESSION
=============================

OLD SESSION:
  $_SESSION['login']  → bool
  $_SESSION['id']     → user id
  $_SESSION['nama']   → user name
  $_SESSION['role']   → user role

NEW SESSION:
  $_SESSION['login']  → bool (sama)
  $_SESSION['id_user'] → user id (diganti nama)
  $_SESSION['nama']   → user name (sama)
  $_SESSION['role']   → user role (sama)


🎯 TESTING CHECKLIST
====================

1. ✓ Setup database baru dengan SQL schema yang provided
2. ✓ Insert seed data (admin, petugas, michael)
3. ✓ Login dengan username: michael, password: 123
4. ✓ Ajukan peminjaman → Check data masuk dengan id_user & id_alat
5. ✓ Login sebagai petugas: petugas/petugas123
6. ✓ Approve peminjaman → Stok berkurang
7. ✓ Lihat di monitoring → Data tampil dengan benar
8. ✓ Confirm pengembalian → Stok kembali, status jadi 'selesai'
9. ✓ Lihat laporan → Data lengkap dengan nama alat dan peminjam
10. ✓ Download PDF/CSV → Data sesuai

⚙️ PERSIAPAN
============

1. Jalankan SQL schema baru (DROP dan CREATE dari file user)
2. Sesuaikan config/database.php kalau ada perubahan
3. Test di semua modul (peminjam, petugas, admin jika ada)
4. Check log_aktivitas untuk verifikasi


✨ BONUS FIXES
==============

✓ Semua query sudah menggunakan FOREIGN KEYS dengan benar
✓ Semua enum status sudah konsisten (lowercase)
✓ Log aktivitas sekarang tercatat di database
✓ Navigation berdasarkan ID, bukan string (lebih aman & scalable)
✓ Pengembalian alat sekarang restore stok otomatis


❗ CATATAN PENTING
==================

⚠️ Status enum hanya ada 3 dalam schema baru:
   - 'menunggu' (pending approval)
   - 'disetujui' (approved, sedang dipinjam)
   - 'selesai' (completed/returned)
   
   Sistem reject sudah diganti ke 'selesai' (not approved tapi tetap closed)

⚠️ Pengembalian alat flow:
   1. Peminjam klik "Kembalikan" → status jadi 'selesai', id inserted ke pengembalian
   2. Petugas monitor & klik "Konfirmasi" → stok restored, status tetap 'selesai'

⚠️ Password masih plaintext, recommend upgrade ke password_hash() untuk production


🎊 SEMUA SUDAH SELESAI!
Aplikasi sudah di-refactor sesuai schema database baru.
