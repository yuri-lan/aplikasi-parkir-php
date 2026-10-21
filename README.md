# 🅿️ ParkirApp - Sistem Manajemen Parkir Terpadu

Aplikasi manajemen parkir berbasis web yang dibangun menggunakan **PHP Native** dan **MySQL**. Aplikasi ini didesain dengan antarmuka modern (Glassmorphism), responsif di perangkat mobile, mendukung **Dark Mode**, serta dilengkapi dengan sistem keamanan database **Least Privilege (DCL)**.

---

## ✨ Fitur Utama

### 🛡️ Multi-Role & Keamanan (Least Privilege)
Aplikasi ini menggunakan 3 level pengguna dengan hak akses yang dibatasi secara ketat langsung dari level database (MySQL User & Grant):
- **Admin**: Akses penuh untuk mengelola User, Tarif, Area, Kendaraan, Transaksi, Laporan, dan Log Aktivitas.
- **Petugas**: Fokus pada operasional. Bisa mengelola data kendaraan dan memproses parkir masuk/keluar. Tidak bisa mengedit data sembarangan (harus lapor ke Admin).
- **Owner**: Hanya bisa melihat Dashboard (Grafik Pendapatan) dan Laporan. Menggunakan *View* khusus agar tidak bisa melihat data sensitif (seperti password user).

### 🚗 Fitur Transaksi Parkir
- **Input Plat Bebas**: Petugas bisa langsung mengetik plat nomor tanpa harus mendaftar kendaraan terlebih dahulu. Sistem akan otomatis mendaftarkannya.
- **Cek Kapasitas Real-time**: Kendaraan akan ditolak masuk jika area parkir yang dipilih sudah penuh.
- **Hitung Biaya Otomatis**: Durasi dan biaya dihitung otomatis (pembulatan ke atas, minimal 1 jam).
- **Struk Digital (Pop-up)**: Menampilkan rincian biaya elegan setelah kendaraan diproses keluar.

### 📊 Laporan & Dashboard Interaktif
- **Grafik Pendapatan**: Menggunakan Chart.js untuk menampilkan pendapatan 7 hari terakhir.
- **Export Excel & PDF**: Fitur unduh laporan transaksi (dengan filter tanggal) dan data master menggunakan DataTables.
- **Sistem Ticketing (Lapor Kesalahan)**: Petugas dapat mengirim laporan data kendaraan yang salah (Data Lama vs Data Baru) ke Admin. Admin dapat meng-*approve* laporan tersebut.
- **Log Aktivitas**: Mencatat setiap aksi yang dilakukan oleh pengguna (Login, Input, Proses Keluar, dll).
- **Pantau Lahan Tanpa Login**: Halaman login menampilkan sisa slot parkir secara *real-time*.

### 🎨 UI/UX Menarik
- Desain halaman login bergaya **Glassmorphism** dengan animasi lengkungan abstrak.
- **Dark / Light Mode**: Tema dapat diganti dan tersimpan di *localStorage* browser.
- **Fully Responsive**: Sidebar otomatis berubah menjadi menu *Hamburger (Offcanvas)* saat dibuka di HP/Tablet.

---

## 💻 Teknologi yang Digunakan
- **Backend**: PHP 8.x (Native), PDO (Prepared Statements untuk keamanan).
- **Database**: MySQL / MariaDB (InnoDB, Foreign Key, View, DCL).
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5.
- **Library Tambahan**: DataTables (Tabel & Export), Chart.js (Grafik), Bootstrap Icons, jQuery.

---

## 🚀 Cara Install (Localhost / XAMPP)

1. **Persiapan**: Pastikan komputer sudah terinstall XAMPP (Apache & MySQL berjalan).
2. **Clone / Download**: Download folder proyek ini atau jalankan perintah:
   ```bash
   git clone https://github.com/yuri-lan/aplikasi-parkir-php.git
