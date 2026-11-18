<?php
/**
 * Komponen Header & Navigasi Utama - StokBarang Pro
 * Sistem Informasi Transaksi Stok Barang
 */

// Deteksi halaman aktif jika belum didefinisikan secara manual
if (!isset($active_page)) {
  $currentPage = basename($_SERVER['PHP_SELF'] ?? '');
  if (in_array($currentPage, ['index.php', 'tambah.php', 'edit.php'])) {
    $active_page = 'barang';
  } elseif (in_array($currentPage, ['transaksi_tambah.php'])) {
    $active_page = 'transaksi_tambah';
  } elseif (in_array($currentPage, ['transaksi_riwayat.php'])) {
    $active_page = 'transaksi_riwayat';
  } else {
    $active_page = '';
  }
}
?>
<!-- Top Sticky Navigation Bar -->
<nav class="navbar">
  <div class="navbar-container">
    <!-- Brand Logo Kotak Oranye-Biru -->
    <a href="index.php" class="navbar-brand" title="Ke Beranda StokBarang Pro">
      <div class="navbar-logo-icon" aria-hidden="true">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M21 7.5L12 2.5L3 7.5L12 12.5L21 7.5Z" fill="#ffedd5" stroke="#ffffff" stroke-width="1.8" stroke-linejoin="round"/>
          <path d="M3 7.5V16.5L12 21.5V12.5L3 7.5Z" fill="#ffffff" fill-opacity="0.9" stroke="#ffffff" stroke-width="1.8" stroke-linejoin="round"/>
          <path d="M12 12.5V21.5L21 16.5V7.5L12 12.5Z" fill="#ffffff" fill-opacity="0.65" stroke="#ffffff" stroke-width="1.8" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="navbar-title">
        StokBarang Pro
        <span class="tagline">Sistem Manajemen Inventaris</span>
      </div>
    </a>

    <!-- Menu Navigasi Horizontal -->
    <div class="navbar-nav" role="navigation" aria-label="Menu Utama">
      <a href="index.php" class="nav-item <?= ($active_page === 'barang') ? 'active' : '' ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
          <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
          <line x1="12" y1="22.08" x2="12" y2="12"></line>
        </svg>
        <span>Data Barang</span>
      </a>

      <a href="transaksi_tambah.php" class="nav-item <?= ($active_page === 'transaksi_tambah') ? 'active' : '' ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>
        <span>Input Transaksi</span>
      </a>

      <a href="transaksi_riwayat.php" class="nav-item <?= ($active_page === 'transaksi_riwayat') ? 'active' : '' ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
        <span>Riwayat Transaksi</span>
      </a>
    </div>

    <!-- Sisi Kanan: Status Sistem & Tombol Aksi -->
    <div class="navbar-meta">
      <div class="live-badge" title="Koneksi database dan sistem inventaris beroperasi normal">
        <span class="live-indicator-dot"></span>
        <span>Sistem Aktif</span>
      </div>

      <?php if (isset($navbar_action_button)): ?>
        <?= $navbar_action_button ?>
      <?php else: ?>
        <a href="transaksi_tambah.php" class="btn btn-primary" title="Catat Mutasi Stok Baru">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Catat Transaksi
        </a>
      <?php endif; ?>
    </div>
  </div>
</nav>
