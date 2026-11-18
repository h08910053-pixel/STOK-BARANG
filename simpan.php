<?php
/**
 * Backend Simpan Data Barang Baru - StokBarang Pro
 */
require __DIR__ . "/config/koneksi.php";

$kode     = trim($_POST["kode_barang"] ?? "");
$nama     = trim($_POST["nama_barang"] ?? "");
$kategori = trim($_POST["kategori"] ?? "");
$satuan   = trim($_POST["satuan"] ?? "");
$stokRaw  = $_POST["stok"] ?? "";
$hargaRaw = $_POST["harga"] ?? "";

$errors = [];
if ($kode === "" || mb_strlen($kode) > 30) {
  $errors[] = "Kode barang wajib diisi (maksimal 30 karakter).";
}
if ($nama === "" || mb_strlen($nama) > 100) {
  $errors[] = "Nama barang wajib diisi (maksimal 100 karakter).";
}
if ($kategori === "") {
  $errors[] = "Kategori barang wajib diisi.";
}
if ($satuan === "") {
  $errors[] = "Satuan barang wajib diisi.";
}
if (filter_var($stokRaw, FILTER_VALIDATE_INT) === false || (int)$stokRaw < 0) {
  $errors[] = "Kuantitas stok harus bilangan bulat non-negatif (0 atau lebih).";
}
if (!is_numeric($hargaRaw) || (float)$hargaRaw < 0) {
  $errors[] = "Harga barang harus berupa angka non-negatif.";
}

if (!empty($errors)) {
  http_response_code(422);
  echo "<!doctype html><html lang='id'><head><meta charset='utf-8'><title>Validasi Gagal</title>";
  echo "<link rel='stylesheet' href='assets/style.css'></head><body><div class='container-sm' style='margin-top:40px;'>";
  echo "<div class='card'>";
  echo "<h2 style='color:var(--danger-600); margin-bottom:12px;'>⚠️ Data Barang Tidak Valid</h2>";
  echo "<ul style='padding-left:20px; margin-bottom:20px; color:var(--slate-700);'>";
  foreach ($errors as $err) {
    echo "<li>" . htmlspecialchars($err, ENT_QUOTES, "UTF-8") . "</li>";
  }
  echo "</ul>";
  echo "<a href='tambah.php' class='btn btn-primary'>Kembali ke Form Tambah</a>";
  echo "</div></div></body></html>";
  exit;
}

$stok  = (int)$stokRaw;
$harga = (float)$hargaRaw;

$stmt = $conn->prepare(
  "INSERT INTO barang (kode_barang, nama_barang, kategori, stok, satuan, harga) VALUES (?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
  http_response_code(500);
  exit("Gagal mempersiapkan query: " . $conn->error);
}

$stmt->bind_param("sssisd", $kode, $nama, $kategori, $stok, $satuan, $harga);

if ($stmt->execute()) {
  $stmt->close();
  header("Location: index.php?status=tambah_sukses");
  exit;
}

if ($stmt->errno === 1062) {
  $stmt->close();
  echo "<!doctype html><html lang='id'><head><meta charset='utf-8'><title>Kode Duplikat</title>";
  echo "<link rel='stylesheet' href='assets/style.css'></head><body><div class='container-sm' style='margin-top:40px;'>";
  echo "<div class='card'>";
  echo "<h2 style='color:var(--danger-600); margin-bottom:12px;'>⚠️ Kode Barang Sudah Terdaftar</h2>";
  echo "<p style='margin-bottom:20px; color:var(--slate-700);'>Kode barang <strong>" . htmlspecialchars($kode, ENT_QUOTES, "UTF-8") . "</strong> sudah digunakan oleh produk lain. Silakan gunakan kode unik yang berbeda.</p>";
  echo "<a href='tambah.php' class='btn btn-primary'>Kembali ke Form Tambah</a>";
  echo "</div></div></body></html>";
  exit;
}

error_log("Insert barang gagal: " . $stmt->error);
$stmt->close();
http_response_code(500);
echo "<!doctype html><html lang='id'><head><meta charset='utf-8'><title>Error Sistem</title>";
echo "<link rel='stylesheet' href='assets/style.css'></head><body><div class='container-sm' style='margin-top:40px;'>";
echo "<div class='card'>";
echo "<h2 style='color:var(--danger-600); margin-bottom:12px;'>Kesalahan Server</h2>";
echo "<p style='margin-bottom:20px; color:var(--slate-700);'>Data belum dapat disimpan ke database. Silakan coba kembali.</p>";
echo "<a href='tambah.php' class='btn btn-primary'>Kembali</a>";
echo "</div></div></body></html>";
?>
