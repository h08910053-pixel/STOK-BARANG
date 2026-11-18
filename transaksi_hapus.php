<?php
/**
 * Backend Pembatalan / Hapus Riwayat Transaksi Mutasi Stok - StokBarang Pro
 * Menyesuaikan / Mengembalikan Stok Barang Secara Aman Menggunakan Database Transaction
 */
require __DIR__ . "/config/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  exit("Metode HTTP tidak diizinkan.");
}

$id_transaksi = filter_input(INPUT_POST, "id_transaksi", FILTER_VALIDATE_INT);
if (!$id_transaksi || $id_transaksi < 1) {
  http_response_code(400);
  exit("ID Transaksi tidak valid.");
}

// Mulai transaksi database
$conn->begin_transaction();

try {
  // 1. Ambil data transaksi yang ingin dihapus
  $stmt_trans = $conn->prepare(
    "SELECT id_transaksi, id_barang, jenis_transaksi, jumlah FROM transaksi WHERE id_transaksi = ? FOR UPDATE"
  );
  if (!$stmt_trans) {
    throw new Exception("Query persiapan transaksi gagal: " . $conn->error);
  }
  $stmt_trans->bind_param("i", $id_transaksi);
  $stmt_trans->execute();
  $trans = $stmt_trans->get_result()->fetch_assoc();
  $stmt_trans->close();

  if (!$trans) {
    throw new Exception("Data riwayat transaksi tidak ditemukan atau sudah dihapus sebelumnya.");
  }

  $id_barang = (int)$trans["id_barang"];
  $jenis_transaksi = $trans["jenis_transaksi"];
  $jumlah = (int)$trans["jumlah"];

  // 2. Kunci dan ambil stok terkini dari barang terkait
  $stmt_brg = $conn->prepare("SELECT id, nama_barang, stok, satuan FROM barang WHERE id = ? FOR UPDATE");
  if (!$stmt_brg) {
    throw new Exception("Query persiapan cek barang gagal: " . $conn->error);
  }
  $stmt_brg->bind_param("i", $id_barang);
  $stmt_brg->execute();
  $barang = $stmt_brg->get_result()->fetch_assoc();
  $stmt_brg->close();

  // Jika barang masih ada di inventaris, kembalikan stoknya sesuai efek kebalikan dari transaksi
  if ($barang) {
    $stok_saat_ini = (int)$barang["stok"];

    if ($jenis_transaksi === "Masuk") {
      // Transaksi masuk dahulu menambah stok, maka membatalkannya akan mengurangi stok
      if ($stok_saat_ini < $jumlah) {
        $conn->rollback();
        header("Location: transaksi_riwayat.php?error=hapus_stok_kurang");
        exit;
      }
      $stok_baru = $stok_saat_ini - $jumlah;
    } else {
      // Transaksi keluar dahulu mengurangi stok, maka membatalkannya akan mengembalikan stok
      $stok_baru = $stok_saat_ini + $jumlah;
    }

    // Update stok barang
    $stmt_update = $conn->prepare("UPDATE barang SET stok = ? WHERE id = ?");
    if (!$stmt_update) {
      throw new Exception("Query update barang gagal: " . $conn->error);
    }
    $stmt_update->bind_param("ii", $stok_baru, $id_barang);
    $stmt_update->execute();
    $stmt_update->close();
  }

  // 3. Hapus data dari tabel transaksi
  $stmt_del = $conn->prepare("DELETE FROM transaksi WHERE id_transaksi = ?");
  if (!$stmt_del) {
    throw new Exception("Query delete transaksi gagal: " . $conn->error);
  }
  $stmt_del->bind_param("i", $id_transaksi);
  $stmt_del->execute();
  $stmt_del->close();

  // 4. Komit seluruh perubahan
  $conn->commit();

  header("Location: transaksi_riwayat.php?status=hapus_sukses");
  exit;

} catch (Exception $e) {
  $conn->rollback();
  error_log("Hapus transaksi gagal: " . $e->getMessage());
  http_response_code(500);
  echo "<!doctype html><html lang='id'><head><meta charset='utf-8'><title>Error Hapus Transaksi</title>";
  echo "<link rel='stylesheet' href='assets/style.css'></head><body><div class='container-sm' style='margin-top:40px;'>";
  echo "<div class='card'>";
  echo "<h2 style='color:var(--danger-600); margin-bottom:12px;'>Gagal Menghapus Transaksi</h2>";
  echo "<p style='margin-bottom:20px;'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, "UTF-8") . "</p>";
  echo "<a href='transaksi_riwayat.php' class='btn btn-primary'>Kembali ke Riwayat</a>";
  echo "</div></div></body></html>";
  exit;
}
