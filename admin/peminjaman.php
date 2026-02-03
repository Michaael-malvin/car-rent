<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('admin');
?>

<h2>Data Peminjaman</h2>

<table border="1">
<tr>
    <th>Peminjam</th>
    <th>Alat</th>
    <th>Tanggal</th>
    <th>Status</th>
</tr>

<?php
$q = mysqli_query($conn,"SELECT * FROM peminjaman");
while($p = mysqli_fetch_assoc($q)){
?>
<tr>
    <td><?= $p['nama_peminjam']; ?></td>
    <td><?= $p['nama_alat']; ?></td>
    <td><?= $p['tgl_pinjam']; ?></td>
    <td><?= $p['status']; ?></td>
</tr>
<?php } ?>
</table>
