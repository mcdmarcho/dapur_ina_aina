-- ============================================
-- DATABASE CAFE
-- RESET DATABASE
-- ============================================

DROP DATABASE IF EXISTS db_cafe;

CREATE DATABASE db_cafe;

USE db_cafe;


-- ============================================
-- 1. TABEL PELANGGAN
-- ============================================

CREATE TABLE pelanggan (
    id_pelanggan INT AUTO_INCREMENT PRIMARY KEY,
    nama_pelanggan VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20)
);


-- ============================================
-- 2. TABEL PRODUK / STOK
-- ============================================

CREATE TABLE produk (
    id_produk INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(100) NOT NULL,
    kategori ENUM('makanan', 'minuman') NOT NULL,
    harga DECIMAL(12,2) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    gambar VARCHAR(255) NOT NULL DEFAULT 'default.jpg'
);


-- ============================================
-- 3. TABEL PESANAN / TRANSAKSI
-- ============================================

CREATE TABLE pesanan (
    id_transaksi VARCHAR(20) PRIMARY KEY,
    id_pelanggan INT NOT NULL,
    tanggal DATETIME NOT NULL,
    status_bayar ENUM('lunas', 'belum bayar') DEFAULT 'belum bayar',
    metode_bayar ENUM('tunai', 'non tunai') DEFAULT NULL,
    total_bayar DECIMAL(12,2) NOT NULL DEFAULT 0,
    uang_bayar DECIMAL(12,2) DEFAULT 0,
    kembalian DECIMAL(12,2) DEFAULT 0,

    FOREIGN KEY (id_pelanggan)
        REFERENCES pelanggan(id_pelanggan)
        ON DELETE CASCADE
);


-- ============================================
-- 4. TABEL DETAIL PESANAN
-- ============================================

CREATE TABLE detail_pesanan (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi VARCHAR(20) NOT NULL,
    id_produk INT NOT NULL,
    harga DECIMAL(12,2) NOT NULL,
    qty INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,

    FOREIGN KEY (id_transaksi)
        REFERENCES pesanan(id_transaksi)
        ON DELETE CASCADE,

    FOREIGN KEY (id_produk)
        REFERENCES produk(id_produk)
        ON DELETE CASCADE
);


-- ============================================
-- 5. DATA PELANGGAN
-- ============================================

INSERT INTO pelanggan
(nama_pelanggan, no_hp)
VALUES
('Agung', '080987654321'),
('Lastari', '081234567890');


-- ============================================
-- 6. DATA PRODUK
-- ============================================

INSERT INTO produk
(nama_produk, kategori, harga, stok, gambar)
VALUES
('Croissant Butter', 'makanan', 18000, 30, 'default.jpg'),
('Salt Bread', 'makanan', 16000, 30, 'default.jpg'),
('Nasi Goreng', 'makanan', 21000, 30, 'default.jpg'),
('Espresso', 'minuman', 20000, 25, 'default.jpg'),
('Latte Ice', 'minuman', 28000, 25, 'default.jpg'),
('Matcha', 'minuman', 30000, 25, 'default.jpg');


-- ============================================
-- 7. DATA PESANAN / TRANSAKSI
-- ============================================

INSERT INTO pesanan
(
    id_transaksi,
    id_pelanggan,
    tanggal,
    status_bayar,
    metode_bayar,
    total_bayar,
    uang_bayar,
    kembalian
)
VALUES
(
    'TRX-001',
    1,
    '2026-09-23 10:15:00',
    'lunas',
    'tunai',
    46000,
    50000,
    4000
),
(
    'TRX-002',
    2,
    '2026-09-23 11:30:00',
    'belum bayar',
    NULL,
    57000,
    0,
    0
);


-- ============================================
-- 8. DATA DETAIL PESANAN
-- ============================================

INSERT INTO detail_pesanan
(
    id_transaksi,
    id_produk,
    harga,
    qty,
    subtotal
)
VALUES
(
    'TRX-001',
    2,
    28000,
    1,
    28000
),
(
    'TRX-001',
    1,
    18000,
    1,
    18000
),
(
    'TRX-002',
    4,
    35000,
    1,
    35000
),
(
    'TRX-002',
    3,
    22000,
    1,
    22000
);


-- ============================================
-- SELESAI
-- ============================================

SELECT 'Database db_cafe berhasil dibuat!' AS status;