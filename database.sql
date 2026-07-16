CREATE DATABASE IF NOT EXISTS toko_sepatu
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE toko_sepatu;

-- ============================================
-- Tabel Sepatu
-- ============================================
CREATE TABLE IF NOT EXISTS sepatu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    merek VARCHAR(100) NOT NULL,
    ukuran VARCHAR(20) NOT NULL,
    harga INT NOT NULL,
    stok INT NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Tabel Users
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pembelian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_pembeli VARCHAR(100) NOT NULL,
    sepatu_id INT NOT NULL,
    jumlah INT NOT NULL,
    total_harga INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pembelian_sepatu FOREIGN KEY (sepatu_id) REFERENCES sepatu(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pengeluaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keterangan VARCHAR(150) NOT NULL,
    nominal INT NOT NULL,
    kategori VARCHAR(50) NOT NULL DEFAULT 'Lainnya',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS stok_keluar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sepatu_id INT NOT NULL,
    jumlah INT NOT NULL,
    keterangan VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stok_keluar_sepatu FOREIGN KEY (sepatu_id) REFERENCES sepatu(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- User Login
-- Username : admin
-- Password : admin123
-- ============================================
INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2y$10$tobI6BBnL6UrRimvzmpFluBFScVr/O1I1g4.2yMsEsrTbxR3shAW.', 'admin')
ON DUPLICATE KEY UPDATE username = username;

-- ============================================
-- Data Sepatu
-- ============================================
INSERT INTO sepatu (nama, merek, ukuran, harga, stok, deskripsi) VALUES
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
('Cloudrunner','On Running','41',2950000,4,'Running shoes')

ON DUPLICATE KEY UPDATE nama = VALUES(nama);

INSERT INTO stok_keluar (sepatu_id, jumlah, keterangan)
SELECT id, 2, 'Terjual ke pelanggan' FROM sepatu ORDER BY id LIMIT 1;

INSERT INTO stok_keluar (sepatu_id, jumlah, keterangan)
SELECT id, 1, 'Retur dari distributor' FROM sepatu ORDER BY id LIMIT 1 OFFSET 1;

INSERT INTO stok_keluar (sepatu_id, jumlah, keterangan)
SELECT id, 3, 'Dipindah ke cabang' FROM sepatu ORDER BY id LIMIT 1 OFFSET 2;

INSERT INTO stok_keluar (sepatu_id, jumlah, keterangan)
SELECT id, 2, 'Barang rusak ringan' FROM sepatu ORDER BY id LIMIT 1 OFFSET 3;

INSERT INTO stok_keluar (sepatu_id, jumlah, keterangan)
SELECT id, 1, 'Terjual online' FROM sepatu ORDER BY id LIMIT 1 OFFSET 4;