<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('peminjam');

// proses pengembalian
if (isset($_GET['kembali'])) {
    $id = $_GET['kembali'];

    mysqli_query($conn,"INSERT INTO pengembalian VALUES(
        NULL,
        '$id',
        '$_SESSION[nama]',
        NOW()
    )");

    mysqli_query($conn,"UPDATE peminjaman SET status='Dikembalikan' WHERE id='$id'");

    echo "<script>alert('Alat berhasil dikembalikan');</script>";
}
?>

<h2>Pengembalian Alat</h2>

<table border="1">
<tr>
    <th>Alat</th>
    <th>Tanggal Pinjam</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>

<?php
$q = mysqli_query($conn,"
    SELECT * FROM peminjaman 
    WHERE nama_peminjam='$_SESSION[nama]' 
    AND status='Disetujui'
");
while($p = mysqli_fetch_assoc($q)){
?>
<tr>
    <td><?= $p['nama_alat']; ?></td>
    <td><?= $p['tgl_pinjam']; ?></td>
    <td><?= $p['status']; ?></td>
    <td>
        <a href="?kembali=<?= $p['id']; ?>">Kembalikan</a>
    </td>
</tr>
<?php } ?>
</table>

<br>
<a href="dashboard.php">Kembali</a>
