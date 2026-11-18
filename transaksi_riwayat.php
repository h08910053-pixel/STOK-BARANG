<?php
/**
 * Halaman Riwayat Transaksi Mutasi Stok - StokBarang Pro
 * Menampilkan histori pergerakan stok barang (Masuk & Keluar) hasil JOIN transaksi dan barang
 */
require __DIR__ . "/config/koneksi.php";

function e($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Tangkap filter dari URL (jika ada)
$filter_jenis = trim($_GET["jenis"] ?? "semua");
if (!in_array($filter_jenis, ["semua", "Masuk", "Keluar"], true)) {
  $filter_jenis = "semua";
}
$filter_q = trim($_GET["q"] ?? "");

// ----------------------------------------------------------------------------
// 1. Hitung Ringkasan KPI Riwayat Transaksi (Aggregasi)
// ----------------------------------------------------------------------------
$kpi_query = "
  SELECT 
    COUNT(*) AS total_transaksi,
    SUM(CASE WHEN jenis_transaksi = 'Masuk' THEN jumlah ELSE 0 END) AS total_masuk,
    SUM(CASE WHEN jenis_transaksi = 'Keluar' THEN jumlah ELSE 0 END) AS total_keluar,
    COUNT(CASE WHEN jenis_transaksi = 'Masuk' THEN 1 END) AS count_masuk,
    COUNT(CASE WHEN jenis_transaksi = 'Keluar' THEN 1 END) AS count_keluar
  FROM transaksi
";
$kpi_result = $conn->query($kpi_query);
$kpi_data = $kpi_result ? $kpi_result->fetch_assoc() : [
  "total_transaksi" => 0,
  "total_masuk" => 0,
  "total_keluar" => 0,
  "count_masuk" => 0,
  "count_keluar" => 0
];

$total_transaksi = (int)($kpi_data["total_transaksi"] ?? 0);
$total_masuk     = (int)($kpi_data["total_masuk"] ?? 0);
$total_keluar    = (int)($kpi_data["total_keluar"] ?? 0);
$count_masuk     = (int)($kpi_data["count_masuk"] ?? 0);
$count_keluar    = (int)($kpi_data["count_keluar"] ?? 0);

// ----------------------------------------------------------------------------
// 2. Ambil Data Riwayat Transaksi JOIN Barang
// ----------------------------------------------------------------------------
$sql = "
  SELECT 
    t.id_transaksi,
    t.tanggal,
    t.jenis_transaksi,
    t.jumlah,
    t.keterangan,
    t.created_at,
    b.id AS id_barang,
    b.kode_barang,
    b.nama_barang,
    b.satuan,
    b.stok AS stok_terkini
  FROM transaksi t
  JOIN barang b ON t.id_barang = b.id
";

$where_clauses = [];
$params = [];
$types = "";

if ($filter_jenis !== "semua") {
  $where_clauses[] = "t.jenis_transaksi = ?";
  $params[] = $filter_jenis;
  $types .= "s";
}

if ($filter_q !== "") {
  $where_clauses[] = "(b.nama_barang LIKE ? OR b.kode_barang LIKE ? OR t.keterangan LIKE ?)";
  $searchTerm = "%" . $filter_q . "%";
  $params[] = $searchTerm;
  $params[] = $searchTerm;
  $params[] = $searchTerm;
  $types .= "sss";
}

if (!empty($where_clauses)) {
  $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY t.tanggal DESC, t.id_transaksi DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$transaksi_list = [];
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $transaksi_list[] = $row;
  }
}
$stmt->close();

// Notifikasi Status
$messages = [
  "tambah_sukses" => "Transaksi mutasi stok berhasil dicatat dan stok inventaris telah diperbarui.",
  "hapus_sukses"  => "Riwayat transaksi berhasil dihapus dan stok barang telah disesuaikan kembali."
];
$errors = [
  "hapus_stok_kurang" => "Pembatalan gagal: sisa stok barang di gudang saat ini lebih kecil dari jumlah transaksi masuk yang ingin ditarik!"
];

$status = $_GET["status"] ?? "";
$error_code = $_GET["error"] ?? "";
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Transaksi Stok - StokBarang Pro</title>
  <link rel="stylesheet" href="assets/style.css">
  <style>
    .kpi-grid-3 {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
      margin-bottom: 28px;
    }
    @media (max-width: 900px) {
      .kpi-grid-3 {
        grid-template-columns: 1fr;
        gap: 12px;
      }
    }
  </style>
</head>
<body>

  <!-- Top Sticky Navigation Bar -->
  <?php
  $active_page = 'transaksi_riwayat';
  $navbar_action_button = '
    <a href="transaksi_tambah.php" class="btn btn-primary" title="Catat Mutasi Stok">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
      </svg>
      Catat Transaksi
    </a>
  ';
  require __DIR__ . "/navbar.php";
  ?>

  <div class="container">

    <!-- Flash Alert Success -->
    <?php if (isset($messages[$status])): ?>
      <div id="toastAlert" class="alert alert-success" role="status">
        <div class="alert-content-group">
          <span><?= htmlspecialchars($messages[$status], ENT_QUOTES, "UTF-8") ?></span>
        </div>
        <button type="button" onclick="document.getElementById('toastAlert').style.display='none'" style="background:none; border:none; color:inherit; cursor:pointer; font-size:1.1rem; padding:0 4px;">&times;</button>
      </div>
    <?php endif; ?>

    <!-- Flash Alert Error -->
    <?php if (isset($errors[$error_code])): ?>
      <div id="toastAlertError" class="alert" style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b;" role="alert">
        <div class="alert-content-group">
          <span style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; background:#ef4444; color:#fff; border-radius:50%; font-size:12px; font-weight:bold;">!</span>
          <span><?= htmlspecialchars($errors[$error_code], ENT_QUOTES, "UTF-8") ?></span>
        </div>
        <button type="button" onclick="document.getElementById('toastAlertError').style.display='none'" style="background:none; border:none; color:inherit; cursor:pointer; font-size:1.1rem; padding:0 4px;">&times;</button>
      </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- RINGKASAN KECIL DI BAGIAN ATAS (KPI TRANSAKSI)                   -->
    <!-- ================================================================ -->
    <div class="kpi-grid-3">
      
      <!-- Total Transaksi -->
      <div class="kpi-card blue">
        <div class="kpi-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="17 1 21 5 17 9"></polyline>
            <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
            <polyline points="7 23 3 19 7 15"></polyline>
            <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
          </svg>
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Total Transaksi</span>
          <span class="kpi-value"><?= number_format($total_transaksi, 0, ",", ".") ?></span>
          <span class="kpi-desc">Akumulasi seluruh catatan mutasi</span>
        </div>
      </div>

      <!-- Total Barang Masuk -->
      <div class="kpi-card emerald">
        <div class="kpi-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 5v14M19 12l-7 7-7-7"/>
          </svg>
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Total Barang Masuk</span>
          <span class="kpi-value" style="color: var(--success-600);">+<?= number_format($total_masuk, 0, ",", ".") ?></span>
          <span class="kpi-desc"><?= $count_masuk ?> kali mutasi masuk gudang</span>
        </div>
      </div>

      <!-- Total Barang Keluar -->
      <div class="kpi-card amber">
        <div class="kpi-icon-box" style="background: var(--danger-light); color: var(--danger-600);">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 19V5M5 12l7-7 7 7"/>
          </svg>
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Total Barang Keluar</span>
          <span class="kpi-value" style="color: var(--danger-600);">-<?= number_format($total_keluar, 0, ",", ".") ?></span>
          <span class="kpi-desc"><?= $count_keluar ?> kali mutasi keluar gudang</span>
        </div>
      </div>

    </div>

    <!-- ================================================================ -->
    <!-- CARD UTAMA: FILTER & TABEL RIWAYAT TRANSAKSI                     -->
    <!-- ================================================================ -->
    <div class="card">
      
      <!-- Table Toolbar & Filter -->
      <div class="table-toolbar">
        
        <!-- Live Search Box -->
        <div class="search-box-wrapper">
          <span class="search-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
          </span>
          <input type="text" id="searchInput" class="search-input" 
                 placeholder="Cari nama barang, kode, atau keterangan..." 
                 value="<?= e($filter_q) ?>" autocomplete="off">
        </div>

        <!-- Filter Tab Jenis Transaksi: Semua / Masuk / Keluar -->
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
          <div class="filter-tabs">
            <a href="transaksi_riwayat.php?jenis=semua<?= $filter_q !== '' ? '&q=' . urlencode($filter_q) : '' ?>" 
               class="filter-tab <?= ($filter_jenis === 'semua') ? 'active' : '' ?>" id="filterSemua">
              Semua (<?= $total_transaksi ?>)
            </a>
            <a href="transaksi_riwayat.php?jenis=Masuk<?= $filter_q !== '' ? '&q=' . urlencode($filter_q) : '' ?>" 
               class="filter-tab <?= ($filter_jenis === 'Masuk') ? 'active' : '' ?>" id="filterMasuk">
              &darr; Masuk (<?= $count_masuk ?>)
            </a>
            <a href="transaksi_riwayat.php?jenis=Keluar<?= $filter_q !== '' ? '&q=' . urlencode($filter_q) : '' ?>" 
               class="filter-tab <?= ($filter_jenis === 'Keluar') ? 'active' : '' ?>" id="filterKeluar">
              &uarr; Keluar (<?= $count_keluar ?>)
            </a>
          </div>

          <span class="items-counter-badge" id="counterBadge">
            Menampilkan <?= count($transaksi_list) ?> mutasi
          </span>
        </div>

      </div>

      <!-- Tabel Responsif Riwayat Transaksi -->
      <div class="table-responsive">
        <table id="transaksiTable">
          <thead>
            <tr>
              <th style="width: 130px;">Tanggal</th>
              <th style="width: 120px;">Kode</th>
              <th>Nama Barang</th>
              <th style="width: 140px; text-align: center;">Jenis Transaksi</th>
              <th style="width: 130px; text-align: right;">Jumlah</th>
              <th>Keterangan / Catatan</th>
              <th class="col-aksi">Aksi</th>
            </tr>
          </thead>
          <tbody id="tableBody">
          <?php if (!empty($transaksi_list)): ?>
            <?php foreach ($transaksi_list as $row): 
              $isMasuk = ($row["jenis_transaksi"] === "Masuk");
              $badgeClass = $isMasuk ? "badge-masuk" : "badge-keluar";
              $iconSymbol = $isMasuk ? "↓" : "↑";
              $qtyPrefix = $isMasuk ? "+" : "-";
              $qtyColor = $isMasuk ? "var(--success-600)" : "var(--danger-600)";
              
              // Format tanggal ramah pengguna
              $formattedDate = date("d M Y", strtotime($row["tanggal"]));
            ?>
            <tr class="table-row-item" data-jenis="<?= e($row['jenis_transaksi']) ?>">
              <!-- Tanggal -->
              <td>
                <span style="font-weight: 600; color: var(--slate-800); white-space: nowrap;">
                  <?= $formattedDate ?>
                </span>
                <div style="font-size: 0.725rem; color: var(--slate-400);">
                  <?= date("H:i", strtotime($row["created_at"])) ?> WIB
                </div>
              </td>

              <!-- Kode Barang -->
              <td>
                <span class="code-pill"><?= e($row["kode_barang"]) ?></span>
              </td>

              <!-- Nama Barang -->
              <td>
                <span class="item-name-cell"><?= e($row["nama_barang"]) ?></span>
                <div style="font-size: 0.75rem; color: var(--slate-500);">
                  Stok saat ini: <strong><?= number_format($row["stok_terkini"], 0, ",", ".") ?> <?= e($row["satuan"]) ?></strong>
                </div>
              </td>

              <!-- Jenis Transaksi (Badge Pil) -->
              <td style="text-align: center;">
                <span class="badge-transaksi <?= $badgeClass ?>">
                  <span class="icon"><?= $iconSymbol ?></span>
                  <?= e($row["jenis_transaksi"]) ?>
                </span>
              </td>

              <!-- Jumlah Disertai Satuan -->
              <td style="text-align: right;">
                <span style="font-family: var(--font-mono); font-weight: 700; font-size: 0.95rem; color: <?= $qtyColor ?>;">
                  <?= $qtyPrefix ?><?= number_format($row["jumlah"], 0, ",", ".") ?>
                </span>
                <span style="font-size: 0.8rem; color: var(--slate-500); margin-left: 3px;">
                  <?= e($row["satuan"]) ?>
                </span>
              </td>

              <!-- Keterangan -->
              <td>
                <?php if (!empty($row["keterangan"])): ?>
                  <span style="font-size: 0.875rem; color: var(--slate-700);">
                    <?= e($row["keterangan"]) ?>
                  </span>
                <?php else: ?>
                  <span style="color: var(--slate-400); font-style: italic; font-size: 0.825rem;">
                    Tanpa keterangan
                  </span>
                <?php endif; ?>
              </td>

              <!-- Tombol Aksi Hapus Riwayat -->
              <td class="col-aksi">
                <div class="table-actions">
                  <form action="transaksi_hapus.php" method="post" style="display:inline; margin:0;"
                        onsubmit="return confirm('Peringatan Pembatalan Transaksi:\n\nApakah Anda yakin ingin menghapus catatan mutasi <?= e($row['jenis_transaksi']) ?> sebanyak <?= $row['jumlah'] ?> <?= e($row['satuan']) ?> untuk barang \'<?= e($row['nama_barang']) ?>\'?\n\nPenghapusan ini akan otomatis mengembalikan stok ke kondisi sebelum mutasi dicatat.')">
                    <input type="hidden" name="id_transaksi" value="<?= (int)$row["id_transaksi"] ?>">
                    <button type="submit" class="btn-danger" title="Batalkan & Hapus Catatan Transaksi Ini">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      </svg>
                      Hapus
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr id="emptyRow">
              <td colspan="7" class="empty-state">
                <div class="empty-state-icon">
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="17 1 21 5 17 9"></polyline>
                    <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
                    <polyline points="7 23 3 19 7 15"></polyline>
                    <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
                  </svg>
                </div>
                <h3>Belum Ada Riwayat Transaksi</h3>
                <p>Belum ada catatan mutasi barang masuk maupun keluar yang terdaftar.</p>
                <a href="transaksi_tambah.php" class="btn btn-primary">+ Catat Transaksi Sekarang</a>
              </td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

  <!-- Client-side Interactive Filter Script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchInput');
      const tableBody = document.getElementById('tableBody');
      const counterBadge = document.getElementById('counterBadge');

      if (!searchInput || !tableBody) return;

      searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const rows = tableBody.querySelectorAll('tr.table-row-item');
        let visibleCount = 0;

        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          if (text.includes(query)) {
            row.style.display = '';
            visibleCount++;
          } else {
            row.style.display = 'none';
          }
        });

        if (counterBadge) {
          counterBadge.textContent = 'Menampilkan ' + visibleCount + ' dari ' + rows.length + ' mutasi';
        }
      });

      // Auto-dismiss Flash Toasts
      const toast = document.getElementById('toastAlert');
      if (toast) {
        setTimeout(() => {
          toast.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
          toast.style.opacity = '0';
          toast.style.transform = 'translateY(-10px)';
          setTimeout(() => toast.remove(), 500);
        }, 4000);
      }

      const toastErr = document.getElementById('toastAlertError');
      if (toastErr) {
        setTimeout(() => {
          toastErr.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
          toastErr.style.opacity = '0';
          toastErr.style.transform = 'translateY(-10px)';
          setTimeout(() => toastErr.remove(), 600);
        }, 6000);
      }
    });
  </script>
</body>
</html>
