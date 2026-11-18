-- ============================================================================
-- PROYEK UAS: SISTEM INFORMASI TRANSAKSI STOK BARANG
-- Basis Data: db_stok_barang
-- Teknologi: MySQL / MariaDB (XAMPP)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `db_stok_barang`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `db_stok_barang`;

-- ----------------------------------------------------------------------------
-- 1. Struktur Tabel `barang` (Master Data Produk & Stok Fisik)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `barang` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_barang` VARCHAR(30) NOT NULL UNIQUE,
  `nama_barang` VARCHAR(100) NOT NULL,
  `kategori` VARCHAR(60) NOT NULL,
  `stok` INT NOT NULL DEFAULT 0,
  `satuan` VARCHAR(20) NOT NULL DEFAULT 'Pcs',
  `harga` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_barang_kode` (`kode_barang`),
  INDEX `idx_barang_kategori` (`kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Struktur Tabel `transaksi` (Mutasi Stok Masuk & Keluar)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id_transaksi` INT AUTO_INCREMENT PRIMARY KEY,
  `tanggal` DATE NOT NULL,
  `id_barang` INT NOT NULL,
  `jenis_transaksi` ENUM('Masuk', 'Keluar') NOT NULL,
  `jumlah` INT NOT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_transaksi_tanggal` (`tanggal`),
  INDEX `idx_transaksi_jenis` (`jenis_transaksi`),
  INDEX `idx_transaksi_barang` (`id_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Data Awal Contoh (Master Data Barang Inventaris)
-- ----------------------------------------------------------------------------
INSERT INTO `barang` (`kode_barang`, `nama_barang`, `kategori`, `stok`, `satuan`, `harga`) VALUES
('BRG-001', 'Kertas HVS A4 70gr PaperOne', 'Alat Tulis Kantor', 45, 'Rim', 48500.00),
('BRG-002', 'Mouse Wireless Silent Logitech M220', 'Elektronik & Gadget', 20, 'Unit', 165000.00),
('BRG-003', 'Tinta Printer Epson Black 003 Original', 'Perlengkapan Kantor', 12, 'Botol', 85000.00),
('BRG-004', 'Flashdisk Sandisk Cruzer Blade 64GB', 'Elektronik & Gadget', 28, 'Unit', 72000.00),
('BRG-005', 'Pulpen Gel Joyko GP-182 0.5mm Hitam', 'Alat Tulis Kantor', 120, 'Pcs', 3500.00)
ON DUPLICATE KEY UPDATE `nama_barang` = VALUES(`nama_barang`);

-- ----------------------------------------------------------------------------
-- 4. Data Awal Contoh (Riwayat Mutasi Transaksi Masuk & Keluar)
-- ----------------------------------------------------------------------------
INSERT INTO `transaksi` (`tanggal`, `id_barang`, `jenis_transaksi`, `jumlah`, `keterangan`)
SELECT CURDATE() - INTERVAL 2 DAY, id, 'Masuk', 50, 'Pengadaan awal stok inventaris kantor'
FROM `barang` WHERE `kode_barang` = 'BRG-001' LIMIT 1;

INSERT INTO `transaksi` (`tanggal`, `id_barang`, `jenis_transaksi`, `jumlah`, `keterangan`)
SELECT CURDATE() - INTERVAL 1 DAY, id, 'Keluar', 5, 'Pengambilan kebutuhan operasional divisi administrasi'
FROM `barang` WHERE `kode_barang` = 'BRG-001' LIMIT 1;
