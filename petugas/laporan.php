<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('petugas');
?>

<h2>Laporan Peminjaman Alat</h2>
<button onclick="window.print()">Cetak</button>

<br><br>

<table border="1" width="100%">
<tr>
    <th>Peminjam</th>
    <th>Alat</th>
    <th>Tgl Pinjam</th>
    <th>Tgl Kembali</th>
    <th>Status</th>
</tr>

<?php
$q = mysqli_query($conn,"
    SELECT p.nama_peminjam, p.nama_alat, p.tgl_pinjam,
           k.tgl_kembali, p.status
    FROM peminjaman p
    LEFT JOIN pengembalian k ON p.id = k.id_peminjaman
");
while($l = mysqli_fetch_assoc($q)){
?>
<tr>
    <td><?= $l['nama_peminjam']; ?></td>
    <td><?= $l['nama_alat']; ?></td>
    <td><?= $l['tgl_pinjam']; ?></td>
    <td><?= $l['tgl_kembali']; ?></td>
    <td><?= $l['status']; ?></td>
</tr>
<?php } ?>
</table>
