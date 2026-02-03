<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('admin');

if (isset($_POST['tambah'])) {
    mysqli_query($conn,"INSERT INTO kategori VALUES(NULL,'$_POST[nama]')");
    header("Location: kategori.php");
}
?>

<h2>CRUD Kategori</h2>

<form method="post">
    <input name="nama" placeholder="Nama Kategori" required>
    <button name="tambah">Tambah</button>
</form>

<hr>

<table border="1">
<?php
$q = mysqli_query($conn,"SELECT * FROM kategori");
while($k = mysqli_fetch_assoc($q)){
    echo "<tr><td>$k[nama_kategori]</td></tr>";
}
?>
</table>
