<?php
session_start();
if (isset($_SESSION['login'])) {
    // kalau sudah login, lempar sesuai role
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
    } elseif ($_SESSION['role'] == 'petugas') {
        header("Location: ../petugas/dashboard.php");
    } else {
        header("Location: ../peminjam/dashboard.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Sistem</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">
    <div class="card" style="max-width:400px;margin:auto;margin-top:100px;">
        <h2 style="text-align:center;">Login Sistem</h2>
        <form action="login_process.php" method="POST">
            <br>
            <label>Username</label>
            <input type="text" name="username" required style="width:100%;padding:8px;">

            <br><br>
            <label>Password</label>
            <input type="password" name="password" required style="width:100%;padding:8px;">

            <br><br>
            <button type="submit" class="btn" style="width:100%;">Login</button>
        </form>
    </div>
</div>

</body>
</html>
