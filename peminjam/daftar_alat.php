<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('peminjam');
?>

<h2>Daftar Alat Tersedia</h2>

<table border="1">
<tr>
    <th>Nama Alat</th>
    <th>Kategori</th>
    <th>Stok</th>
</tr>

<?php
$q = mysqli_query($conn,"SELECT * FROM alat");
while($a = mysqli_fetch_assoc($q)){
?>
<tr>
    <td><?= $a['nama_alat']; ?></td>
    <td><?= $a['kategori']; ?></td>
    <td><?= $a['stok']; ?></td>
</tr>
<?php } ?>
</table>

<br>
<a href="dashboard.php">Kembali</a>
