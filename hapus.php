<?php
/**
 * Backend Hapus Data Barang - StokBarang Pro
 */
require __DIR__ . "/config/koneksi.php";

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
if (!$id || $id < 1) { 
  http_response_code(400); 
  exit("ID barang tidak valid."); 
}

$stmt = $conn->prepare("DELETE FROM barang WHERE id = ?");
if (!$stmt) {
  http_response_code(500);
  exit("Gagal mempersiapkan query: " . $conn->error);
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  $stmt->close();
  header("Location: index.php?status=hapus_sukses");
  exit;
}

$error_msg = $stmt->error;
error_log("Delete barang gagal: " . $error_msg);
$stmt->close();

http_response_code(500);
echo "<!doctype html><html lang='id'><head><meta charset='utf-8'><title>Gagal Menghapus</title>";
echo "<link rel='stylesheet' href='assets/style.css'></head><body><div class='container-sm' style='margin-top:40px;'>";
echo "<div class='card'>";
echo "<h2 style='color:var(--danger-600); margin-bottom:12px;'>⚠️ Data Barang Tidak Dapat Dihapus</h2>";
echo "<p style='margin-bottom:20px; color:var(--slate-700);'>Barang ini mungkin memiliki keterkaitan dengan data riwayat transaksi aktif yang mencegah penghapusan langsung.</p>";
echo "<a href='index.php' class='btn btn-primary'>Kembali ke Dashboard</a>";
echo "</div></div></body></html>";
?>
