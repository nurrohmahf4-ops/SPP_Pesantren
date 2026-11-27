<?php
session_start();
require 'config.php';

$username = $_POST['username'];
$password = $_POST['password'];

$query = "SELECT * FROM user WHERE username = '$username'";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) === 1) {

    // User ditemukan, memeriksa password
    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user['password'])) {

        // Password benar, menyimpan session
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];

        $_SESSION['pesan-sukses'] = "Login berhasil. Selamat datang, " . $user['nama_lengkap'] . "!";

        // Memeriksa role pengguna
        if ($user['role'] === 'admin') {

            header("Location: admin/dashboard.php");

        } else if ($user['role'] === 'santri/walisantri') {

            header("Location: dashboard.php");

        } else {

            // Role tidak dikenal
            $_SESSION['pesan-gagal'] = "Role pengguna tidak dikenal.";
            header("Location: login.php");
        }

        exit();

    } else {

        // Password salah
        $_SESSION['pesan-gagal'] = "Username atau password salah.";
        header("Location: login.php");
        exit();
    }

} else {

    // Username tidak ditemukan
    $_SESSION['pesan-gagal'] = "Username atau password salah.";
    header("Location: login.php");
    exit();
}
?>