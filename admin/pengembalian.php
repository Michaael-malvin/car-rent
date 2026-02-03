<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('admin');
?>

<h2>Data Pengembalian</h2>

<table border="1">
<?php
$q = mysqli_query($conn,"SELECT * FROM pengembalian");
while($r = mysqli_fetch_assoc($q)){
    echo "<tr>
            <td>$r[nama_peminjam]</td>
            <td>$r[nama_alat]</td>
            <td>$r[tgl_kembali]</td>
          </tr>";
}
?>
</table>
