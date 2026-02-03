<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('petugas');
?>

<h2>Monitoring Pengembalian</h2>

<table border="1">
<tr>
    <th>Peminjam</th>
    <th>Alat</th>
    <th>Tgl Pinjam</th>
    <th>Status</th>
</tr>

<?php
$q = mysqli_query($conn,"
    SELECT * FROM peminjaman 
    WHERE status='Disetujui'
");
while($m = mysqli_fetch_assoc($q)){
?>
<tr>
    <td><?= $m['nama_peminjam']; ?></td>
    <td><?= $m['nama_alat']; ?></td>
    <td><?= $m['tgl_pinjam']; ?></td>
    <td><?= $m['status']; ?></td>
</tr>
<?php } ?>
</table>
