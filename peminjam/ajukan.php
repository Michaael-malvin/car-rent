<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('peminjam');

if (isset($_POST['ajukan'])) {
    mysqli_query($conn,"INSERT INTO peminjaman VALUES(
        NULL,
        '$_SESSION[nama]',
        '$_POST[alat]',
        NOW(),
        'Menunggu'
    )");
    echo "<script>alert('Pengajuan berhasil dikirim');</script>";
}
?>

<h2>Ajukan Peminjaman</h2>

<form method="post">
    <label>Pilih Alat</label><br>
    <select name="alat" required>
        <?php
        $q = mysqli_query($conn,"SELECT * FROM alat WHERE stok > 0");
        while($a = mysqli_fetch_assoc($q)){
            echo "<option value='$a[nama_alat]'>$a[nama_alat]</option>";
        }
        ?>
    </select>
    <br><br>
    <button name="ajukan">Ajukan</button>
</form>

<br>
<a href="dashboard.php">Kembali</a>
