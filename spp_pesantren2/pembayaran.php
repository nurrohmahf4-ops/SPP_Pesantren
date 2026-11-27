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

// Data pengguna
$nama = $_SESSION['nama_lengkap'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Lakukan Pembayaran - Syahriah Pesantren</title>

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

        .form-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
        }

        .form-label {
            font-weight: 600;
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
                    <h3 class="fw-bold mb-1">Lakukan Pembayaran</h3>

                    <p class="text-muted mb-0">
                        Silakan isi formulir pembayaran syahriah pesantren.
                    </p>
                </div>

                <span class="badge bg-success p-2 mt-2">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= htmlspecialchars($nama); ?>
                </span>

            </div>

            <!-- PESAN -->
            <?php if (isset($_GET['pesan'])): ?>
                <div class="alert alert-info alert-dismissible fade show">
                    <?= htmlspecialchars($_GET['pesan']); ?>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- FORM PEMBAYARAN -->
            <div class="card form-card">

                <div class="card-header bg-white p-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-receipt text-success me-2"></i>
                        Formulir Pembayaran Syahriah
                    </h5>
                </div>

                <div class="card-body p-4">

                    <form action="proses-pembayaran.php"
                          method="POST"
                          enctype="multipart/form-data">

                        <!-- Nama Santri -->
                        <div class="mb-3">
                            <label class="form-label">Nama Santri</label>

                            <input type="text"
                                   name="nama_santri"
                                   class="form-control"
                                   value="<?= htmlspecialchars($nama); ?>"
                                   readonly>
                        </div>

                        <!-- Periode -->
                        <div class="mb-3">
                            <label class="form-label">Periode Pembayaran</label>

                            <select name="periode" class="form-select" required>
                                <option value="">-- Pilih Periode --</option>
                                <option value="Januari">Januari</option>
                                <option value="Februari">Februari</option>
                                <option value="Maret">Maret</option>
                                <option value="April">April</option>
                                <option value="Mei">Mei</option>
                                <option value="Juni">Juni</option>
                                <option value="Juli">Juli</option>
                                <option value="Agustus">Agustus</option>
                                <option value="September">September</option>
                                <option value="Oktober">Oktober</option>
                                <option value="November">November</option>
                                <option value="Desember">Desember</option>
                            </select>
                        </div>

                        <!-- Tanggal Pembayaran -->
                        <div class="mb-3">
                            <label class="form-label">Tanggal Pembayaran</label>

                            <input type="date"
                                   name="tanggal_bayar"
                                   class="form-control"
                                   value="<?= date('Y-m-d'); ?>"
                                   required>
                        </div>

                        <!-- Jumlah Pembayaran -->
                        <div class="mb-3">
                            <label class="form-label">
                                Jumlah Pembayaran (Rp)
                            </label>

                            <input type="text"
                                   name="jumlah_bayar"
                                   class="form-control"
                                   inputmode="decimal"
                                   placeholder="Contoh: 125.000"
                                   required>
                        </div>

                        <!-- Metode Pembayaran -->
                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>

                            <select name="metode_pembayaran"
                                    id="metode_pembayaran"
                                    class="form-select"
                                    required>

                                <option value="">-- Pilih Metode Pembayaran --</option>
                                <option value="tunai">Tunai</option>
                                <option value="transfer">Transfer</option>

                            </select>
                        </div>

                        <!-- Bukti Transfer -->
                        <div class="mb-4"
                             id="bukti-pembayaran"
                             style="display: none;">

                            <label class="form-label">Bukti Transfer</label>

                            <input type="file"
                                   name="bukti_transfer"
                                   id="bukti_transfer"
                                   class="form-control"
                                   accept=".jpg,.jpeg,.png">

                            <small class="text-muted">
                                Format file JPG atau PNG, maksimal 2 MB.
                            </small>

                        </div>

                        <!-- TOMBOL -->
                        <div class="d-flex gap-2">

                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-send me-1"></i>
                                Kirim Pembayaran
                            </button>

                            <a href="dashboard.php" class="btn btn-secondary">
                                Kembali
                            </a>

                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript Metode Pembayaran -->
<script>
document.getElementById('metode_pembayaran').addEventListener('change', function() {

    const metode = this.value;
    const bukti = document.getElementById('bukti-pembayaran');
    const inputBukti = document.getElementById('bukti_transfer');

    if (metode === 'transfer') {

        bukti.style.display = 'block';
        inputBukti.required = true;

    } else {

        bukti.style.display = 'none';
        inputBukti.required = false;
        inputBukti.value = '';

    }

});
</script>

</body>
</html>
