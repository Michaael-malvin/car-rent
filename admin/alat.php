<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('admin');

if (isset($_POST['tambah'])) {
    mysqli_query($conn,"INSERT INTO alat VALUES(
        NULL,
        '$_POST[nama]',
        '$_POST[kategori]',
        '$_POST[stok]'
    )");
    header("Location: alat.php");
}
?>

<h2>CRUD Alat</h2>

<form method="post">
    <input name="nama" placeholder="Nama Alat" required>
    <input name="kategori" placeholder="Kategori" required>
    <input name="stok" type="number" placeholder="Stok" required>
    <button name="tambah">Tambah</button>
</form>

<hr>

<table border="1">
<?php
$q = mysqli_query($conn,"SELECT * FROM alat");
while($a = mysqli_fetch_assoc($q)){
    echo "<tr>
            <td>$a[nama_alat]</td>
            <td>$a[kategori]</td>
            <td>$a[stok]</td>
          </tr>";
}
?>
</table>
