<?php
include '../config/auth.php';
include '../config/database.php';
cekRole('admin');
?>

<h2>Log Aktivitas</h2>

<table border="1">
<?php
$q = mysqli_query($conn,"SELECT * FROM log_aktivitas ORDER BY id DESC");
while($l = mysqli_fetch_assoc($q)){
    echo "<tr>
            <td>$l[waktu]</td>
            <td>$l[aktivitas]</td>
          </tr>";
}
?>
</table>
