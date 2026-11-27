<?php
session_start();
require 'config.php';



// Memastikan data dikirim melalui POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

// Mengambil ID pengguna dari session
$id_user = (int) $_SESSION['user_id'];

// Mengambil ID pembayaran dari form
$id_pembayaran = isset($_POST['id_pembayaran'])
    ? (int) $_POST['id_pembayaran']
    : 0;

// Query untuk menghapus data pembayaran
$query = "DELETE FROM log_pembayaran
          WHERE id_pembayaran = '$id_pembayaran'
          AND id_user = '$id_user'";

$result = mysqli_query($koneksi, $query);

// Memeriksa hasil penghapusan
if ($result) {
    $_SESSION['pesan-sukses'] = "Data pembayaran berhasil dihapus";
    header("Location: dashboard.php");
    exit();
} else {
    $_SESSION['pesan-gagal'] = "Gagal menghapus data pembayaran";
    header("Location: dashboard.php");
    exit();
}
?>