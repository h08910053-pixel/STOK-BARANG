<?php
/**
 * Halaman Catat Transaksi Mutasi Stok - StokBarang Pro
 * Desain Enterprise & Profesional (Inbound / Outbound Inventory Management)
 */
require __DIR__ . "/config/koneksi.php";

function e($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Ambil daftar barang untuk dropdown
$query_barang = "SELECT id, kode_barang, nama_barang, kategori, stok, satuan, harga FROM barang ORDER BY nama_barang ASC";
$result_barang = $conn->query($query_barang);

$barang_list = [];
if ($result_barang) {
  while ($b = $result_barang->fetch_assoc()) {
    $barang_list[] = $b;
  }
}

// Parameter pre-select dari query string
$selected_id = filter_input(INPUT_GET, 'id_barang', FILTER_VALIDATE_INT) ?: 0;
$selected_jenis = filter_input(INPUT_GET, 'jenis', FILTER_DEFAULT) ?: 'Masuk';
if (!in_array($selected_jenis, ['Masuk', 'Keluar'])) {
  $selected_jenis = 'Masuk';
}

$error_msg = $_GET['error'] ?? '';
$error_messages = [
  'stok_kurang'   => 'Kuantitas transaksi keluar melebihi stok yang tersedia di gudang!',
  'input_invalid' => 'Data formulir tidak lengkap atau format tidak valid.',
  'simpan_gagal'  => 'Gagal memproses transaksi akibat gangguan pada server.'
];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catat Mutasi Stok - Sistem Manajemen Inventaris</title>
  <link rel="stylesheet" href="assets/style.css?v=2.1">
  <style>
    /* ==========================================================================
       ENTERPRISE FORM STYLES: SCOPED ENHANCEMENT
       ========================================================================== */
    :root {
      --slate-50: #f8fafc;
      --slate-100: #f1f5f9;
      --slate-200: #e2e8f0;
      --slate-300: #cbd5e1;
      --slate-400: #94a3b8;
      --slate-500: #64748b;
      --slate-600: #475569;
      --slate-700: #334155;
      --slate-800: #1e293b;
      --slate-900: #0f172a;
      --primary-600: #2563eb;
      --primary-700: #1d4ed8;
      --primary-50: #eff6ff;
      --emerald-600: #059669;
      --emerald-700: #047857;
      --emerald-50: #ecfdf5;
      --rose-600: #e11d48;
      --rose-700: #be123c;
      --rose-50: #fff1f2;
      --amber-600: #d97706;
      --amber-50: #fffbeb;
    }

    body {
      background-color: #f8fafc;
      color: var(--slate-700);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    .form-page-container {
      max-width: 960px;
      margin: 0 auto 60px auto;
      padding: 0 20px;
    }

    /* Page Navigation & Title Header */
    .header-breadcrumb {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 0.85rem;
      font-weight: 500;
      color: var(--slate-500);
      margin-bottom: 16px;
      text-decoration: none;
      transition: color 0.15s ease;
    }
    .header-breadcrumb:hover {
      color: var(--primary-600);
    }

    .page-title-panel {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 24px;
    }

    .page-title-left {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .page-icon-badge {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      background: #eff6ff;
      border: 1px solid #dbeafe;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary-600);
    }

    .page-heading {
      font-size: 1.4rem;
      font-weight: 700;
      color: var(--slate-900);
      letter-spacing: -0.02em;
      margin: 0;
      line-height: 1.25;
    }

    .page-subheading {
      font-size: 0.875rem;
      color: var(--slate-500);
      margin: 4px 0 0 0;
    }

    /* Form Container Card */
    .pro-card {
      background: #ffffff;
      border: 1px solid var(--slate-200);
      border-radius: 16px;
      box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.03);
      padding: 32px;
    }

    /* Segmented Selector for Transaction Type (Masuk / Keluar) */
    .type-selector-label {
      font-size: 0.825rem;
      font-weight: 600;
      color: var(--slate-700);
      text-transform: uppercase;
      letter-spacing: 0.04em;
      margin-bottom: 10px;
      display: block;
    }

    .type-selector-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-bottom: 28px;
    }

    .type-card {
      position: relative;
      border: 2px solid var(--slate-200);
      border-radius: 12px;
      padding: 16px 18px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 14px;
      background: #ffffff;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      user-select: none;
    }

    .type-card input[type="radio"] {
      position: absolute;
      opacity: 0;
      pointer-events: none;
    }

    .type-card:hover {
      border-color: var(--slate-300);
      background: var(--slate-50);
    }

    .type-card-icon {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      background: var(--slate-100);
      color: var(--slate-600);
      transition: all 0.2s ease;
    }

    .type-card-text {
      flex: 1;
    }

    .type-card-title {
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--slate-800);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .type-card-desc {
      font-size: 0.775rem;
      color: var(--slate-500);
      margin-top: 3px;
    }

    .type-card-badge {
      font-size: 0.725rem;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 9999px;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }

    /* Active State: Masuk (Inbound / Emerald) */
    .type-card.active-masuk {
      border-color: var(--emerald-600);
      background: var(--emerald-50);
      box-shadow: 0 0 0 1px var(--emerald-600), 0 4px 12px rgba(5, 150, 105, 0.1);
    }
    .type-card.active-masuk .type-card-icon {
      background: #ffffff;
      color: var(--emerald-600);
      box-shadow: 0 2px 6px rgba(5, 150, 105, 0.15);
    }
    .type-card.active-masuk .type-card-title {
      color: var(--emerald-700);
    }
    .type-card.active-masuk .type-card-badge {
      background: #a7f3d0;
      color: #065f46;
    }

    /* Active State: Keluar (Outbound / Rose) */
    .type-card.active-keluar {
      border-color: var(--rose-600);
      background: var(--rose-50);
      box-shadow: 0 0 0 1px var(--rose-600), 0 4px 12px rgba(225, 29, 72, 0.1);
    }
    .type-card.active-keluar .type-card-icon {
      background: #ffffff;
      color: var(--rose-600);
      box-shadow: 0 2px 6px rgba(225, 29, 72, 0.15);
    }
    .type-card.active-keluar .type-card-title {
      color: var(--rose-700);
    }
    .type-card.active-keluar .type-card-badge {
      background: #fecdd3;
      color: #9f1239;
    }

    /* Form Layout 2 Columns */
    .form-layout-2col {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 28px;
    }

    @media (max-width: 820px) {
      .type-selector-grid {
        grid-template-columns: 1fr;
      }
      .form-layout-2col {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      .pro-card {
        padding: 22px 18px;
      }
    }

    /* Field Group Styling */
    .pro-form-group {
      margin-bottom: 20px;
    }

    .pro-label {
      display: block;
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--slate-800);
      margin-bottom: 7px;
    }

    .pro-label .required {
      color: var(--rose-600);
      margin-left: 2px;
    }

    .pro-input-box {
      position: relative;
      display: flex;
      align-items: center;
    }

    .pro-input-icon {
      position: absolute;
      left: 14px;
      color: var(--slate-400);
      pointer-events: none;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .pro-input {
      width: 100%;
      height: 44px;
      padding: 0 14px 0 42px;
      font-size: 0.925rem;
      font-family: inherit;
      color: var(--slate-900);
      background: #ffffff;
      border: 1px solid var(--slate-300);
      border-radius: 9px;
      transition: all 0.18s ease;
      outline: none;
      box-sizing: border-box;
    }

    .pro-input:focus {
      border-color: var(--primary-600);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .pro-select {
      appearance: none;
      -webkit-appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 14px center;
      padding-right: 40px;
      cursor: pointer;
    }

    .pro-textarea {
      width: 100%;
      min-height: 96px;
      padding: 12px 14px;
      font-size: 0.925rem;
      font-family: inherit;
      color: var(--slate-900);
      background: #ffffff;
      border: 1px solid var(--slate-300);
      border-radius: 9px;
      transition: all 0.18s ease;
      outline: none;
      box-sizing: border-box;
      resize: vertical;
      line-height: 1.5;
    }

    .pro-textarea:focus {
      border-color: var(--primary-600);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .pro-help-text {
      font-size: 0.775rem;
      color: var(--slate-500);
      margin-top: 5px;
    }

    /* Selected Item Summary Callout */
    .item-info-card {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 14px 16px;
      margin-top: 10px;
      display: none;
    }

    .item-info-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 6px;
    }
    .item-info-row:last-child {
      margin-bottom: 0;
      padding-top: 6px;
      border-top: 1px dashed var(--slate-200);
    }

    .item-info-label {
      font-size: 0.8rem;
      color: var(--slate-500);
    }

    .item-info-val {
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--slate-800);
    }

    /* Stock Flow Calculator Box */
    .stock-calc-panel {
      background: #f8fafc;
      border: 1px solid var(--slate-200);
      border-radius: 12px;
      padding: 18px 20px;
      margin-top: 8px;
    }

    .stock-calc-header {
      font-size: 0.775rem;
      font-weight: 700;
      color: var(--slate-500);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .calc-step-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 0.875rem;
      color: var(--slate-600);
      padding: 6px 0;
    }

    .calc-step-row.total-row {
      border-top: 1.5px solid var(--slate-200);
      margin-top: 8px;
      padding-top: 10px;
      font-weight: 700;
      color: var(--slate-900);
      font-size: 0.95rem;
    }

    .stock-val-pill {
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 6px;
      font-size: 0.875rem;
    }
    .pill-neutral {
      background: #e2e8f0;
      color: var(--slate-800);
    }
    .pill-green {
      background: #dcfce7;
      color: #15803d;
    }
    .pill-red {
      background: #fee2e2;
      color: #b91c1c;
    }
    .pill-blue {
      background: #dbeafe;
      color: #1e40af;
    }

    .stock-warning-banner {
      display: none;
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 8px;
      padding: 10px 14px;
      color: var(--rose-700);
      font-size: 0.825rem;
      font-weight: 600;
      margin-top: 10px;
      align-items: center;
      gap: 8px;
    }

    /* Actions Footer */
    .pro-form-footer {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 12px;
      margin-top: 28px;
      padding-top: 24px;
      border-top: 1px solid var(--slate-200);
    }

    .btn-pro-secondary {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--slate-700);
      background: #ffffff;
      border: 1px solid var(--slate-300);
      border-radius: 9px;
      text-decoration: none;
      transition: all 0.15s ease;
      cursor: pointer;
    }
    .btn-pro-secondary:hover {
      background: var(--slate-50);
      border-color: var(--slate-400);
    }

    .btn-pro-primary {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 24px;
      font-size: 0.875rem;
      font-weight: 600;
      color: #ffffff;
      background: var(--primary-600);
      border: 1px solid var(--primary-600);
      border-radius: 9px;
      text-decoration: none;
      transition: all 0.15s ease;
      cursor: pointer;
      box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .btn-pro-primary:hover {
      background: var(--primary-700);
      border-color: var(--primary-700);
    }
  </style>
</head>
<body>

  <!-- Top Sticky Navigation Bar -->
  <?php
  $active_page = 'transaksi_tambah';
  $navbar_action_button = '
    <a href="transaksi_riwayat.php" class="btn btn-secondary">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <polyline points="12 6 12 12 16 14"></polyline>
      </svg>
      Riwayat Transaksi
    </a>
  ';
  require __DIR__ . "/navbar.php";
  ?>

  <div class="form-page-container">

    <!-- Header & Breadcrumbs -->
    <a href="transaksi_riwayat.php" class="header-breadcrumb">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Kembali ke Riwayat Mutasi
    </a>

    <div class="page-title-panel">
      <div class="page-title-left">
        <div class="page-icon-badge">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="17 1 21 5 17 9"></polyline>
            <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
            <polyline points="7 23 3 19 7 15"></polyline>
            <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
          </svg>
        </div>
        <div>
          <h1 class="page-heading">Catat Transaksi Mutasi Stok</h1>
          <p class="page-subheading">Kelola pergerakan inventaris barang masuk (Inbound) dan barang keluar (Outbound)</p>
        </div>
      </div>
    </div>

    <!-- Alert Notifikasi Gagal dari Server jika ada -->
    <?php if (isset($error_messages[$error_msg])): ?>
      <div class="alert" style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; margin-bottom: 20px; border-radius:10px; padding:14px 18px; display:flex; align-items:center; justify-content:space-between;" role="alert">
        <div style="display:flex; align-items:center; gap:10px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
          <span style="font-size:0.9rem; font-weight:600;"><?= htmlspecialchars($error_messages[$error_msg], ENT_QUOTES, "UTF-8") ?></span>
        </div>
      </div>
    <?php endif; ?>

    <!-- Card Form Utama -->
    <div class="pro-card">

      <?php if (empty($barang_list)): ?>
        <div class="empty-state" style="text-align:center; padding:40px 10px;">
          <div style="font-size:2.5rem; margin-bottom:12px; color:var(--slate-400);">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
          </div>
          <h3 style="font-size:1.15rem; color:var(--slate-900); margin-bottom:8px;">Data Barang Masih Kosong</h3>
          <p style="color:var(--slate-500); font-size:0.9rem; margin-bottom:20px;">Silakan daftarkan barang terlebih dahulu sebelum mencatat transaksi mutasi stok.</p>
          <a href="tambah.php" class="btn-pro-primary">+ Tambah Barang Baru</a>
        </div>
      <?php else: ?>

        <form action="transaksi_proses.php" method="post" id="proTransaksiForm" novalidate>

          <!-- 1. Segmented Control: Jenis Transaksi -->
          <span class="type-selector-label">Jenis Transaksi Stok <span class="required" style="color:var(--rose-600);">*</span></span>
          <div class="type-selector-grid">

            <!-- Opsi Barang Masuk -->
            <label class="type-card <?= ($selected_jenis === 'Masuk') ? 'active-masuk' : '' ?>" id="cardMasuk">
              <input type="radio" name="jenis_transaksi" value="Masuk" <?= ($selected_jenis === 'Masuk') ? 'checked' : '' ?> required>
              <div class="type-card-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 5v14M19 12l-7 7-7-7"/>
                </svg>
              </div>
              <div class="type-card-text">
                <div class="type-card-title">
                  Barang Masuk
                  <span class="type-card-badge">+ Penambahan</span>
                </div>
                <div class="type-card-desc">Menambah stok persediaan di gudang penyimpanan</div>
              </div>
            </label>

            <!-- Opsi Barang Keluar -->
            <label class="type-card <?= ($selected_jenis === 'Keluar') ? 'active-keluar' : '' ?>" id="cardKeluar">
              <input type="radio" name="jenis_transaksi" value="Keluar" <?= ($selected_jenis === 'Keluar') ? 'checked' : '' ?> required>
              <div class="type-card-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 19V5M5 12l7-7 7 7"/>
                </svg>
              </div>
              <div class="type-card-text">
                <div class="type-card-title">
                  Barang Keluar
                  <span class="type-card-badge">- Pengurangan</span>
                </div>
                <div class="type-card-desc">Mengurangi stok persediaan untuk kebutuhan operasional/penjualan</div>
              </div>
            </label>

          </div>

          <!-- 2. Two-Column Layout Form -->
          <div class="form-layout-2col">

            <!-- Kolom Kiri: Tanggal & Pemilihan Barang -->
            <div class="form-col-left">

              <!-- Tanggal Transaksi -->
              <div class="pro-form-group">
                <label for="tanggal" class="pro-label">Tanggal Transaksi <span class="required">*</span></label>
                <div class="pro-input-box">
                  <span class="pro-input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                      <line x1="16" y1="2" x2="16" y2="6"></line>
                      <line x1="8" y1="2" x2="8" y2="6"></line>
                      <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                  </span>
                  <input type="date" id="tanggal" name="tanggal" class="pro-input" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="pro-help-text">Tanggal efektif terjadinya pergerakan fisik barang</div>
              </div>

              <!-- Pilih Barang -->
              <div class="pro-form-group">
                <label for="id_barang" class="pro-label">Pilih Produk Inventaris <span class="required">*</span></label>
                <div class="pro-input-box">
                  <span class="pro-input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                  </span>
                  <select id="id_barang" name="id_barang" class="pro-input pro-select" required>
                    <option value="" data-stok="0" data-satuan="" data-kategori="" data-kode="">-- Pilih Barang yang Akan Dimutasi --</option>
                    <?php foreach ($barang_list as $b): 
                      $isSelected = ($b['id'] == $selected_id) ? 'selected' : '';
                    ?>
                      <option value="<?= (int)$b['id'] ?>" 
                              data-stok="<?= (int)$b['stok'] ?>" 
                              data-satuan="<?= e($b['satuan']) ?>"
                              data-kode="<?= e($b['kode_barang']) ?>"
                              data-nama="<?= e($b['nama_barang']) ?>"
                              data-kategori="<?= e($b['kategori']) ?>"
                              <?= $isSelected ?>>
                        <?= e($b['kode_barang']) ?> - <?= e($b['nama_barang']) ?> (Stok: <?= number_format($b['stok'], 0, ',', '.') ?> <?= e($b['satuan']) ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="pro-help-text">Pilih barang berdasarkan kode atau nama barang</div>
              </div>

              <!-- Callout Info Barang Terpilih -->
              <div id="itemInfoCard" class="item-info-card">
                <div class="item-info-row">
                  <span class="item-info-label">Kode & Kategori</span>
                  <span class="item-info-val"><span id="infoKode" style="font-family:monospace; color:var(--primary-600);">-</span> &bull; <span id="infoKategori">-</span></span>
                </div>
                <div class="item-info-row">
                  <span class="item-info-label">Stok Fisik Tersedia</span>
                  <span class="item-info-val" id="infoStokTerkini" style="font-size:0.95rem; color:var(--slate-900); font-weight:700;">-</span>
                </div>
              </div>

            </div>

            <!-- Kolom Kanan: Kuantitas, Kalkulator Mutasi, & Catatan -->
            <div class="form-col-right">

              <!-- Jumlah Kuantitas Mutasi -->
              <div class="pro-form-group">
                <label for="jumlah" class="pro-label">Jumlah Kuantitas Mutasi <span class="required">*</span></label>
                <div class="pro-input-box">
                  <span class="pro-input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="4" y="4" width="16" height="16" rx="2"></rect>
                      <line x1="8" y1="12" x2="16" y2="12"></line>
                      <line x1="12" y1="8" x2="12" y2="16"></line>
                    </svg>
                  </span>
                  <input type="number" id="jumlah" name="jumlah" class="pro-input" min="1" step="1" placeholder="Masukkan jumlah unit (min. 1)" required autocomplete="off">
                </div>
                <div class="pro-help-text">Jumlah unit fisik yang akan dipindahkan</div>

                <!-- Stock Calculation Preview Panel -->
                <div class="stock-calc-panel" id="stockCalcPanel">
                  <div class="stock-calc-header">
                    <span>Proyeksi Perubahan Stok</span>
                    <span id="badgeCalcMode" style="font-weight:700; color:var(--emerald-600);">Inbound (+)</span>
                  </div>

                  <div class="calc-step-row">
                    <span>Stok Saat Ini di Gudang</span>
                    <span class="stock-val-pill pill-neutral" id="calcStokAwal">0 Unit</span>
                  </div>

                  <div class="calc-step-row">
                    <span id="lblStepMutasi">Kuantitas Ditambahkan</span>
                    <span class="stock-val-pill pill-green" id="calcMutasiNilai">+0 Unit</span>
                  </div>

                  <div class="calc-step-row total-row">
                    <span>Estimasi Stok Akhir</span>
                    <span class="stock-val-pill pill-blue" id="calcStokAkhir">0 Unit</span>
                  </div>

                  <!-- Banner Peringatan Stok Kurang -->
                  <div class="stock-warning-banner" id="stockWarningBanner">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="10"></circle>
                      <line x1="12" y1="8" x2="12" y2="12"></line>
                      <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span id="stockWarningText">Kuantitas keluar melebihi stok yang tersedia di gudang!</span>
                  </div>
                </div>
              </div>

              <!-- Catatan / Keterangan -->
              <div class="pro-form-group">
                <label for="keterangan" class="pro-label">Keterangan / Dokumen Referensi</label>
                <textarea id="keterangan" name="keterangan" class="pro-textarea" placeholder="Contoh: Penerimaan PO #PO-2026-001 dari Supplier, Pengeluaran inventaris divisi Marketing, Surat Jalan #SJ-889, dll."></textarea>
                <div class="pro-help-text">Catat nama supplier, nomor faktur, atau divisi pemohon</div>
              </div>

            </div>

          </div>

          <!-- Footer Actions -->
          <div class="pro-form-footer">
            <a href="transaksi_riwayat.php" class="btn-pro-secondary">Batal</a>
            <button type="submit" id="btnSubmitForm" class="btn-pro-primary">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
              </svg>
              Simpan Transaksi
            </button>
          </div>

        </form>

      <?php endif; ?>

    </div>
  </div>

  <!-- Interactive JavaScript: Segmented Selector, Realtime Calculator & Validation -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('proTransaksiForm');
      if (!form) return;

      const selectBarang = document.getElementById('id_barang');
      const inputJumlah = document.getElementById('jumlah');
      const cardMasuk = document.getElementById('cardMasuk');
      const cardKeluar = document.getElementById('cardKeluar');
      const radioMasuk = cardMasuk ? cardMasuk.querySelector('input[type="radio"]') : null;
      const radioKeluar = cardKeluar ? cardKeluar.querySelector('input[type="radio"]') : null;

      // Item info elements
      const itemInfoCard = document.getElementById('itemInfoCard');
      const infoKode = document.getElementById('infoKode');
      const infoKategori = document.getElementById('infoKategori');
      const infoStokTerkini = document.getElementById('infoStokTerkini');

      // Calculator elements
      const badgeCalcMode = document.getElementById('badgeCalcMode');
      const calcStokAwal = document.getElementById('calcStokAwal');
      const lblStepMutasi = document.getElementById('lblStepMutasi');
      const calcMutasiNilai = document.getElementById('calcMutasiNilai');
      const calcStokAkhir = document.getElementById('calcStokAkhir');
      const stockWarningBanner = document.getElementById('stockWarningBanner');
      const stockWarningText = document.getElementById('stockWarningText');

      function getJenisTransaksi() {
        if (radioKeluar && radioKeluar.checked) return 'Keluar';
        return 'Masuk';
      }

      function updateCardStyles() {
        const jenis = getJenisTransaksi();
        if (jenis === 'Masuk') {
          cardMasuk.classList.add('active-masuk');
          cardKeluar.classList.remove('active-keluar');
          badgeCalcMode.textContent = 'Inbound (+)';
          badgeCalcMode.style.color = 'var(--emerald-600)';
          lblStepMutasi.textContent = 'Kuantitas Ditambahkan';
        } else {
          cardKeluar.classList.add('active-keluar');
          cardMasuk.classList.remove('active-masuk');
          badgeCalcMode.textContent = 'Outbound (-)';
          badgeCalcMode.style.color = 'var(--rose-600)';
          lblStepMutasi.textContent = 'Kuantitas Dikeluarkan';
        }
        recalculateStock();
      }

      // Klik card memicu pilihan radio
      if (cardMasuk && cardKeluar) {
        cardMasuk.addEventListener('click', function() {
          radioMasuk.checked = true;
          updateCardStyles();
        });
        cardKeluar.addEventListener('click', function() {
          radioKeluar.checked = true;
          updateCardStyles();
        });
      }

      function recalculateStock() {
        const selectedOption = selectBarang.options[selectBarang.selectedIndex];
        const jenis = getJenisTransaksi();

        if (!selectedOption || !selectedOption.value) {
          itemInfoCard.style.display = 'none';
          calcStokAwal.textContent = '0 Unit';
          calcMutasiNilai.textContent = (jenis === 'Masuk' ? '+' : '-') + '0 Unit';
          calcStokAkhir.textContent = '0 Unit';
          calcStokAkhir.className = 'stock-val-pill pill-neutral';
          stockWarningBanner.style.display = 'none';
          inputJumlah.style.borderColor = '';
          return;
        }

        const currentStock = parseInt(selectedOption.getAttribute('data-stok'), 10) || 0;
        const satuan = selectedOption.getAttribute('data-satuan') || 'Unit';
        const kode = selectedOption.getAttribute('data-kode') || '-';
        const kategori = selectedOption.getAttribute('data-kategori') || '-';

        // Tampilkan info card
        itemInfoCard.style.display = 'block';
        infoKode.textContent = kode;
        infoKategori.textContent = kategori;
        infoStokTerkini.textContent = currentStock.toLocaleString('id-ID') + ' ' + satuan;

        calcStokAwal.textContent = currentStock.toLocaleString('id-ID') + ' ' + satuan;

        const jumlahVal = parseInt(inputJumlah.value, 10);
        if (isNaN(jumlahVal) || jumlahVal <= 0) {
          calcMutasiNilai.textContent = '-';
          calcMutasiNilai.className = 'stock-val-pill pill-neutral';
          calcStokAkhir.textContent = currentStock.toLocaleString('id-ID') + ' ' + satuan;
          calcStokAkhir.className = 'stock-val-pill pill-blue';
          stockWarningBanner.style.display = 'none';
          inputJumlah.style.borderColor = '';
          return;
        }

        if (jenis === 'Masuk') {
          const newStock = currentStock + jumlahVal;
          calcMutasiNilai.textContent = '+' + jumlahVal.toLocaleString('id-ID') + ' ' + satuan;
          calcMutasiNilai.className = 'stock-val-pill pill-green';
          calcStokAkhir.textContent = newStock.toLocaleString('id-ID') + ' ' + satuan;
          calcStokAkhir.className = 'stock-val-pill pill-green';
          stockWarningBanner.style.display = 'none';
          inputJumlah.style.borderColor = '';
        } else {
          // Keluar
          const newStock = currentStock - jumlahVal;
          calcMutasiNilai.textContent = '-' + jumlahVal.toLocaleString('id-ID') + ' ' + satuan;
          calcMutasiNilai.className = 'stock-val-pill pill-red';
          calcStokAkhir.textContent = newStock.toLocaleString('id-ID') + ' ' + satuan;

          if (jumlahVal > currentStock) {
            calcStokAkhir.className = 'stock-val-pill pill-red';
            stockWarningText.textContent = `Peringatan: Kuantitas keluar (${jumlahVal.toLocaleString('id-ID')} ${satuan}) melebihi stok yang tersedia (${currentStock.toLocaleString('id-ID')} ${satuan})!`;
            stockWarningBanner.style.display = 'flex';
            inputJumlah.style.borderColor = 'var(--rose-600)';
          } else {
            calcStokAkhir.className = 'stock-val-pill pill-blue';
            stockWarningBanner.style.display = 'none';
            inputJumlah.style.borderColor = '';
          }
        }
      }

      selectBarang.addEventListener('change', recalculateStock);
      inputJumlah.addEventListener('input', recalculateStock);

      // Inisialisasi awal
      updateCardStyles();

      // Validasi form submit
      form.addEventListener('submit', function(e) {
        if (!selectBarang.value) {
          e.preventDefault();
          alert('Silakan pilih produk barang terlebih dahulu!');
          selectBarang.focus();
          return false;
        }

        const jumlahVal = parseInt(inputJumlah.value, 10);
        if (isNaN(jumlahVal) || jumlahVal < 1) {
          e.preventDefault();
          alert('Jumlah kuantitas mutasi minimal harus 1 unit!');
          inputJumlah.focus();
          return false;
        }

        const selectedOption = selectBarang.options[selectBarang.selectedIndex];
        const currentStock = parseInt(selectedOption.getAttribute('data-stok'), 10) || 0;
        const satuan = selectedOption.getAttribute('data-satuan') || 'Unit';
        const nama = selectedOption.getAttribute('data-nama') || 'Barang';
        const jenis = getJenisTransaksi();

        if (jenis === 'Keluar' && jumlahVal > currentStock) {
          e.preventDefault();
          alert(`Transaksi Ditolak!\n\nStok produk "${nama}" tidak mencukupi.\nStok tersedia: ${currentStock.toLocaleString('id-ID')} ${satuan}\nJumlah keluar: ${jumlahVal.toLocaleString('id-ID')} ${satuan}`);
          inputJumlah.focus();
          return false;
        }

        return true;
      });
    });
  </script>
</body>
</html>
