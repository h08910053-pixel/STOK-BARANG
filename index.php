<?php
require __DIR__ . "/config/koneksi.php";

$result = $conn->query(
  "SELECT id, kode_barang, nama_barang, kategori, stok, satuan, harga
  FROM barang ORDER BY id DESC"
);

function e($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$items = [];
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $items[] = $row;
  }
}

// Summary KPI calculations
$total_jenis = count($items);
$total_stok = array_sum(array_column($items, 'stok'));
$total_nilai = 0;
$stok_menipis = 0;
foreach ($items as $item) {
  $total_nilai += (float)$item['stok'] * (float)$item['harga'];
  if ((int)$item['stok'] <= 5) {
    $stok_menipis++;
  }
}

$messages = [
  "tambah_sukses" => "Barang baru berhasil ditambahkan ke inventaris.",
  "ubah_sukses"   => "Data barang berhasil diperbarui.",
  "hapus_sukses"  => "Barang berhasil dihapus dari sistem."
];
$status = $_GET["status"] ?? "";
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Manajemen Stok Barang</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

  <!-- Top Sticky Navigation Bar -->
  <?php
  $active_page = 'barang';
  $navbar_action_button = '
    <div style="display:flex; align-items:center; gap:8px;">
      <a href="transaksi_tambah.php" class="btn btn-secondary" style="border-radius: var(--radius-md);">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="17 1 21 5 17 9"></polyline>
          <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
          <polyline points="7 23 3 19 7 15"></polyline>
          <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
        </svg>
        Mutasi
      </a>
      <a href="tambah.php" class="btn btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Tambah Barang
      </a>
    </div>
  ';
  require __DIR__ . "/navbar.php";
  ?>

  <div class="container">

    <!-- Flash Alert Message -->
    <?php if (isset($messages[$status])): ?>
      <div id="toastAlert" class="alert alert-success" role="status">
        <div class="alert-content-group">
          <span><?= htmlspecialchars($messages[$status], ENT_QUOTES, "UTF-8") ?></span>
        </div>
        <button type="button" onclick="document.getElementById('toastAlert').style.display='none'" style="background:none; border:none; color:inherit; cursor:pointer; font-size:1.1rem; padding:0 4px;">&times;</button>
      </div>
    <?php endif; ?>

    <!-- KPI Summary Dashboard Widgets -->
    <div class="kpi-grid">
      <div class="kpi-card blue">
        <div class="kpi-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
          </svg>
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Jenis Produk</span>
          <span class="kpi-value" id="kpiTotalJenis"><?= number_format($total_jenis, 0, ",", ".") ?></span>
          <span class="kpi-desc">Total jenis barang terdaftar</span>
        </div>
      </div>

      <div class="kpi-card emerald">
        <div class="kpi-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="20" x2="18" y2="10"></line>
            <line x1="12" y1="20" x2="12" y2="4"></line>
            <line x1="6" y1="20" x2="6" y2="14"></line>
          </svg>
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Total Unit Fisik</span>
          <span class="kpi-value" id="kpiTotalUnit"><?= number_format($total_stok, 0, ",", ".") ?></span>
          <span class="kpi-desc">Total kuantitas di gudang</span>
        </div>
      </div>

      <div class="kpi-card purple">
        <div class="kpi-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
            <line x1="2" y1="10" x2="22" y2="10"></line>
          </svg>
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Nilai Inventaris</span>
          <span class="kpi-value">Rp <?= number_format($total_nilai, 0, ",", ".") ?></span>
          <span class="kpi-desc">Estimasi total nilai aset</span>
        </div>
      </div>

      <div class="kpi-card amber">
        <div class="kpi-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Stok Kritis (≤ 5)</span>
          <span class="kpi-value" style="<?= $stok_menipis > 0 ? 'color: var(--warning-600);' : '' ?>"><?= number_format($stok_menipis, 0, ",", ".") ?></span>
          <span class="kpi-desc">Perlu segera restock</span>
        </div>
      </div>
    </div>

    <!-- Main Table Card -->
    <div class="card">
      <div class="table-toolbar">
        <div class="search-box-wrapper">
          <span class="search-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
          </span>
          <input type="text" id="searchInput" class="search-input" placeholder="Cari kode, nama, atau kategori barang..." autocomplete="off">
        </div>

        <div class="toolbar-actions">
          <span class="items-counter-badge" id="counterBadge">
            Menampilkan <?= count($items) ?> barang
          </span>
        </div>
      </div>

      <div class="table-responsive">
        <table id="inventoryTable">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Nama Barang</th>
              <th>Kategori</th>
              <th>Kuantitas</th>
              <th>Satuan</th>
              <th>Harga Satuan</th>
              <th>Total Nilai</th>
              <th>Status</th>
              <th class="col-aksi">Aksi</th>
            </tr>
          </thead>
          <tbody id="tableBody">
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $row): 
              $stokVal = (int)$row["stok"];
              $hargaVal = (float)$row["harga"];
              $subtotalVal = $stokVal * $hargaVal;

              if ($stokVal === 0) {
                $statusClass = "out-stock";
                $statusText = "Habis";
              } elseif ($stokVal <= 5) {
                $statusClass = "low-stock";
                $statusText = "Menipis";
              } else {
                $statusClass = "in-stock";
                $statusText = "Tersedia";
              }
            ?>
            <tr class="table-row-item">
              <td>
                <span class="code-pill"><?= e($row["kode_barang"]) ?></span>
              </td>
              <td>
                <span class="item-name-cell"><?= e($row["nama_barang"]) ?></span>
              </td>
              <td>
                <span class="category-badge">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:3px; vertical-align:-1px;">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                  </svg>
                  <?= e($row["kategori"]) ?>
                </span>
              </td>
              <td>
                <strong style="font-size: 1rem; color: var(--slate-900);"><?= number_format($stokVal, 0, ",", ".") ?></strong>
              </td>
              <td>
                <span style="color: var(--slate-500); font-size: 0.85rem;"><?= e($row["satuan"]) ?></span>
              </td>
              <td>
                <span class="price-tag">Rp <?= number_format($hargaVal, 2, ",", ".") ?></span>
              </td>
              <td>
                <span class="price-tag" style="color: var(--primary-700);">Rp <?= number_format($subtotalVal, 2, ",", ".") ?></span>
              </td>
              <td>
                <span class="stock-pill <?= $statusClass ?>">
                  <span class="dot"></span>
                  <?= $statusText ?>
                </span>
              </td>
              <td class="col-aksi">
                <div class="table-actions">
                  <a href="edit.php?id=<?= (int)$row["id"] ?>" class="btn-edit" title="Edit Barang">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    Edit
                  </a>
                  <form action="hapus.php" method="post" style="display:inline; margin:0;"
                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang \'<?= e($row['nama_barang']) ?>\' (<?= e($row['kode_barang']) ?>)?')">
                    <input type="hidden" name="id" value="<?= (int)$row["id"] ?>">
                    <button type="submit" class="btn-danger" title="Hapus Barang">
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
              <td colspan="9" class="empty-state">
                <div class="empty-state-icon">
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                  </svg>
                </div>
                <h3>Inventaris Masih Kosong</h3>
                <p>Belum ada data stok barang yang terdaftar dalam sistem.</p>
                <a href="tambah.php" class="btn btn-primary">+ Tambah Barang Sekarang</a>
              </td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Lightweight Interactive Script for Search and Auto-dismiss -->
  <script>
    // Live Search Filter
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const counterBadge = document.getElementById('counterBadge');
    
    if (searchInput && tableBody) {
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
          counterBadge.textContent = 'Menampilkan ' + visibleCount + ' dari ' + rows.length + ' barang';
        }
      });
    }

    // Auto dismiss toast alert after 4 seconds
    const toast = document.getElementById('toastAlert');
    if (toast) {
      setTimeout(() => {
        toast.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 500);
      }, 4000);
    }
  </script>
</body>
</html>
