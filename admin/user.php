<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('admin');

// tambah user
if (isset($_POST['tambah'])) {
    mysqli_query($conn, "INSERT INTO users VALUES(
        NULL,
        '$_POST[nama]',
        '$_POST[username]',
        '$_POST[password]',
        '$_POST[role]'
    )");
    header("Location: user.php");
}
?>

<h2>CRUD User</h2>

<form method="post">
    <input name="nama" placeholder="Nama" required>
    <input name="username" placeholder="Username" required>
    <input name="password" placeholder="Password" required>
    <select name="role">
        <option value="admin">Admin</option>
        <option value="petugas">Petugas</option>
        <option value="peminjam">Peminjam</option>
    </select>
    <button name="tambah">Tambah</button>
</form>

<hr>

<table border="1">
<tr>
    <th>Nama</th>
    <th>Username</th>
    <th>Role</th>
</tr>

<?php
$data = mysqli_query($conn,"SELECT * FROM users");
while($u = mysqli_fetch_assoc($data)){
?>
<tr>
    <td><?= $u['nama']; ?></td>
    <td><?= $u['username']; ?></td>
    <td><?= $u['role']; ?></td>
</tr>
<?php } ?>
</table>
