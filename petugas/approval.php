<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('petugas');

// proses approve
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    mysqli_query($conn,"UPDATE peminjaman SET status='Disetujui' WHERE id='$id'");
    header("Location: approval.php");
}

// proses tolak
if (isset($_GET['tolak'])) {
    $id = $_GET['tolak'];
    mysqli_query($conn,"UPDATE peminjaman SET status='Ditolak' WHERE id='$id'");
    header("Location: approval.php");
}
?>

<h2>Persetujuan Peminjaman</h2>

<table border="1">
<tr>
    <th>Peminjam</th>
    <th>Alat</th>
    <th>Tanggal</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>

<?php
$q = mysqli_query($conn,"SELECT * FROM peminjaman WHERE status='Menunggu'");
while($p = mysqli_fetch_assoc($q)){
?>
<tr>
    <td><?= $p['nama_peminjam']; ?></td>
    <td><?= $p['nama_alat']; ?></td>
    <td><?= $p['tgl_pinjam']; ?></td>
    <td><?= $p['status']; ?></td>
    <td>
        <a href="?approve=<?= $p['id']; ?>">Setujui</a> |
        <a href="?tolak=<?= $p['id']; ?>">Tolak</a>
    </td>
</tr>
<?php } ?>
</table>
