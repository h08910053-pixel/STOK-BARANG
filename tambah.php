<?php
/**
 * Halaman Tambah Data Barang Baru - StokBarang Pro
 * Desain Enterprise & Profesional
 */
require __DIR__ . "/config/koneksi.php";
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Barang Baru - Sistem Manajemen Inventaris</title>
  <link rel="stylesheet" href="assets/style.css?v=2.1">
  <style>
    .form-page-container {
      max-width: 860px;
      margin: 0 auto 60px auto;
      padding: 0 20px;
    }
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
      gap: 14px;
      margin-bottom: 24px;
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
    }
    .page-subheading {
      font-size: 0.875rem;
      color: var(--slate-500);
      margin: 4px 0 0 0;
    }
    .pro-card {
      background: #ffffff;
      border: 1px solid var(--slate-200);
      border-radius: 16px;
      box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
      padding: 32px;
    }
    .section-legend {
      font-size: 0.825rem;
      font-weight: 700;
      color: var(--slate-500);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding-bottom: 8px;
      border-bottom: 1px solid var(--slate-200);
      margin-bottom: 20px;
    }
    .form-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 24px;
    }
    .form-grid-2 .full-col {
      grid-column: span 2;
    }
    @media (max-width: 700px) {
      .form-grid-2 {
        grid-template-columns: 1fr;
      }
      .form-grid-2 .full-col {
        grid-column: span 1;
      }
      .pro-card {
        padding: 22px 18px;
      }
    }
    .pro-label {
      display: block;
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--slate-800);
      margin-bottom: 7px;
    }
    .pro-label .required {
      color: #e11d48;
      margin-left: 2px;
    }
    .pro-input-box {
      position: relative;
      display: flex;
      align-items: center;
    }
    .pro-input-prefix {
      position: absolute;
      left: 14px;
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--slate-400);
      pointer-events: none;
      display: flex;
      align-items: center;
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
    .pro-input.no-prefix {
      padding-left: 14px;
    }
    .pro-input:focus {
      border-color: var(--primary-600);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .pro-help-text {
      font-size: 0.775rem;
      color: var(--slate-500);
      margin-top: 5px;
    }
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
      cursor: pointer;
    }
    .btn-pro-secondary:hover {
      background: var(--slate-50);
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
      cursor: pointer;
    }
    .btn-pro-primary:hover {
      background: var(--primary-700);
    }
  </style>
</head>
<body>

  <!-- Top Sticky Navigation Bar -->
  <?php
  $active_page = 'barang';
  $navbar_action_button = '
    <a href="index.php" class="btn btn-secondary">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Tabel Inventaris
    </a>
  ';
  require __DIR__ . "/navbar.php";
  ?>

  <div class="form-page-container">

    <a href="index.php" class="header-breadcrumb">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      Kembali ke Daftar Inventaris
    </a>

    <div class="page-title-panel">
      <div class="page-icon-badge">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
      </div>
      <div>
        <h1 class="page-heading">Tambah Produk Baru</h1>
        <p class="page-subheading">Daftarkan item barang baru ke dalam basis data inventaris persediaan</p>
      </div>
    </div>

    <div class="pro-card">
      <form action="simpan.php" method="post" id="tambahForm">

        <!-- 1. Identitas Produk -->
        <div class="section-legend">1. Identitas & Klasifikasi Produk</div>
        <div class="form-grid-2">
          
          <div>
            <label for="kode_barang" class="pro-label">Kode Barang <span class="required">*</span></label>
            <div class="pro-input-box">
              <span class="pro-input-prefix">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="9" x2="20" y2="9"></line><line x1="4" y1="15" x2="20" y2="15"></line><line x1="10" y1="3" x2="8" y2="21"></line><line x1="16" y1="3" x2="14" y2="21"></line></svg>
              </span>
              <input type="text" id="kode_barang" name="kode_barang" class="pro-input" maxlength="30" placeholder="Contoh: BRG-001" required autofocus autocomplete="off">
            </div>
            <div class="pro-help-text">Kode unik pengenal produk (SKU)</div>
          </div>

          <div>
            <label for="kategori" class="pro-label">Kategori <span class="required">*</span></label>
            <div class="pro-input-box">
              <span class="pro-input-prefix">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
              </span>
              <input type="text" id="kategori" name="kategori" class="pro-input" maxlength="60" placeholder="Elektronik, Alat Tulis Kantor, dsb." required>
            </div>
            <div class="pro-help-text">Kelompok atau divisi barang</div>
          </div>

          <div class="full-col">
            <label for="nama_barang" class="pro-label">Nama Barang <span class="required">*</span></label>
            <div class="pro-input-box">
              <span class="pro-input-prefix">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
              </span>
              <input type="text" id="nama_barang" name="nama_barang" class="pro-input" maxlength="100" placeholder="Nama lengkap produk barang..." required>
            </div>
          </div>

        </div>

        <!-- 2. Stok & Finansial -->
        <div class="section-legend" style="margin-top: 10px;">2. Kuantitas Stok & Harga Satuan</div>
        <div class="form-grid-2">

          <div>
            <label for="stok" class="pro-label">Kuantitas Stok Awal <span class="required">*</span></label>
            <input type="number" id="stok" name="stok" class="pro-input no-prefix" min="0" step="1" placeholder="0" required>
            <div class="pro-help-text">Jumlah unit fisik awal di gudang</div>
          </div>

          <div>
            <label for="satuan" class="pro-label">Satuan Unit <span class="required">*</span></label>
            <input type="text" id="satuan" name="satuan" class="pro-input no-prefix" maxlength="20" placeholder="Pcs, Rim, Unit, Botol, Box" required>
            <div class="pro-help-text">Satuan penghitungan inventaris</div>
          </div>

          <div class="full-col">
            <label for="harga" class="pro-label">Harga Satuan (IDR) <span class="required">*</span></label>
            <div class="pro-input-box">
              <span class="pro-input-prefix" style="color:var(--slate-600); font-weight:700;">Rp</span>
              <input type="number" id="harga" name="harga" class="pro-input" min="0" step="0.01" placeholder="0.00" required>
            </div>
            <div class="pro-help-text" id="pricePreview">Masukkan nominal harga satuan produk</div>
          </div>

        </div>

        <!-- Footer Actions -->
        <div class="pro-form-footer">
          <a href="index.php" class="btn-pro-secondary">Batal</a>
          <button type="submit" class="btn-pro-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
              <polyline points="17 21 17 13 7 13 7 21"></polyline>
              <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Simpan Produk
          </button>
        </div>

      </form>
    </div>

  </div>

  <script>
    const hargaInput = document.getElementById('harga');
    const pricePreview = document.getElementById('pricePreview');
    if (hargaInput && pricePreview) {
      hargaInput.addEventListener('input', function() {
        const val = parseFloat(this.value);
        if (!isNaN(val) && val >= 0) {
          pricePreview.innerHTML = 'Estimasi Nominal: <strong style="color:var(--primary-600);">' + 
            new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(val) + 
            '</strong>';
        } else {
          pricePreview.textContent = 'Masukkan nominal harga satuan produk';
        }
      });
    }
  </script>
</body>
</html>
