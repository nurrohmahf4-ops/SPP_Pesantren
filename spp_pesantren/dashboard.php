<?php
// Memulai session dan memanggil konfigurasi database
session_start();
require 'config.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Memeriksa hak akses pengguna
if ($_SESSION['role'] !== 'santri/walisantri') {
    header("Location: ../login.php");
    exit;
}

// Mengambil data session
$id_user = (int) $_SESSION['user_id'];
$nama = $_SESSION['nama_lengkap'];

// Mengambil data pembayaran berdasarkan pengguna
$sql = "SELECT * FROM log_pembayaran
        WHERE id_user = $id_user
        ORDER BY tanggal_bayar DESC";

$result = mysqli_query($koneksi, $sql);

// Menyiapkan ringkasan pembayaran
$total_bayar = 0;
$total_transaksi = 0;
$total_lunas = 0;
$total_menunggu = 0;
$total_ditolak = 0;

$data_pembayaran = [];

if ($result) {
    while ($data = mysqli_fetch_assoc($result)) {

        $data_pembayaran[] = $data;
        $total_transaksi++;

        if ($data['status'] == 'lunas') {
            $total_bayar += (float) $data['jumlah_bayar'];
            $total_lunas++;

        } elseif ($data['status'] == 'menunggu') {
            $total_menunggu++;

        } elseif ($data['status'] == 'ditolak') {
            $total_ditolak++;
        }
    }
}

// Fungsi format rupiah
function rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Fungsi keamanan
function aman($data) {
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Aplikasi Pembayaran Pesantren</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f7f6;
            font-family: Arial, sans-serif;
        }

        .sidebar {
            min-height: 100vh;
            background-color: #198754;
            color: white;
        }

        .sidebar h5 {
            font-weight: bold;
        }

        .sidebar a {
            display: block;
            text-decoration: none;
            color: white;
            padding: 12px 15px;
            margin-bottom: 7px;
            border-radius: 8px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: rgba(255,255,255,0.2);
        }

        .content {
            padding: 25px;
        }

        .card-statistik {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
        }

        .icon-box {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 23px;
        }

        .table-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
        }

        @media (max-width: 767px) {
            .sidebar {
                min-height: auto;
            }

            .content {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 sidebar p-3">

            <h5 class="text-center py-3">
                <i class="bi bi-mortarboard-fill"></i>
                SYAHRIAH PESANTREN
            </h5>

            <hr>

            <p class="small text-white-50">MENU UTAMA</p>

            <a href="dashboard.php" class="active">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>

            <a href="pembayaran.php" class="nav-link d-flex align-items-center gap-3">
                <i class="bi bi-wallet2 me-2"></i>
                Lakukan Pembayaran
            </a>

            <a href="riwayat-pembayaran.php" class="nav-link d-flex align-items-center gap-3">
                <i class="bi bi-clock-history me-2"></i>
                Riwayat Pembayaran
            </a>

            <hr>

            <a href="logout.php"
               onclick="return confirm('Yakin ingin keluar?')">
                <i class="bi bi-box-arrow-right me-2"></i>
                Logout
            </a>

        </div>

        <!-- KONTEN UTAMA -->
        <div class="col-md-10 content">

            <!-- HEADER -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

                <div>
                    <h3 class="fw-bold mb-1">Dashboard</h3>

                    <p class="text-muted mb-0">
                        Selamat datang di Aplikasi Pembayaran Pesantren
                    </p>
                </div>

                <span class="badge bg-success p-2 mt-2">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= aman($nama); ?>
                </span>

            </div>

            <!-- INFORMASI -->
            <div class="alert alert-success">
                <i class="bi bi-info-circle me-2"></i>
                Selamat datang, <strong><?= aman($nama); ?></strong>.
                Silakan periksa informasi pembayaran kamu.
            </div>

           

                
            <!-- TABEL PEMBAYARAN -->
            <div class="card table-card">

                <div class="card-header bg-white p-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-receipt text-success me-2"></i>
                        Riwayat Pembayaran Terbaru
                    </h5>
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-success">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Santri</th>
                                    <th>Periode</th>
                                    <th>Jumlah Bayar</th>
                                    <th>Tanggal Bayar</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>

                            <?php if (count($data_pembayaran) > 0): ?>

                                <?php $no = 1; ?>

                                <?php foreach (array_slice($data_pembayaran, 0, 5) as $data): ?>

                                    <tr>
                                        <td><?= $no++; ?></td>

                                        <td>
                                            <?= aman($data['nama_santri']); ?>
                                        </td>

                                        <td>
                                            <?= aman($data['periode']); ?>
                                        </td>

                                        <td>
                                            <?= rupiah($data['jumlah_bayar']); ?>
                                        </td>

                                        <td>
                                            <?= date('d-m-Y', strtotime($data['tanggal_bayar'])); ?>
                                        </td>

                                        <td>
                                            <?= ucfirst(aman($data['metode_pembayaran'])); ?>
                                        </td>

                                        <td>
                                            <?php if ($data['status'] == 'lunas'): ?>

                                                <span class="badge bg-success">
                                                    Lunas
                                                </span>

                                            <?php elseif ($data['status'] == 'menunggu'): ?>

                                                <span class="badge bg-warning text-dark">
                                                    Menunggu
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-danger">
                                                    Ditolak
                                                </span>

                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        Belum ada data pembayaran.
                                    </td>
                                </tr>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                    <a href="riwayat-pembayaran.php" class="btn btn-success btn-sm">
                        Lihat Semua Riwayat
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>