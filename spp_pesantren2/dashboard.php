
<?php
session_start();
require 'config.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Memeriksa hak akses pengguna
if ($_SESSION['role'] !== 'santri/walisantri') {
    header("Location: ../login.php");
    exit();
}

// Mengambil data session
$id_user = (int) $_SESSION['user_id'];
$nama = $_SESSION['nama_lengkap'];

// Mengambil pesan notifikasi
$pesan = $_GET['pesan'] ?? '';
$pesan_sukses = $_SESSION['pesan-sukses'] ?? '';
$pesan_gagal = $_SESSION['pesan-gagal'] ?? '';

// Menghapus pesan session setelah dibaca
unset($_SESSION['pesan-sukses']);
unset($_SESSION['pesan-gagal']);

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

    <title>Dashboard - Syahriah Pesantren</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

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

        .table-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
        }

        .bukti-gambar {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
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
                <i class="fa-solid fa-mosque"></i>
                SYAHRIAH PESANTREN
            </h5>

            <hr>

            <p class="small text-white-50">MENU UTAMA</p>

            <a href="dashboard.php" class="nav-link d-flex align-items-center gap-3">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>

            <a href="pembayaran.php" class="nav-link d-flex align-items-center gap-3">
                <i class="bi bi-wallet2 me-2"></i>
                Lakukan Pembayaran
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

            <!-- PESAN PEMBAYARAN -->
            <?php if (!empty($pesan)): ?>

                <div class="alert alert-success alert-dismissible fade show"
                     role="alert">

                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong><?= aman($pesan); ?></strong>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>

                </div>

            <?php endif; ?>

            <!-- PESAN BERHASIL -->
            <?php if (!empty($pesan_sukses)): ?>

                <div class="alert alert-success alert-dismissible fade show"
                     role="alert">

                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= aman($pesan_sukses); ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>

                </div>

            <?php endif; ?>

            <!-- PESAN GAGAL -->
            <?php if (!empty($pesan_gagal)): ?>

                <div class="alert alert-danger alert-dismissible fade show"
                     role="alert">

                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <?= aman($pesan_gagal); ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>

                </div>

            <?php endif; ?>

            <!-- INFORMASI -->
            <div class="alert alert-success">
                <i class="bi bi-info-circle me-2"></i>

                Selamat datang,
                <strong><?= aman($nama); ?></strong>.

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
                                    <th>Bukti Pembayaran</th>
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

                                        <!-- BUKTI PEMBAYARAN -->
                                        <td>
                                            <?php
                                            $bukti = trim((string)($data['bukti_transfer'] ?? ''));
                                            $lokasi_bukti = __DIR__ . '/' . ltrim($bukti, '/\\');

                                            if (
                                                $data['metode_pembayaran'] === 'transfer' &&
                                                $bukti !== '' &&
                                                strpos($bukti, 'uploads/') === 0 &&
                                                is_file($lokasi_bukti)
                                            ):
                                            ?>

                                                <a href="<?= aman($bukti); ?>"
                                                   target="_blank"
                                                   title="Lihat bukti pembayaran">

                                                    <img src="<?= aman($bukti); ?>"
                                                         alt="Bukti pembayaran"
                                                         class="bukti-gambar">
                                                </a>

                                            <?php elseif ($data['metode_pembayaran'] === 'tunai'): ?>

                                                <span class="text-muted">-</span>

                                            <?php else: ?>

                                                <span class="text-muted small">
                                                    Bukti tidak tersedia
                                                </span>

                                            <?php endif; ?>
                                        </td>

                                        <!-- STATUS PEMBAYARAN -->
                                        <td>

                                            <?php if ($data['status'] == 'lunas'): ?>

                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    Lunas
                                                </span>

                                            <?php elseif ($data['status'] == 'menunggu'): ?>

                                                <span class="badge bg-warning text-dark">
                                                    Menunggu
                                                </span>

                                            <?php elseif ($data['status'] == 'ditolak'): ?>

                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle me-1"></i>
                                                    Ditolak
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-secondary">
                                                    <?= aman($data['status']); ?>
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <!-- AKSI HAPUS -->
                                        <td>

                                            <form action="proses-hapus.php"
                                                  method="POST"
                                                  onsubmit="return confirm('Yakin ingin menghapus pembayaran ini?');">

                                                <input type="hidden"
                                                       name="id_pembayaran"
                                                       value="<?= (int)$data['id_pembayaran']; ?>">

                                                <button type="submit"
                                                        class="btn btn-danger btn-sm">
                                                    Hapus

                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="9"
                                        class="text-center text-muted py-4">

                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>

                                        Belum ada data pembayaran.

                                    </td>
                                </tr>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                    <a href="riwayat-pembayaran.php"
                       class="btn btn-success btn-sm">

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
