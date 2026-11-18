-- ============================================================================
-- SISTEM INFORMASI TRANSAKSI STOK BARANG (StokBarang Pro)
-- Skema Database: Tabel Transaksi Mutasi Stok
-- Database Target: db_stok_barang
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `db_stok_barang`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `db_stok_barang`;

-- ----------------------------------------------------------------------------
-- 1. Struktur Tabel `transaksi` (Tanpa Foreign Key ketat agar 100% kompatibel)
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
-- 2. Data Awal Contoh Riwayat Transaksi (Opsional)
-- ----------------------------------------------------------------------------
INSERT INTO `transaksi` (`tanggal`, `id_barang`, `jenis_transaksi`, `jumlah`, `keterangan`)
SELECT CURDATE(), id, 'Masuk', 25, 'Penerimaan stok awal dari Supplier CV Sentosa Abadi'
FROM `barang` LIMIT 1;
