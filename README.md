# Sistem Informasi Transaksi Stok Barang (StokBarang Pro)

Aplikasi web modern berbasis **HTML, CSS, JavaScript, PHP, dan MySQL** yang dirancang untuk mengelola inventaris produk dan mencatat riwayat transaksi mutasi barang masuk (*In*) serta barang keluar (*Out*) secara *real-time*.

Aplikasi ini dibuat untuk memenuhi seluruh kriteria dan petunjuk pengerjaan **Ujian Akhir Semester (UAS) Pemrograman Web**.

---

## 🚀 Fitur Utama Aplikasi

### 1. Desain Antarmuka & Navigasi (UI/UX)
- **Tampilan Modern & Responsif**: Menggunakan CSS kustom (*Modern SaaS & Dashboard Design*) dengan tipografi *Plus Jakarta Sans*, sistem warna semantik konsisten, kartu KPI, serta badge status.
- **Top Sticky Navbar**: Menu navigasi horizontal yang selalu terlihat saat di-scroll, lengkap dengan indikator status halaman aktif (*Data Barang*, *Input Transaksi*, *Riwayat Transaksi*).
- **KPI Summary Widgets**: Menampilkan ringkasan total jenis barang, total kuantitas unit, estimasi nilai total aset inventaris, serta peringatan barang dengan stok kritis (≤ 5 unit).

### 2. Manajemen Data Barang (CRUD Lengkap)
- **Tampil Data (Read)**: Menampilkan seluruh data barang dalam tabel terstruktur beserta badge status ketersediaan stok (*Tersedia*, *Menipis*, atau *Habis*).
- **Tambah Data (Create)**: Formulir tambah barang dengan validasi kode barang unik, kategori, nama, kuantitas stok awal, satuan, dan harga satuan dilengkapi *live preview* mata uang Rupiah.
- **Ubah Data (Update)**: Memperbarui informasi barang yang sudah terdaftar dengan validasi integritas data.
- **Hapus Data (Delete)**: Menghapus produk dari sistem dengan dialog konfirmasi JavaScript untuk mencegah ketidaksengajaan.
- **Live Search**: Pencarian instan sisi klien (*client-side filter*) berdasarkan kode, nama barang, atau kategori tanpa reload halaman.

### 3. Transaksi Mutasi Stok (Masuk & Keluar)
- **Formulir Transaksi Terpadu**:
  - Tanggal transaksi (otomatis terisi hari ini, dapat disesuaikan).
  - Pilihan jenis transaksi: **Barang Masuk (In)** atau **Barang Keluar (Out)** dengan *Radio Cards* interaktif.
  - Dropdown pemilihan barang yang otomatis menampilkan informasi stok terkini.
  - Input kuantitas mutasi dengan **Dynamic Stock Calculator Preview**: Menghitung secara langsung `Stok Saat Ini ➔ Mutasi ➔ Estimasi Stok Akhir`.
  - Catatan/keterangan mutasi (supplier, no. invoice, divisi pemohon, dll).
- **Validasi Kritis Ketersediaan Stok**:
  - **Sisi Klien (JavaScript)**: Mencegah penginputan barang keluar jika kuantitas melebihi stok yang ada di gudang, menampilkan kotak peringatan, dan memblokir submit form.
  - **Sisi Server (PHP & MySQL Transaction)**: Memeriksa kembali kuantitas stok di database dengan mekanisme *ACID Transaction* (`begin_transaction`, `commit`, `rollback`) agar data stok selalu konsisten.
- **Otomatisasi Mutasi Stok**:
  - Transaksi **Masuk** otomatis **menambah** nilai stok barang (`stok = stok + jumlah`).
  - Transaksi **Keluar** otomatis **mengurangi** nilai stok barang (`stok = stok - jumlah`).

### 4. Riwayat & Histori Transaksi
- Menampilkan seluruh catatan mutasi barang dalam bentuk tabel kronologis (dilengkapi jam pencatatan).
- Badge warna visual: **Hijau (+)** untuk transaksi Masuk dan **Merah (-)** untuk transaksi Keluar.
- Tab filter cepat: **Semua**, **Masuk**, dan **Keluar**.
- Pencarian data transaksi berdasarkan kata kunci keterangan, nama, atau kode barang.
- **Fitur Pembatalan/Hapus Riwayat Transaksi**: Menghapus catatan mutasi sekaligus mengembalikan nilai stok barang secara otomatis dan aman.

---

## 📂 Struktur File Proyek

```text
stok-barang/
├── assets/
│   └── style.css            # Desain CSS lengkap (design system, responsif, komponen UI)
├── config/
│   └── koneksi.php          # Konfigurasi koneksi MySQLi & penanganan error terintegrasi
├── database.sql             # Skrip SQL untuk membuat database, tabel, dan data awal
├── database_transaksi.sql   # Cadangan skema database transaksi
├── index.php                # Halaman utama: Dashboard & Tabel Master Data Barang (CRUD)
├── tambah.php               # Halaman formulir tambah data barang baru
├── simpan.php               # Skrip PHP backend pemroses simpan data barang baru
├── edit.php                 # Halaman formulir ubah/edit data barang
├── update.php               # Skrip PHP backend pemroses pembaruan data barang
├── hapus.php                # Skrip PHP backend pemroses hapus data barang
├── transaksi_tambah.php     # Halaman formulir input transaksi stok masuk & keluar
├── transaksi_proses.php     # Skrip PHP backend transaksi stok & kalkulasi mutasi atomik
├── transaksi_riwayat.php    # Halaman histori / riwayat seluruh transaksi stok
├── transaksi_hapus.php      # Skrip PHP backend hapus riwayat & rollback stok barang
├── navbar.php               # Komponen navigasi atas (modular reusable component)
├── style.css                # Style loader (importer assets/style.css)
└── README.md                # Dokumentasi lengkap proyek aplikasi
```

---

## 🗄️ Skema Basis Data (MySQL)

Database yang digunakan bernama **`db_stok_barang`** dengan 2 tabel yang saling berelasi:

### 1. Tabel `barang`
Menyimpan master data produk inventaris.
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | `INT AUTO_INCREMENT` | Primary Key |
| `kode_barang` | `VARCHAR(30)` | Unique, Kode pengenal produk (contoh: `BRG-001`) |
| `nama_barang` | `VARCHAR(100)` | Nama lengkap produk |
| `kategori` | `VARCHAR(60)` | Kelompok barang (contoh: `Alat Tulis Kantor`, `Elektronik`) |
| `stok` | `INT` | Jumlah fisik unit yang tersedia di gudang |
| `satuan` | `VARCHAR(20)` | Satuan hitung (`Pcs`, `Rim`, `Unit`, `Botol`, dll.) |
| `harga` | `DECIMAL(15,2)` | Harga satuan produk |
| `created_at` | `TIMESTAMP` | Waktu data pertama kali dibuat |

### 2. Tabel `transaksi`
Menyimpan riwayat mutasi masuk dan keluar.
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id_transaksi` | `INT AUTO_INCREMENT` | Primary Key |
| `tanggal` | `DATE` | Tanggal terjadinya mutasi stok |
| `id_barang` | `INT` | Foreign Key yang berelasi ke `barang.id` |
| `jenis_transaksi`| `ENUM('Masuk','Keluar')` | Jenis mutasi |
| `jumlah` | `INT` | Kuantitas barang yang dimutasi |
| `keterangan` | `TEXT` | Catatan supplier, divisi penerima, atau keterangan dokumen |
| `created_at` | `TIMESTAMP` | Waktu pencatatan mutasi ke sistem |

---

## ⚙️ Petunjuk Instalasi & Menjalankan di XAMPP

1. **Letakkan Folder Proyek di htdocs**:
   Pastikan folder `stok-barang` berada di dalam direktori web server XAMPP:
   ```text
   C:\xampp\htdocs\stok-barang
   ```
2. **Jalankan XAMPP**:
   - Buka **XAMPP Control Panel**.
   - Klik tombol **Start** pada modul **Apache** dan **MySQL** (keduanya berwarna hijau).
3. **Impor Database MySQL**:
   - Buka browser dan kunjungi: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Buat database baru bernama: `db_stok_barang` (atau langsung gunakan tab *Import*).
   - Klik tab **Import** ➔ Klik **Choose File** ➔ Pilih file `database.sql` yang ada di dalam folder proyek `stok-barang`.
   - Klik tombol **Import / Kirim** di bagian bawah.
4. **Buka Aplikasi di Browser**:
   Buka peramban (Chrome, Edge, Firefox) dan akses:
   ```text
   http://localhost/stok-barang/
   ```

---

## 💡 Penerapan Konsep Pemrograman Web

Aplikasi ini menerapkan seluruh konsep dasar yang dipelajari selama perkuliahan:

1. **Variabel & Tipe Data**:
   - String, Integer, Float/Decimal, Boolean, dan Array asosiatif untuk menangani record tabel dan nilai input form.
2. **Operator**:
   - Operator Aritmatika (`+`, `-`, `*`) untuk kalkulasi mutasi stok dan total nilai aset.
   - Operator Perbandingan (`===`, `!==`, `<`, `>`, `<=`) untuk pengecekan batas stok dan validasi.
   - Operator Logika (`&&`, `||`, `!`) untuk validasi input ganda.
3. **Percabangan (Branching)**:
   - Konstruksi `if`, `elseif`, `else` dan operator ternary (`?:`) untuk menentukan status stok (*Tersedia*, *Menipis*, *Habis*), pemilihan arah mutasi (*Masuk* vs *Keluar*), dan handling pesan flash.
4. **Perulangan (Looping)**:
   - `while` untuk mengambil baris data dari kueri MySQL (`$result->fetch_assoc()`).
   - `foreach` untuk merender baris-baris tabel barang, opsi dropdown, dan riwayat transaksi.
5. **Fungsi (Functions)**:
   - Fungsi sanitasi `e()` berbasis `htmlspecialchars` untuk mencegah celah keamanan *Cross-Site Scripting* (XSS).
   - Fungsi modular `proses_mutasi_stok()` yang mengenkapsulasi transaksi database atomik.
6. **Keamanan & Best Practices**:
   - Menggunakan **Prepared Statements (`$conn->prepare`)** pada seluruh operasi SQL untuk mencegah serangan **SQL Injection**.
   - Validasi ganda (*Client-side* via JavaScript & *Server-side* via PHP).
   - Database Transaction (`begin_transaction`, `commit`, `rollback`) untuk integritas data stok.
