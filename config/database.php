<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'toko_sepatu';

// Membuat koneksi ke server MySQL secara senyap (suppressed)
$connector = @new mysqli($host, $user, $pass);
$connection_error = '';

if ($connector->connect_error) {
    $connection_error = $connector->connect_error;
} else {
    $connector->set_charset('utf8mb4');
    // Membuat database jika belum ada
    $connector->query("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $connector->select_db($dbname);
}

$conn = $connector;

function ensureDatabaseSchema(mysqli $conn): array
{
    $status = [
        'sepatu' => false,
        'users' => false,
        'pembelian' => false,
        'seeding' => false
    ];

    // 1. Membuat Tabel Sepatu
    $q1 = $conn->query("CREATE TABLE IF NOT EXISTS sepatu (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        merek VARCHAR(100) NOT NULL,
        ukuran VARCHAR(20) NOT NULL,
        harga INT NOT NULL,
        stok INT NOT NULL,
        deskripsi TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if ($q1) $status['sepatu'] = true;

    // 2. Membuat Tabel Users (Admin)
    $q2 = $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if ($q2) $status['users'] = true;

    // 3. Membuat Tabel Pembelian (Transaksi)
    $q3 = $conn->query("CREATE TABLE IF NOT EXISTS pembelian (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_pembeli VARCHAR(100) NOT NULL,
        sepatu_id INT NOT NULL,
        jumlah INT NOT NULL,
        total_harga INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sepatu_id) REFERENCES sepatu(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if ($q3) $status['pembelian'] = true;

    // 4. Membuat Tabel Pengeluaran
    $conn->query("CREATE TABLE IF NOT EXISTS pengeluaran (
        id INT AUTO_INCREMENT PRIMARY KEY,
        keterangan VARCHAR(150) NOT NULL,
        nominal INT NOT NULL,
        kategori VARCHAR(50) NOT NULL DEFAULT 'Lainnya',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 5. Membuat Tabel Stok Keluar
    $conn->query("CREATE TABLE IF NOT EXISTS stok_keluar (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sepatu_id INT NOT NULL,
        jumlah INT NOT NULL,
        keterangan VARCHAR(150) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sepatu_id) REFERENCES sepatu(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 6. Seeding Data Banyak Sepatu (51 Items)
    $count = (int)($conn->query('SELECT COUNT(*) AS total FROM sepatu')->fetch_assoc()['total'] ?? 0);
    if ($count === 0) {
        $q4 = $conn->query("INSERT INTO sepatu (nama, merek, ukuran, harga, stok, deskripsi) VALUES
            ('Air Jordan 1','Nike','41',1800000,8,'Sneaker klasik premium'),
            ('Air Max 270','Nike','42',2100000,10,'Sneaker dengan Air Cushion'),
            ('Air Force 1','Nike','40',1700000,15,'Sepatu casual ikonik'),
            ('Revolution 7','Nike','41',980000,20,'Sepatu lari ringan'),
            ('Pegasus 40','Nike','42',1950000,7,'Running shoes premium'),
            ('Court Vision','Nike','39',1200000,12,'Sneaker kasual pria'),
            ('Superstar','Adidas','41',1350000,12,'Sepatu klasik Adidas'),
            ('Stan Smith','Adidas','40',1450000,10,'Sneaker lifestyle'),
            ('Ultraboost 22','Adidas','42',2500000,6,'Running shoes premium'),
            ('Duramo SL','Adidas','43',850000,18,'Sepatu olahraga'),
            ('Galaxy 6','Adidas','41',950000,20,'Running shoes'),
            ('Grand Court','Adidas','40',990000,15,'Sneaker casual'),
            ('RS-X','Puma','42',1650000,9,'Sneaker modern'),
            ('Future Rider','Puma','41',1200000,14,'Sepatu lifestyle'),
            ('Smash V2','Puma','40',780000,25,'Sneaker casual'),
            ('Suede Classic','Puma','39',1100000,10,'Model klasik'),
            ('Electron E','Puma','42',950000,18,'Sepatu olahraga'),
            ('Flyer Flex','Puma','41',850000,16,'Sepatu lari'),
            ('Chuck Taylor High','Converse','41',899000,20,'Sepatu kanvas klasik'),
            ('Chuck Taylor Low','Converse','40',850000,18,'Sneaker low cut'),
            ('Run Star Hike','Converse','39',1750000,7,'Model kekinian'),
            ('Converse Move','Converse','38',1350000,8,'Platform sneakers'),
            ('Old Skool','Vans','41',999000,15,'Sepatu skateboard'),
            ('Authentic','Vans','40',899000,20,'Sepatu kasual'),
            ('Slip-On Checkerboard','Vans','39',950000,17,'Slip-on populer'),
            ('Sk8-Hi','Vans','42',1199000,9,'High top sneakers'),
            ('574 Classic','New Balance','41',1450000,12,'Sneaker retro'),
            ('327','New Balance','42',1750000,8,'Lifestyle shoes'),
            ('530','New Balance','40',1650000,10,'Sneaker modern'),
            ('574 Core','New Balance','39',1550000,11,'Sepatu kasual'),
            ('Gel Nimbus','Asics','42',2500000,5,'Running premium'),
            ('Gel Kayano','Asics','41',2300000,6,'Running stability'),
            ('Patriot 13','Asics','40',850000,18,'Sepatu olahraga'),
            ('Jolt 4','Asics','39',780000,20,'Running entry level'),
            ('Wave Rider','Mizuno','42',2100000,7,'Running shoes'),
            ('Wave Inspire','Mizuno','41',1950000,8,'Sepatu lari'),
            ('Wave Sky','Mizuno','40',2350000,4,'Premium running'),
            ('Cloud X','On Running','42',2800000,5,'Training shoes'),
            ('Cloudmonster','On Running','43',3200000,3,'Running premium'),
            ('Speedcross 6','Salomon','42',2600000,4,'Trail running'),
            ('XA Pro 3D','Salomon','41',2900000,3,'Outdoor shoes'),
            ('Gel Venture','Asics','43',990000,12,'Trail running'),
            ('Zoom Winflo','Nike','42',1650000,9,'Running shoes'),
            ('Lite Racer','Adidas','40',780000,21,'Sepatu santai'),
            ('Caven 2.0','Puma','41',890000,17,'Sneaker casual'),
            ('Era','Vans','39',850000,19,'Sneaker klasik'),
            ('One Star','Converse','42',1299000,8,'Sepatu lifestyle'),
            ('Fresh Foam X','New Balance','43',2200000,5,'Running shoes'),
            ('Wave Ultima','Mizuno','41',2050000,6,'Sepatu olahraga'),
            ('XT-6','Salomon','42',3400000,2,'Sepatu trail premium'),
            ('Cloudrunner','On Running','41',2950000,4,'Running shoes')");
        if ($q4) {
            $status['seeding'] = true;
        }
    } else {
        $status['seeding'] = 'already_seeded';
    }

    // 7. Seeding Data Sampel Pembelian, Pengeluaran, dan Stok Keluar
    $pembelianCount = (int)($conn->query('SELECT COUNT(*) AS total FROM pembelian')->fetch_assoc()['total'] ?? 0);
    if ($pembelianCount < 8) {
        $conn->query("INSERT INTO pembelian (nama_pembeli, sepatu_id, jumlah, total_harga) VALUES
            ('Aldi', 1, 2, 3600000),
            ('Bunga', 3, 1, 1700000),
            ('Candra', 8, 3, 4050000),
            ('Dina', 12, 2, 1900000),
            ('Eko', 19, 1, 899000),
            ('Fira', 24, 2, 1998000),
            ('Gilang', 31, 1, 2500000),
            ('Hana', 40, 2, 3600000)");
    }

    $pengeluaranCount = (int)($conn->query('SELECT COUNT(*) AS total FROM pengeluaran')->fetch_assoc()['total'] ?? 0);
    if ($pengeluaranCount < 6) {
        $conn->query("INSERT INTO pengeluaran (keterangan, nominal, kategori) VALUES
            ('Belanja stok box packaging', 450000, 'Operasional'),
            ('Tagihan listrik toko', 320000, 'Utilitas'),
            ('Biaya iklan Instagram', 250000, 'Marketing'),
            ('Pembelian alat kebersihan', 180000, 'Operasional'),
            ('Gaji asisten toko', 1500000, 'SDM'),
            ('Beli perlengkapan display', 400000, 'Display')");
    }

    $stokKeluarCount = (int)($conn->query('SELECT COUNT(*) AS total FROM stok_keluar')->fetch_assoc()['total'] ?? 0);
    if ($stokKeluarCount < 5) {
        $sepatuIdsResult = $conn->query('SELECT id FROM sepatu ORDER BY id');
        $sepatuIds = [];
        while ($row = $sepatuIdsResult->fetch_assoc()) {
            $sepatuIds[] = (int)$row['id'];
        }

        $samples = [
            ['jumlah' => 2, 'keterangan' => 'Terjual ke pelanggan'],
            ['jumlah' => 1, 'keterangan' => 'Retur dari distributor'],
            ['jumlah' => 3, 'keterangan' => 'Dipindah ke cabang'],
            ['jumlah' => 2, 'keterangan' => 'Barang rusak ringan'],
            ['jumlah' => 1, 'keterangan' => 'Terjual online'],
        ];

        $stmt = $conn->prepare('INSERT INTO stok_keluar (sepatu_id, jumlah, keterangan) VALUES (?, ?, ?)');
        foreach ($samples as $index => $sample) {
            if ($index >= count($sepatuIds)) {
                break;
            }

            $sepatuId = $sepatuIds[$index];
            $stmt->bind_param('iis', $sepatuId, $sample['jumlah'], $sample['keterangan']);
            $stmt->execute();
        }
        $stmt->close();
    }

    // 8. Seeding Akun Admin Default menggunakan hash khusus Anda
    $adminHash = '$2y$10$tobI6BBnL6UrRimvzmpFluBFScVr/O1I1g4.2yMsEsrTbxR3shAW.';
    $conn->query("INSERT INTO users (username, password_hash, role) VALUES ('admin', '$adminHash', 'admin') ON DUPLICATE KEY UPDATE username = username");

    return $status;
}

$schema_status = [];
$stats = [
    'total_sepatu' => 0,
    'total_stok' => 0,
    'rata_harga' => 0,
    'total_merek' => 0
];
$histogram_data = [
    'labels' => [],
    'values' => []
];

if (empty($connection_error)) {
    $schema_status = ensureDatabaseSchema($conn);
    
    // Tarik Statistik untuk Dashboard Indah
    $res_stats = $conn->query("SELECT COUNT(*) as total_items, SUM(stok) as total_stok, AVG(harga) as avg_harga FROM sepatu");
    if ($res_stats) {
        $row_stats = $res_stats->fetch_assoc();
        $stats['total_sepatu'] = (int)$row_stats['total_items'];
        $stats['total_stok'] = (int)$row_stats['total_stok'];
        $stats['rata_harga'] = (float)$row_stats['avg_harga'];
    }
    
    $res_merek = $conn->query("SELECT COUNT(DISTINCT merek) as total_brand FROM sepatu");
    if ($res_merek) {
        $stats['total_merek'] = (int)$res_merek->fetch_assoc()['total_brand'];
    }

    // Hitung Histogram Harga Sepatu
    $ranges = [
        '< 1 Juta' => 0,
        '1.0M - 1.5M' => 0,
        '1.5M - 2.0M' => 0,
        '2.0M - 2.5M' => 0,
        '2.5M - 3.0M' => 0,
        '> 3.0 Juta' => 0
    ];
    $res_hist = $conn->query("SELECT harga FROM sepatu");
    if ($res_hist) {
        while ($row = $res_hist->fetch_assoc()) {
            $price = (int)$row['harga'];
            if ($price < 1000000) $ranges['< 1 Juta']++;
            elseif ($price <= 1500000) $ranges['1.0M - 1.5M']++;
            elseif ($price <= 2000000) $ranges['1.5M - 2.0M']++;
            elseif ($price <= 2500000) $ranges['2.0M - 2.5M']++;
            elseif ($price <= 3000000) $ranges['2.5M - 3.0M']++;
            else $ranges['> 3.0 Juta']++;
        }
    }
    $histogram_data['labels'] = array_keys($ranges);
    $histogram_data['values'] = array_values($ranges);
}

// Deteksi akses langsung oleh browser
$is_direct_access = (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__));

if ($is_direct_access):
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Dashboard - Toko Sepatu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js untuk Histogram -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --bg-canvas: #FDFBF7;
            --bg-gradient-start: #FAF5EF;
            --bg-gradient-end: #EAE0D5;
            --card-bg: rgba(255, 255, 255, 0.85);
            --border-color: rgba(127, 85, 57, 0.12);
            --primary-nude: #7F5539;
            --accent-soft: #B5828F;
            --primary-grad: linear-gradient(135deg, #B5828F 0%, #7F5539 100%);
            --text-primary: #4A3B32;
            --text-secondary: #9C8979;
            --success-color: #6E8E72;
            --danger-color: #C37B7B;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            min-height: 100vh;
            color: var(--text-primary);
            overflow-x: hidden;
            position: relative;
            padding: 40px 20px;
        }

        /* Latar Belakang Glow Estetis */
        body::before, body::after {
            content: '';
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            filter: blur(120px);
            z-index: 0;
            opacity: 0.5;
            pointer-events: none;
        }

        body::before {
            background: #DDB892;
            top: -10%;
            left: 5%;
        }

        body::after {
            background: #B5828F;
            bottom: -10%;
            right: 5%;
        }

        .dashboard-container {
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .status-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 28px;
            box-shadow: 0 20px 50px -15px rgba(127, 85, 57, 0.08);
            padding: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(127, 85, 57, 0.08);
            border-radius: 20px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(127, 85, 57, 0.05);
            background: rgba(255, 255, 255, 0.85);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: rgba(181, 130, 143, 0.15);
            color: var(--accent-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 12px;
        }

        .status-header-icon {
            width: 64px;
            height: 64px;
            background-image: linear-gradient(135deg, #EDE0D4 0%, #DDB892 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(127, 85, 57, 0.08);
            border: 2px solid #FFFFFF;
        }

        .status-header-icon i {
            font-size: 1.6rem;
            color: #7F5539;
        }

        .status-list {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            border: 1px solid rgba(127, 85, 57, 0.08);
            padding: 20px;
        }

        .status-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 0;
            border-bottom: 1px solid rgba(127, 85, 57, 0.05);
        }

        .status-item:last-child {
            border-bottom: none;
        }

        .badge-success {
            background-color: rgba(110, 142, 114, 0.12);
            color: var(--success-color);
            border: 1px solid rgba(110, 142, 114, 0.2);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .badge-danger {
            background-color: rgba(195, 123, 123, 0.12);
            color: var(--danger-color);
            border: 1px solid rgba(195, 123, 123, 0.2);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .btn-action {
            background: var(--primary-grad);
            border: none;
            border-radius: 16px;
            padding: 15px 30px;
            font-weight: 700;
            font-size: 0.95rem;
            color: white;
            transition: all 0.4s ease;
            box-shadow: 0 4px 20px rgba(127, 85, 57, 0.15);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(127, 85, 57, 0.25);
            opacity: 0.95;
            color: white;
        }

        .chart-container {
            background: rgba(255, 255, 255, 0.55);
            border: 1px solid rgba(127, 85, 57, 0.08);
            border-radius: 24px;
            padding: 24px;
            min-height: 280px;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="status-card">
        
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-5">
            <div class="d-flex align-items-center gap-3">
                <div class="status-header-icon">
                    <i class="bi bi-intersect"></i>
                </div>
                <div>
                    <h2 class="fw-bold m-0" style="letter-spacing: -0.5px;">Dashboard Database</h2>
                    <p class="text-muted m-0 small">Manajemen Tabel & Analitik Toko Sepatu</p>
                </div>
            </div>
            
            <?php if (empty($connection_error)): ?>
                <a href="login.php" class="btn btn-action shadow-sm">
                    Lanjut ke Login <i class="bi bi-arrow-right-short fs-5"></i>
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($connection_error)): ?>
            <!-- Error Koneksi Database -->
            <div class="alert alert-danger border-0 rounded-4 p-4 mb-4" style="background: rgba(195,123,123,0.1); color: var(--danger-color);">
                <div class="fw-bold mb-1 fs-5"><i class="bi bi-exclamation-octagon-fill me-2"></i>Koneksi Gagal Terbuka!</div>
                <p class="m-0 mb-3 small"><?php echo htmlspecialchars($connection_error); ?></p>
                <p class="small text-muted m-0">Pastikan modul Apache & MySQL di aplikasi XAMPP, MAMP atau Laragon Anda telah diaktifkan.</p>
            </div>
            <div class="text-center mt-4">
                <a href="db.php" class="btn btn-action w-100 py-3">
                    Coba Sambungkan Ulang <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        <?php else: ?>
            
            <!-- Real-time Statistics Cards -->
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color: var(--text-primary);">
                <i class="bi bi-bar-chart-fill text-muted"></i> Statistik Inventaris Sepatu
            </h5>
            <div class="row g-3 mb-5">
                <div class="col-6 col-md-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
                        <span class="text-secondary small fw-medium">Total Produk</span>
                        <h3 class="fw-bold m-0 mt-1"><?php echo number_format($stats['total_sepatu']); ?></h3>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon" style="background: rgba(110, 142, 114, 0.15); color: var(--success-color);"><i class="bi bi-layers"></i></div>
                        <span class="text-secondary small fw-medium">Total Stok</span>
                        <h3 class="fw-bold m-0 mt-1"><?php echo number_format($stats['total_stok']); ?></h3>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon" style="background: rgba(221, 184, 146, 0.15); color: #7F5539;"><i class="bi bi-tag"></i></div>
                        <span class="text-secondary small fw-medium">Total Brand</span>
                        <h3 class="fw-bold m-0 mt-1"><?php echo number_format($stats['total_merek']); ?></h3>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon" style="background: rgba(181, 130, 143, 0.15); color: var(--accent-soft);"><i class="bi bi-currency-dollar"></i></div>
                        <span class="text-secondary small fw-medium">Rata-rata Harga</span>
                        <h4 class="fw-bold m-0 mt-2" style="font-size: 1.15rem; color: var(--text-primary);">
                            Rp <?php echo number_format($stats['rata_harga'], 0, ',', '.'); ?>
                        </h4>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Left: Status Checklist -->
                <div class="col-lg-5">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color: var(--text-primary);">
                        <i class="bi bi-shield-check text-muted"></i> Status Integrasi Sistem
                    </h5>
                    <div class="status-list mb-4">
                        <div class="status-item">
                            <span class="small fw-semibold text-secondary">Status Server</span>
                            <span class="badge-success"><i class="bi bi-check-circle-fill me-1"></i> Terhubung</span>
                        </div>
                        <div class="status-item">
                            <span class="small fw-semibold text-secondary">Database: <?php echo htmlspecialchars($dbname); ?></span>
                            <span class="badge-success"><i class="bi bi-check-circle-fill me-1"></i> Aktif</span>
                        </div>
                        <div class="status-item">
                            <span class="small fw-semibold text-secondary">Tabel 'sepatu'</span>
                            <span class="<?php echo $schema_status['sepatu'] ? 'badge-success' : 'badge-danger'; ?>">
                                <i class="bi <?php echo $schema_status['sepatu'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?> me-1"></i>
                                <?php echo $schema_status['sepatu'] ? 'Siap' : 'Gagal'; ?>
                            </span>
                        </div>
                        <div class="status-item">
                            <span class="small fw-semibold text-secondary">Tabel 'users' (Admin)</span>
                            <span class="<?php echo $schema_status['users'] ? 'badge-success' : 'badge-danger'; ?>">
                                <i class="bi <?php echo $schema_status['users'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?> me-1"></i>
                                <?php echo $schema_status['users'] ? 'Siap' : 'Gagal'; ?>
                            </span>
                        </div>
                        <div class="status-item">
                            <span class="small fw-semibold text-secondary">Tabel 'pembelian'</span>
                            <span class="<?php echo $schema_status['pembelian'] ? 'badge-success' : 'badge-danger'; ?>">
                                <i class="bi <?php echo $schema_status['pembelian'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?> me-1"></i>
                                <?php echo $schema_status['pembelian'] ? 'Siap' : 'Gagal'; ?>
                            </span>
                        </div>
                        <div class="status-item">
                            <span class="small fw-semibold text-secondary">Data Bawaan (Seeding)</span>
                            <span class="badge-success">
                                <i class="bi bi-check-circle-fill me-1"></i>
                                <?php echo $schema_status['seeding'] === 'already_seeded' ? 'Sudah Ada' : 'Sukses'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-3 rounded-4 border border-dashed text-center small mb-3" style="border-color: var(--border-color); background: rgba(255,255,255,0.25);">
                        <i class="bi bi-key-fill text-warning me-1"></i> Akun Login Default: <code class="text-dark fw-bold">admin / admin123</code>
                    </div>
                </div>

                <!-- Right: Histogram Chart -->
                <div class="col-lg-7">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color: var(--text-primary);">
                        <i class="bi bi-graph-up-arrow text-muted"></i> Histogram Distribusi Harga Sepatu
                    </h5>
                    <div class="chart-container">
                        <canvas id="histogramHarga" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>

        <?php endif; ?>
        
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php if (empty($connection_error)): ?>
    const ctx = document.getElementById('histogramHarga').getContext('2d');
    
    const labels = <?php echo json_encode($histogram_data['labels']); ?>;
    const values = <?php echo json_encode($histogram_data['values']); ?>;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Sepatu',
                data: values,
                backgroundColor: 'rgba(181, 130, 143, 0.75)', // Soft Rose Nude
                borderColor: '#7F5539', // Espresso Cokelat
                borderWidth: 1.5,
                borderRadius: 8,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(127, 85, 57, 0.9)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#4A3B32',
                    titleFont: { family: 'Plus Jakarta Sans', weight: 'bold' },
                    bodyFont: { family: 'Plus Jakarta Sans' },
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#9C8979',
                        font: {
                            family: 'Plus Jakarta Sans',
                            size: 11
                        }
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(127, 85, 57, 0.06)'
                    },
                    ticks: {
                        color: '#9C8979',
                        precision: 0,
                        font: {
                            family: 'Plus Jakarta Sans',
                            size: 11
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>
</body>
</html>
<?php
endif; // Akhir deteksi direct access
?>