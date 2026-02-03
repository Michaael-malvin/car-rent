<?php
session_start();
include '../config/database.php';

$username = $_POST['username'];
$password = $_POST['password'];

// keamanan dasar
$username = mysqli_real_escape_string($conn, $username);

// ambil user
$query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
$user  = mysqli_fetch_assoc($query);

if ($user) {
    // cocokkan password (sementara plaintext, nanti bisa upgrade ke password_hash)
    if ($password == $user['password']) {

        $_SESSION['login'] = true;
        $_SESSION['id']    = $user['id'];
        $_SESSION['nama']  = $user['nama'];
        $_SESSION['role']  = $user['role'];

        // redirect sesuai role
        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($user['role'] == 'petugas') {
            header("Location: ../petugas/dashboard.php");
        } else {
            header("Location: ../peminjam/dashboard.php");
        }
        exit;

    } else {
        echo "<script>
                alert('Password salah!');
                window.location='login.php';
              </script>";
    }
} else {
    echo "<script>
            alert('Username tidak ditemukan!');
            window.location='login.php';
          </script>";
}
?>
