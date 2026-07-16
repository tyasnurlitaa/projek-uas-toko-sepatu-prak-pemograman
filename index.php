<?php
// Sesuaikan dengan letak koneksi database Anda (bisa '/config/database.php' atau '/db.php' langsung)
if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
} else {
    require_once __DIR__ . '/db.php';
}

require_once __DIR__ . '/auth.php';

// Cek apakah autoload vendor ada (untuk dompdf/export pdf jika digunakan)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

requireLogin();

$mode = $_GET['action'] ?? 'index';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'store';

    if ($action === 'beli') {
        $namaPembeli = trim($_POST['nama_pembeli'] ?? '');
        $sepatuId = (int)($_POST['sepatu_id'] ?? 0);
        $jumlah = (int)($_POST['jumlah'] ?? 0);

        if ($namaPembeli === '' || $sepatuId <= 0 || $jumlah <= 0) {
            $error = 'Data pembelian belum lengkap.';
        } else {
            $stmt = $conn->prepare('SELECT id, harga, stok FROM sepatu WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $sepatuId);
            $stmt->execute();
            $sepatu = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$sepatu) {
                $error = 'Sepatu tidak ditemukan.';
            } elseif ($sepatu['stok'] < $jumlah) {
                $error = 'Stok tidak mencukupi.';
            } else {
                $totalHarga = $sepatu['harga'] * $jumlah;
                $stmt = $conn->prepare('INSERT INTO pembelian (nama_pembeli, sepatu_id, jumlah, total_harga) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('siii', $namaPembeli, $sepatuId, $jumlah, $totalHarga);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare('UPDATE sepatu SET stok = stok - ? WHERE id = ?');
                $stmt->bind_param('ii', $jumlah, $sepatuId);
                $stmt->execute();
                $stmt->close();

                $message = 'Pembelian berhasil dicatat.';
            }
        }
    }

    if ($action === 'expense') {
        $keterangan = trim($_POST['keterangan'] ?? '');
        $nominal = (int)($_POST['nominal'] ?? 0);
        $kategori = trim($_POST['kategori'] ?? 'Lainnya');

        if ($keterangan === '' || $nominal <= 0) {
            $error = 'Data pengeluaran belum lengkap.';
        } else {
            $stmt = $conn->prepare('INSERT INTO pengeluaran (keterangan, nominal, kategori) VALUES (?, ?, ?)');
            $stmt->bind_param('sis', $keterangan, $nominal, $kategori);
            $stmt->execute();
            $message = 'Pengeluaran berhasil dicatat.';
            $stmt->close();
        }
    }

    if ($action === 'stok_keluar') {
        $sepatuId = (int)($_POST['sepatu_id'] ?? 0);
        $jumlah = (int)($_POST['jumlah'] ?? 0);
        $keterangan = trim($_POST['keterangan_keluar'] ?? '');

        if ($sepatuId <= 0 || $jumlah <= 0 || $keterangan === '') {
            $error = 'Data stok keluar belum lengkap.';
        } else {
            $stmt = $conn->prepare('SELECT id, stok FROM sepatu WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $sepatuId);
            $stmt->execute();
            $sepatu = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$sepatu) {
                $error = 'Sepatu tidak ditemukan.';
            } elseif ($sepatu['stok'] < $jumlah) {
                $error = 'Stok tidak mencukupi untuk dicatat keluar.';
            } else {
                $stmt = $conn->prepare('INSERT INTO stok_keluar (sepatu_id, jumlah, keterangan) VALUES (?, ?, ?)');
                $stmt->bind_param('iis', $sepatuId, $jumlah, $keterangan);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare('UPDATE sepatu SET stok = stok - ? WHERE id = ?');
                $stmt->bind_param('ii', $jumlah, $sepatuId);
                $stmt->execute();
                $stmt->close();

                $message = 'Data stok keluar berhasil dicatat.';
            }
        }
    }

    if ($action === 'store') {
        $nama = trim($_POST['nama'] ?? '');
        $merek = trim($_POST['merek'] ?? '');
        $ukuran = trim($_POST['ukuran'] ?? '');
        $harga = (int)($_POST['harga'] ?? 0);
        $stok = (int)($_POST['stok'] ?? 0);
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if ($nama === '' || $merek === '' || $ukuran === '' || $harga <= 0 || $stok < 0) {
            $error = 'Semua field wajib diisi dengan benar.';
        } else {
            $stmt = $conn->prepare('INSERT INTO sepatu (nama, merek, ukuran, harga, stok, deskripsi) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssiis', $nama, $merek, $ukuran, $harga, $stok, $deskripsi);
            $stmt->execute();
            $message = 'Data sepatu berhasil ditambahkan.';
            $stmt->close();
        }
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $merek = trim($_POST['merek'] ?? '');
        $ukuran = trim($_POST['ukuran'] ?? '');
        $harga = (int)($_POST['harga'] ?? 0);
        $stok = (int)($_POST['stok'] ?? 0);
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if ($id <= 0 || $nama === '' || $merek === '' || $ukuran === '' || $harga <= 0 || $stok < 0) {
            $error = 'Data yang diedit tidak valid.';
        } else {
            $stmt = $conn->prepare('UPDATE sepatu SET nama = ?, merek = ?, ukuran = ?, harga = ?, stok = ?, deskripsi = ? WHERE id = ?');
            $stmt->bind_param('sssiisi', $nama, $merek, $ukuran, $harga, $stok, $deskripsi, $id);
            $stmt->execute();
            $message = 'Data sepatu berhasil diperbarui.';
            $stmt->close();
        }
    }
}

if ($mode === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) {
        $stmt = $conn->prepare('DELETE FROM sepatu WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        $message = 'Data sepatu berhasil dihapus.';
    }
    $mode = 'index';
}

$detailData = null;
if ($mode === 'detail' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare('SELECT * FROM sepatu WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $detailData = $result->fetch_assoc();
    $stmt->close();
}

$editData = null;
if ($mode === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare('SELECT * FROM sepatu WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
    $stmt->close();
}

$result = $conn->query('SELECT * FROM sepatu ORDER BY created_at DESC');
$items = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$transaksiResult = $conn->query('SELECT p.id, p.nama_pembeli, p.jumlah, p.total_harga, p.created_at, s.nama AS nama_sepatu FROM pembelian p JOIN sepatu s ON s.id = p.sepatu_id ORDER BY p.created_at DESC LIMIT 10');
$transactions = $transaksiResult ? $transaksiResult->fetch_all(MYSQLI_ASSOC) : [];

$pengeluaranResult = $conn->query('SELECT * FROM pengeluaran ORDER BY created_at DESC LIMIT 10');
$expenses = $pengeluaranResult ? $pengeluaranResult->fetch_all(MYSQLI_ASSOC) : [];

$stokKeluarResult = $conn->query('SELECT sk.id, sk.jumlah, sk.keterangan, sk.created_at, s.nama AS nama_sepatu FROM stok_keluar sk JOIN sepatu s ON s.id = sk.sepatu_id ORDER BY sk.created_at DESC LIMIT 10');
$stokKeluar = $stokKeluarResult ? $stokKeluarResult->fetch_all(MYSQLI_ASSOC) : [];

$totalItems = count($items);
$totalStok = array_sum(array_column($items, 'stok'));
$nilaiPersediaan = array_sum(array_map(fn($item) => $item['harga'] * $item['stok'], $items));
$totalPengeluaran = array_sum(array_map(fn($item) => (int)$item['nominal'], $expenses));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Toko Sepatu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-canvas: #FAF6F0;
            --bg-gradient-start: #F5EFEB;
            --bg-gradient-end: #DFD5CD;
            --card-bg: rgba(253, 251, 249, 0.95);
            --border-color: rgba(127, 85, 57, 0.12);
            --primary-nude: #7F5539;
            --accent-soft: #B5828F;
            --accent-terracotta: #C08A7C;
            --primary-grad: linear-gradient(135deg, #C08A7C 0%, #7F5539 100%);
            --text-primary: #4A3525;
            --text-secondary: #8E7968;
            --success-color: #6B826E;
            --danger-color: #B27272;
            --gold-soft: #C6A080;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            min-height: 100vh;
            color: var(--text-primary);
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient Glow Background */
        body::before, body::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            filter: blur(140px);
            z-index: 0;
            opacity: 0.5;
            pointer-events: none;
        }

        body::before {
            background: #E6CCB2;
            top: 5%;
            left: -5%;
        }

        body::after {
            background: #DDB892;
            bottom: 5%;
            right: -5%;
        }

        .navbar-custom {
            background: rgba(253, 251, 249, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            z-index: 10;
        }

        .navbar-brand-custom {
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--primary-nude) !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .hero-card {
            background: var(--primary-grad);
            border: none;
            border-radius: 24px;
            color: #FAF6F0;
            box-shadow: 0 15px 35px rgba(127, 85, 57, 0.15);
            position: relative;
            overflow: hidden;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            filter: blur(40px);
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 24px 20px;
            box-shadow: 0 10px 30px -10px rgba(127, 85, 57, 0.05);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px -5px rgba(127, 85, 57, 0.08);
            border-color: var(--gold-soft);
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 12px;
        }

        .dashboard-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            box-shadow: 0 15px 35px -10px rgba(127, 85, 57, 0.05);
            padding: 24px;
            margin-bottom: 24px;
        }

        .card-title-custom {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.3px;
        }

        .form-label-custom {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        .form-control-custom {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(127, 85, 57, 0.15);
            border-radius: 12px;
            padding: 10px 14px;
            color: var(--text-primary);
            transition: all 0.25s ease;
        }

        .form-control-custom:focus {
            background: #ffffff;
            border-color: var(--accent-terracotta);
            box-shadow: 0 0 0 4px rgba(192, 138, 124, 0.15);
            color: var(--text-primary);
        }

        .btn-primary-custom {
            background: var(--primary-grad);
            border: none;
            border-radius: 12px;
            padding: 11px 20px;
            font-weight: 700;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(127, 85, 57, 0.2);
        }

        .btn-outline-custom {
            border: 1px solid rgba(127, 85, 57, 0.3);
            background: transparent;
            color: var(--text-primary);
            border-radius: 12px;
            padding: 9px 18px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-outline-custom:hover {
            background: rgba(127, 85, 57, 0.05);
            border-color: var(--primary-nude);
            color: var(--text-primary);
        }

        .badge-stock-out {
            background-color: rgba(192, 138, 124, 0.12);
            color: var(--accent-terracotta);
            border: 1px solid rgba(192, 138, 124, 0.2);
            font-weight: 700;
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 30px;
        }

        .table-custom th {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-secondary);
            border-bottom: 2px solid rgba(127, 85, 57, 0.1);
            padding: 14px 10px;
        }

        .table-custom td {
            font-size: 0.9rem;
            color: var(--text-primary);
            padding: 14px 10px;
            border-bottom: 1px solid rgba(127, 85, 57, 0.06);
        }

        .alert-custom-success {
            background: rgba(107, 130, 110, 0.12);
            border: 1px solid rgba(107, 130, 110, 0.2);
            color: var(--success-color);
            border-radius: 16px;
            font-weight: 600;
        }

        .alert-custom-danger {
            background: rgba(178, 114, 114, 0.12);
            border: 1px solid rgba(178, 114, 114, 0.2);
            color: var(--danger-color);
            border-radius: 16px;
            font-weight: 600;
        }

        .action-icon-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
        }

        .btn-edit-style {
            background: rgba(198, 160, 128, 0.15);
            color: var(--primary-nude);
        }

        .btn-edit-style:hover {
            background: var(--primary-nude);
            color: white !important;
        }

        .btn-delete-style {
            background: rgba(178, 114, 114, 0.12);
            color: var(--danger-color);
        }

        .btn-delete-style:hover {
            background: var(--danger-color);
            color: white !important;
        }

        .custom-confirm-modal {
            border-radius: 20px;
            border: 1px solid var(--border-color);
            background: var(--card-bg);
        }

        .action-row-group {
            display: inline-flex;
            gap: 6px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .action-row-group .btn {
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        @media (max-width: 576px) {
            .dashboard-card {
                padding: 18px;
                border-radius: 18px;
            }

            .card-title-custom {
                font-size: 1rem;
            }

            .form-control-custom,
            .btn-primary-custom,
            .btn-outline-custom {
                font-size: 0.95rem;
            }

            .table-custom th,
            .table-custom td {
                white-space: nowrap;
            }

            .action-icon-btn {
                width: 36px;
                height: 36px;
            }
        }
    </style>
</head>
<body>

<!-- Navbar Minimalis Mewah -->
<nav class="navbar navbar-expand-lg navbar-custom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand navbar-brand-custom" href="index.php">
            <i class="bi bi-layers-half fs-4"></i>
            <span>Toko Sepatu</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <a class="btn btn-outline-custom btn-sm me-1 d-none d-sm-inline-flex align-items-center gap-1" href="report.php" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a class="btn btn-primary-custom btn-sm d-flex align-items-center gap-1" style="background: var(--primary-nude);" href="logout.php">
                <i class="bi bi-box-arrow-right"></i> Keluar
            </a>
        </div>
    </div>
</nav>

<div class="container py-4 position-relative" style="z-index: 1;">
    
    <!-- Hero Header Card -->
    <div class="card hero-card shadow-sm mb-4">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-white text-dark px-3 py-2 rounded-pill fw-bold mb-3 small shadow-sm">
                        <i class="bi bi-shield-lock-fill text-warning me-1"></i> Mode Admin Panel
                    </span>
                    <h2 class="fw-extrabold mb-1" style="letter-spacing: -0.5px;">Selamat Datang, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!</h2>
                    <p class="mb-0 text-white-50">Kelola ketersediaan produk, pantau arus transaksi penjualan, dan dapatkan laporan inventaris secara real-time.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="text-white-50 d-block small">Hari Operasional</span>
                    <span class="fw-bold fs-5"><?php echo date('d F Y'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Toko (3 Kotak Utama) -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background: rgba(192, 138, 124, 0.15); color: var(--accent-terracotta);">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <span class="text-secondary small fw-bold">Variasi Produk</span>
                <h3 class="fw-extrabold mb-0 mt-1" style="color: var(--text-primary);"><?php echo $totalItems; ?> Merek/Tipe</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background: rgba(107, 130, 110, 0.15); color: var(--success-color);">
                    <i class="bi bi-layers-fill"></i>
                </div>
                <span class="text-secondary small fw-bold">Total Unit Stok</span>
                <h3 class="fw-extrabold mb-0 mt-1" style="color: var(--text-primary);"><?php echo number_format($totalStok); ?> Pasang</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card h-100">
                <div class="stat-icon" style="background: rgba(198, 160, 128, 0.15); color: var(--gold-soft);">
                    <i class="bi bi-wallet2"></i>
                </div>
                <span class="text-secondary small fw-bold">Nilai Persediaan</span>
                <h3 class="fw-extrabold mb-0 mt-1" style="color: var(--text-primary);">Rp <?php echo number_format($nilaiPersediaan, 0, ',', '.'); ?></h3>
            </div>
        </div>
    </div>

    <!-- Alert Notifikasi Interaktif -->
    <?php if ($message): ?>
        <div class="alert alert-custom-success p-3 border-0 d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div><?php echo htmlspecialchars($message); ?></div>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-custom-danger p-3 border-0 d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <!-- Bagian Form & Tabel Utama -->
    <div class="row g-4">
        
        <!-- Sidebar Kiri: Formulir Transaksi & Input Sepatu -->
        <div class="col-lg-4">
            
            <!-- 1. Form Catat Pembelian -->
            <div class="dashboard-card shadow-sm">
                <h5 class="card-title-custom mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-cart-plus text-secondary"></i> Catat Pembelian Baru
                </h5>
                <form method="post" autocomplete="off">
                    <input type="hidden" name="action" value="beli">
                    
                    <div class="mb-3">
                        <label class="form-label form-label-custom">Nama Pembeli</label>
                        <input type="text" name="nama_pembeli" class="form-control form-control-custom" placeholder="Nama lengkap pelanggan" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label form-label-custom">Pilih Sepatu</label>
                        <select name="sepatu_id" class="form-select form-control-custom" required>
                            <option value="">-- Pilih Model Sepatu --</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?php echo (int)$item['id']; ?>">
                                    <?php echo htmlspecialchars($item['nama']) . ' (' . htmlspecialchars($item['merek']) . ') - Ukuran ' . htmlspecialchars($item['ukuran']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label form-label-custom">Jumlah Pasang</label>
                        <input type="number" name="jumlah" class="form-control form-control-custom" min="1" placeholder="Contoh: 1" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-cash-stack"></i> Simpan Transaksi
                    </button>
                </form>
            </div>

            <!-- 2. Form Catat Pengeluaran -->
            <div class="dashboard-card shadow-sm">
                <h5 class="card-title-custom mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-wallet2 text-secondary"></i> Catat Pengeluaran
                </h5>
                <form method="post" autocomplete="off">
                    <input type="hidden" name="action" value="expense">
                    <div class="mb-3">
                        <label class="form-label form-label-custom">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control form-control-custom" placeholder="Contoh: Belanja packaging" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-label-custom">Nominal</label>
                        <input type="number" name="nominal" class="form-control form-control-custom" min="1" placeholder="Contoh: 250000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-label-custom">Kategori</label>
                        <select name="kategori" class="form-select form-control-custom">
                            <option value="Operasional">Operasional</option>
                            <option value="Utilitas">Utilitas</option>
                            <option value="Marketing">Marketing</option>
                            <option value="SDM">SDM</option>
                            <option value="Display">Display</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-custom w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-plus-circle"></i> Simpan Pengeluaran
                    </button>
                </form>
            </div>

            <!-- 3. Form Tambah Stok Keluar Manual -->
            <div class="dashboard-card shadow-sm">
                <h5 class="card-title-custom mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-box-arrow-right text-secondary"></i> Input Stok Keluar Manual
                </h5>
                <form method="post" autocomplete="off">
                    <input type="hidden" name="action" value="stok_keluar">
                    <div class="mb-3">
                        <label class="form-label form-label-custom">Pilih Sepatu</label>
                        <select name="sepatu_id" class="form-select form-control-custom" required>
                            <option value="">-- Pilih model --</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?php echo (int)$item['id']; ?>"><?php echo htmlspecialchars($item['nama']) . ' (' . htmlspecialchars($item['merek']) . ')'; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-label-custom">Jumlah Keluar</label>
                        <input type="number" name="jumlah" class="form-control form-control-custom" min="1" placeholder="Contoh: 2" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-label-custom">Keterangan</label>
                        <input type="text" name="keterangan_keluar" class="form-control form-control-custom" placeholder="Contoh: Terjual, retur, rusak" required>
                    </div>
                    <button type="submit" class="btn btn-outline-custom w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-plus-circle"></i> Simpan Stok Keluar
                    </button>
                </form>
            </div>

            <!-- 4. Form Tambah Sepatu -->
            <div class="dashboard-card shadow-sm">
                <h5 class="card-title-custom mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle text-secondary"></i> Tambah Inventaris Baru
                </h5>
                
                <form method="post" autocomplete="off">
                    <input type="hidden" name="action" value="store">
                    
                    <div class="mb-3">
                        <label class="form-label form-label-custom">Nama Model</label>
                        <input type="text" name="nama" class="form-control form-control-custom" placeholder="Contoh: Air Force 1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label form-label-custom">Merek / Brand</label>
                        <input type="text" name="merek" class="form-control form-control-custom" placeholder="Contoh: Nike" required>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label form-label-custom">Ukuran (Size)</label>
                            <input type="text" name="ukuran" class="form-control form-control-custom" placeholder="Contoh: 41" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label form-label-custom">Stok Awal</label>
                            <input type="number" name="stok" class="form-control form-control-custom" placeholder="0" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label form-label-custom">Harga (Rupiah)</label>
                        <div class="input-group">
                            <span class="input-group-text border-0" style="background: rgba(127, 85, 57, 0.1); border-radius: 12px 0 0 12px; color: var(--text-primary); font-weight: bold; font-size: 0.9rem;">Rp</span>
                            <input type="number" name="harga" class="form-control form-control-custom" style="border-radius: 0 12px 12px 0;" placeholder="Harga jual sepatu" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label form-label-custom">Deskripsi Singkat</label>
                        <textarea name="deskripsi" class="form-control form-control-custom" rows="3" placeholder="Informasi detail sepatu..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-save"></i> Simpan Produk
                    </button>
                </form>
            </div>
        </div>

        <!-- Kolom Kanan: Tabel Riwayat Transaksi & Daftar Produk -->
        <div class="col-lg-8">
            
            <!-- 1. Riwayat Transaksi Penjualan -->
            <div class="dashboard-card shadow-sm">
                <h5 class="card-title-custom mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-activity text-secondary"></i> Riwayat Penjualan Terakhir
                </h5>
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th style="width: 15%">Jenis</th>
                                <th>Pembeli</th>
                                <th>Sepatu</th>
                                <th class="text-center">Jumlah</th>
                                <th>Total Harga</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada transaksi pembelian tercatat.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $trx): ?>
                                    <tr>
                                        <td><span class="badge badge-stock-out">Stok Keluar</span></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($trx['nama_pembeli']); ?></td>
                                        <td><?php echo htmlspecialchars($trx['nama_sepatu']); ?></td>
                                        <td class="text-center fw-semibold"><?php echo (int)$trx['jumlah']; ?> psg</td>
                                        <td class="fw-semibold">Rp <?php echo number_format((int)$trx['total_harga'], 0, ',', '.'); ?></td>
                                        <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($trx['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. Tabel Catatan Stok Keluar -->
            <div class="dashboard-card shadow-sm">
                <h5 class="card-title-custom mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-box-arrow-right text-secondary"></i> Catatan Stok Keluar
                </h5>
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Sepatu</th>
                                <th class="text-center">Jumlah</th>
                                <th>Keterangan</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stokKeluar)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada data stok keluar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($stokKeluar as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['nama_sepatu']); ?></td>
                                        <td class="text-center fw-semibold"><?php echo (int)$item['jumlah']; ?></td>
                                        <td><?php echo htmlspecialchars($item['keterangan']); ?></td>
                                        <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Tabel Catatan Pengeluaran -->
            <div class="dashboard-card shadow-sm">
                <h5 class="card-title-custom mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-wallet2 text-secondary"></i> Catatan Pengeluaran Terbaru
                </h5>
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th class="text-end">Nominal</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($expenses)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada catatan pengeluaran.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($expenses as $expense): ?>
                                    <tr>
                                        <td><span class="badge px-2 py-1 rounded" style="background: rgba(127, 85, 57, 0.08); color: var(--primary-nude); font-size: 0.8rem; font-weight: 600;"><?php echo htmlspecialchars($expense['kategori']); ?></span></td>
                                        <td><?php echo htmlspecialchars($expense['keterangan']); ?></td>
                                        <td class="text-end fw-semibold">Rp <?php echo number_format((int)$expense['nominal'], 0, ',', '.'); ?></td>
                                        <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($expense['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Tabel Inventaris Daftar Sepatu -->
            <div class="dashboard-card shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 class="card-title-custom m-0 d-flex align-items-center gap-2">
                        <i class="bi bi-view-list text-secondary"></i> Daftar Sepatu di Gudang
                    </h5>
                    <a class="btn btn-outline-custom btn-sm" href="index.php">
                        <i class="bi bi-arrow-clockwise"></i> Segarkan Data
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Merek</th>
                                <th class="text-center">Size</th>
                                <th>Harga Jual</th>
                                <th class="text-center">Stok</th>
                                <th class="text-end" style="width: 15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Inventaris sepatu masih kosong.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($item['nama']); ?></td>
                                        <td>
                                            <span class="badge px-2 py-1 rounded" style="background: rgba(127, 85, 57, 0.08); color: var(--primary-nude); font-size: 0.8rem; font-weight: 600;">
                                                <?php echo htmlspecialchars($item['merek']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center fw-semibold"><?php echo htmlspecialchars($item['ukuran']); ?></td>
                                        <td class="fw-bold">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                        <td class="text-center fw-bold <?php echo $item['stok'] <= 3 ? 'text-danger' : ''; ?>">
                                            <?php echo (int)$item['stok']; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="action-row-group">
                                                <a class="btn btn-outline-custom btn-sm" href="index.php?action=detail&id=<?php echo (int)$item['id']; ?>">Detail</a>
                                                <a class="btn btn-outline-custom btn-sm" href="index.php?action=edit&id=<?php echo (int)$item['id']; ?>">Update</a>
                                                <a class="btn btn-delete-style btn-sm" href="index.php?action=delete&id=<?php echo (int)$item['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data sepatu <?php echo htmlspecialchars($item['nama']); ?>?')">Hapus</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php if ($editData || $detailData): ?>
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content custom-confirm-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="itemModalLabel">
                    <?php echo $editData ? 'Update Data Sepatu' : 'Detail Sepatu'; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($editData): ?>
                    <form method="post" autocomplete="off">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?php echo (int)$editData['id']; ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label form-label-custom">Nama Model</label>
                                <input type="text" name="nama" class="form-control form-control-custom" value="<?php echo htmlspecialchars($editData['nama'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-custom">Merek / Brand</label>
                                <input type="text" name="merek" class="form-control form-control-custom" value="<?php echo htmlspecialchars($editData['merek'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-custom">Ukuran</label>
                                <input type="text" name="ukuran" class="form-control form-control-custom" value="<?php echo htmlspecialchars($editData['ukuran'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-custom">Stok</label>
                                <input type="number" name="stok" class="form-control form-control-custom" value="<?php echo htmlspecialchars($editData['stok'] ?? ''); ?>" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-custom">Harga</label>
                                <input type="number" name="harga" class="form-control form-control-custom" value="<?php echo htmlspecialchars($editData['harga'] ?? ''); ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label form-label-custom">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control form-control-custom" rows="3"><?php echo htmlspecialchars($editData['deskripsi'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <a href="index.php" class="btn btn-outline-custom flex-fill">Batal</a>
                            <button type="submit" class="btn btn-primary-custom flex-fill">Simpan Perubahan</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label form-label-custom">Nama Model</label>
                            <div class="form-control form-control-custom bg-white"><?php echo htmlspecialchars($detailData['nama'] ?? '-'); ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-custom">Merek</label>
                            <div class="form-control form-control-custom bg-white"><?php echo htmlspecialchars($detailData['merek'] ?? '-'); ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-custom">Ukuran</label>
                            <div class="form-control form-control-custom bg-white"><?php echo htmlspecialchars($detailData['ukuran'] ?? '-'); ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-custom">Stok</label>
                            <div class="form-control form-control-custom bg-white"><?php echo (int)($detailData['stok'] ?? 0); ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-custom">Harga</label>
                            <div class="form-control form-control-custom bg-white">Rp <?php echo number_format((int)($detailData['harga'] ?? 0), 0, ',', '.'); ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label form-label-custom">Deskripsi</label>
                            <div class="form-control form-control-custom bg-white" style="min-height: 100px;"><?php echo htmlspecialchars($detailData['deskripsi'] ?? '-'); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($editData || $detailData): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = new bootstrap.Modal(document.getElementById('itemModal'));
        modal.show();
    });
</script>
<?php endif; ?>

<div class="container-fluid mt-4">
    <div class="text-center pb-4">
        <a href="https://github.com/triyasnur/toko-sepatu-projek-prak-pemograman" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-secondary small d-inline-flex align-items-center gap-2">
            <i class="bi bi-github"></i>
            GitHub: triyasnur/toko-sepatu-projek-prak-pemograman
        </a>
    </div>
</div>

</body>
</html>