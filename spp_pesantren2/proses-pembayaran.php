<?php
session_start();
require 'config.php';

// Memeriksa login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Memeriksa hak akses
if ($_SESSION['role'] !== 'santri/walisantri') {
    header("Location: ../login.php");
    exit;
}

// Fungsi kembali ke dashboard dengan pesan
function kembali($pesan) {
    header("Location: dashboard.php?pesan=" . urlencode($pesan));
    exit;
}

// Memeriksa metode pengiriman
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pembayaran.php");
    exit;
}

// Mengambil data formulir
$id_user = (int) $_SESSION['user_id'];
$nama_santri = $_SESSION['nama_lengkap'];
$periode = trim($_POST['periode'] ?? '');
$tanggal_bayar = $_POST['tanggal_bayar'] ?? '';
$jumlah_input = trim($_POST['jumlah_bayar'] ?? '');
$metode_pembayaran = $_POST['metode_pembayaran'] ?? '';

// Validasi periode
$periode_valid = [
    'Januari', 'Februari', 'Maret', 'April',
    'Mei', 'Juni', 'Juli', 'Agustus',
    'September', 'Oktober', 'November', 'Desember'
];

if (!in_array($periode, $periode_valid, true)) {
    kembali("Periode pembayaran tidak valid.");
}

// Mengubah format angka Indonesia
$jumlah_input = str_replace(' ', '', $jumlah_input);

if (preg_match('/^\d{1,3}(\.\d{3})+(,\d{1,5})?$/', $jumlah_input)) {
    $jumlah_bayar = str_replace('.', '', $jumlah_input);
    $jumlah_bayar = str_replace(',', '.', $jumlah_bayar);

} elseif (preg_match('/^\d+,\d{1,5}$/', $jumlah_input)) {
    $jumlah_bayar = str_replace(',', '.', $jumlah_input);

} else {
    $jumlah_bayar = $jumlah_input;
}

// Validasi jumlah pembayaran
if (
    !preg_match('/^\d{1,15}(\.\d{1,5})?$/', $jumlah_bayar) ||
    (float)$jumlah_bayar <= 0
) {
    kembali("Jumlah pembayaran tidak valid.");
}

// Validasi tanggal
$tanggal = DateTime::createFromFormat('Y-m-d', $tanggal_bayar);

if (!$tanggal || $tanggal->format('Y-m-d') !== $tanggal_bayar) {
    kembali("Tanggal pembayaran tidak valid.");
}

// Validasi metode pembayaran
if (!in_array($metode_pembayaran, ['tunai', 'transfer'], true)) {
    kembali("Metode pembayaran tidak valid.");
}

// Menyiapkan bukti pembayaran
$bukti_transfer = null;
$lokasi_file = null;

// Jika metode transfer
if ($metode_pembayaran === 'transfer') {

    if (
        !isset($_FILES['bukti_transfer']) ||
        $_FILES['bukti_transfer']['error'] !== UPLOAD_ERR_OK
    ) {
        kembali("Bukti transfer wajib diunggah.");
    }

    $file = $_FILES['bukti_transfer'];

    if ($file['size'] > 2 * 1024 * 1024) {
        kembali("Ukuran bukti maksimal 2 MB.");
    }

    // Memeriksa jenis file
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $jenis_file = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png'
    ];

    if (!isset($jenis_file[$mime])) {
        kembali("File harus berupa JPG atau PNG.");
    }

    // Membuat folder uploads
    $folder = __DIR__ . '/uploads/';

    if (!is_dir($folder)) {
        if (!mkdir($folder, 0755, true)) {
            kembali("Folder upload gagal dibuat.");
        }
    }

    // Membuat nama file
    $nama_file = 'bukti_' . $id_user . '_' .
                 bin2hex(random_bytes(8)) . '.' .
                 $jenis_file[$mime];

    $lokasi_file = $folder . $nama_file;

    // Memindahkan file
    if (!move_uploaded_file($file['tmp_name'], $lokasi_file)) {
        kembali("Bukti transfer gagal diunggah.");
    }

    $bukti_transfer = 'uploads/' . $nama_file;
}

// Menyimpan data ke database
$sql = "INSERT INTO log_pembayaran
        (id_user, nama_santri, periode, jumlah_bayar,
         tanggal_bayar, metode_pembayaran, bukti_transfer, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'menunggu')";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {

    if ($lokasi_file !== null && file_exists($lokasi_file)) {
        unlink($lokasi_file);
    }

    kembali("Gagal menyiapkan penyimpanan data.");
}

// Mengikat parameter
mysqli_stmt_bind_param(
    $stmt,
    "issssss",
    $id_user,
    $nama_santri,
    $periode,
    $jumlah_bayar,
    $tanggal_bayar,
    $metode_pembayaran,
    $bukti_transfer
);

// Menjalankan query
if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    // Langsung menuju dashboard
    kembali("Pembayaran berhasil dikirim dan menunggu verifikasi admin.");

} else {

    mysqli_stmt_close($stmt);

    if ($lokasi_file !== null && file_exists($lokasi_file)) {
        unlink($lokasi_file);
    }

    kembali("Pembayaran gagal disimpan. Silakan coba kembali.");
}
?>
