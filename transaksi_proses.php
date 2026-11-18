<?php
/**
 * Backend Proses Transaksi Mutasi Stok (Masuk / Keluar) - StokBarang Pro
 * Menggunakan Database Transaction (mysqli_begin_transaction) untuk Konsistensi Data Stok
 */
require __DIR__ . "/config/koneksi.php";

// Pastikan metode request adalah POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  exit("Metode HTTP tidak diizinkan. Silakan kirim data melalui form transaksi.");
}

// ----------------------------------------------------------------------------
// 1. Ambil & Sanitasi Parameter Input
// ----------------------------------------------------------------------------
$tanggal_raw   = trim($_POST["tanggal"] ?? "");
$id_barang_raw = $_POST["id_barang"] ?? "";
$jenis_raw     = trim($_POST["jenis_transaksi"] ?? "");
$jumlah_raw    = $_POST["jumlah"] ?? "";
$keterangan    = trim($_POST["keterangan"] ?? "");

$errors = [];

// Validasi Tanggal
if ($tanggal_raw === "") {
  $errors[] = "Tanggal transaksi wajib diisi.";
} else {
  $d = DateTime::createFromFormat("Y-m-d", $tanggal_raw);
  if (!$d || $d->format("Y-m-d") !== $tanggal_raw) {
    $errors[] = "Format tanggal tidak valid (gunakan format YYYY-MM-DD).";
  }
}
$tanggal = $tanggal_raw;

// Validasi ID Barang
$id_barang = filter_var($id_barang_raw, FILTER_VALIDATE_INT);
if ($id_barang === false || $id_barang < 1) {
  $errors[] = "Pilihan barang inventaris tidak valid.";
}

// Validasi Jenis Transaksi
if (!in_array($jenis_raw, ["Masuk", "Keluar"], true)) {
  $errors[] = "Jenis transaksi harus 'Masuk' atau 'Keluar'.";
}
$jenis_transaksi = $jenis_raw;

// Validasi Kuantitas Jumlah
$jumlah = filter_var($jumlah_raw, FILTER_VALIDATE_INT);
if ($jumlah === false || $jumlah < 1) {
  $errors[] = "Jumlah kuantitas mutasi harus berupa bilangan bulat positif (minimal 1 unit).";
}

// Jika terdapat kegagalan validasi dasar input
if (!empty($errors)) {
  http_response_code(422);
  echo "<!doctype html><html lang='id'><head><meta charset='utf-8'><title>Validasi Gagal</title>";
  echo "<link rel='stylesheet' href='assets/style.css'></head><body><div class='container-sm' style='margin-top:40px;'>";
  echo "<div class='card'>";
  echo "<h2 style='color:var(--danger-600); margin-bottom:12px;'>⚠️ Input Transaksi Tidak Valid</h2>";
  echo "<ul style='padding-left:20px; margin-bottom:20px;'>";
  foreach ($errors as $err) {
    echo "<li>" . htmlspecialchars($err, ENT_QUOTES, "UTF-8") . "</li>";
  }
  echo "</ul>";
  echo "<a href='transaksi_tambah.php' class='btn btn-primary'>Kembali ke Form</a>";
  echo "</div></div></body></html>";
  exit;
}

// ----------------------------------------------------------------------------
// 2. Eksekusi Transaksi Database Terstruktur
// ----------------------------------------------------------------------------
/**
 * Fungsi untuk mencatat mutasi transaksi dan mengupdate stok secara atomik
 *
 * @param mysqli $conn
 * @param string $tanggal
 * @param int $id_barang
 * @param string $jenis_transaksi
 * @param int $jumlah
 * @param string $keterangan
 * @return array ['success' => bool, 'message' => string, 'stok_akhir' => int]
 */
function proses_mutasi_stok($conn, $tanggal, $id_barang, $jenis_transaksi, $jumlah, $keterangan) {
  // Mulai Transaksi Database MySQL
  $conn->begin_transaction();

  try {
    // 1. Kunci dan ambil stok saat ini dari tabel barang (FOR UPDATE)
    $stmt_check = $conn->prepare("SELECT id, nama_barang, stok, satuan FROM barang WHERE id = ? FOR UPDATE");
    if (!$stmt_check) {
      throw new Exception("Gagal mempersiapkan query cek barang: " . $conn->error);
    }
    $stmt_check->bind_param("i", $id_barang);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    $barang = $res_check->fetch_assoc();
    $stmt_check->close();

    if (!$barang) {
      throw new Exception("Barang dengan ID {$id_barang} tidak ditemukan.");
    }

    $stok_saat_ini = (int)$barang["stok"];

    // 2. Validasi Server-Side: Cek ketersediaan stok jika jenis transaksi adalah 'Keluar'
    if ($jenis_transaksi === "Keluar" && $stok_saat_ini < $jumlah) {
      // Rollback transaksi dan kembalikan status error stok kurang
      $conn->rollback();
      return [
        "success" => false,
        "code"    => "stok_kurang",
        "message" => "Stok barang '{$barang['nama_barang']}' saat ini ({$stok_saat_ini} {$barang['satuan']}) tidak mencukupi untuk dikeluarkan sebanyak {$jumlah} {$barang['satuan']}."
      ];
    }

    // 3. Hitung stok baru dengan operator aritmatika (+ atau -)
    if ($jenis_transaksi === "Masuk") {
      $stok_baru = $stok_saat_ini + $jumlah;
    } else {
      $stok_baru = $stok_saat_ini - $jumlah;
    }

    // 4. Masukkan data ke tabel transaksi
    $stmt_insert = $conn->prepare(
      "INSERT INTO transaksi (tanggal, id_barang, jenis_transaksi, jumlah, keterangan) VALUES (?, ?, ?, ?, ?)"
    );
    if (!$stmt_insert) {
      throw new Exception("Gagal mempersiapkan query insert transaksi: " . $conn->error);
    }
    $stmt_insert->bind_param("sisis", $tanggal, $id_barang, $jenis_transaksi, $jumlah, $keterangan);
    if (!$stmt_insert->execute()) {
      throw new Exception("Gagal menyimpan transaksi: " . $stmt_insert->error);
    }
    $stmt_insert->close();

    // 5. Update kolom stok pada tabel barang
    $stmt_update = $conn->prepare("UPDATE barang SET stok = ? WHERE id = ?");
    if (!$stmt_update) {
      throw new Exception("Gagal mempersiapkan query update stok barang: " . $conn->error);
    }
    $stmt_update->bind_param("ii", $stok_baru, $id_barang);
    if (!$stmt_update->execute()) {
      throw new Exception("Gagal memperbarui stok barang: " . $stmt_update->error);
    }
    $stmt_update->close();

    // 6. Komit seluruh perubahan ke database
    $conn->commit();

    return [
      "success"     => true,
      "stok_akhir"  => $stok_baru,
      "nama_barang" => $barang["nama_barang"]
    ];

  } catch (Exception $e) {
    // Batalkan seluruh perubahan jika terjadi kesalahan
    $conn->rollback();
    error_log("Transaksi mutasi gagal: " . $e->getMessage());
    return [
      "success" => false,
      "code"    => "server_error",
      "message" => $e->getMessage()
    ];
  }
}

// Jalankan proses mutasi
$hasil = proses_mutasi_stok($conn, $tanggal, $id_barang, $jenis_transaksi, $jumlah, $keterangan);

if ($hasil["success"]) {
  // Redirect ke halaman riwayat transaksi dengan notifikasi sukses
  header("Location: transaksi_riwayat.php?status=tambah_sukses");
  exit;
} elseif (isset($hasil["code"]) && $hasil["code"] === "stok_kurang") {
  // Redirect kembali ke form dengan parameter error stok kurang
  header("Location: transaksi_tambah.php?error=stok_kurang&id_barang=" . $id_barang . "&jenis=Keluar");
  exit;
} else {
  // Gagal karena server error
  http_response_code(500);
  echo "<!doctype html><html lang='id'><head><meta charset='utf-8'><title>Error Sistem</title>";
  echo "<link rel='stylesheet' href='assets/style.css'></head><body><div class='container-sm' style='margin-top:40px;'>";
  echo "<div class='card'>";
  echo "<h2 style='color:var(--danger-600); margin-bottom:12px;'>Kesalahan Server</h2>";
  echo "<p style='margin-bottom:20px;'>Transaksi belum dapat diproses: " . htmlspecialchars($hasil["message"] ?? "Terjadi kesalahan internal.", ENT_QUOTES, "UTF-8") . "</p>";
  echo "<a href='transaksi_tambah.php' class='btn btn-primary'>Kembali ke Form</a>";
  echo "</div></div></body></html>";
  exit;
}
