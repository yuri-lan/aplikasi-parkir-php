<?php
// config/database.php

// Set Timezone ke Waktu Indonesia Barat (WIB)
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_parkir');

// Fungsi untuk mendapatkan koneksi PDO berdasarkan role
function getKoneksi($role = 'admin') {
    // Tentukan user & password MySQL berdasarkan role
    switch ($role) {
        case 'petugas':
            $db_user = 'petugas_parkir';
            $db_pass = 'Petugas#2026';
            break;
        case 'owner':
            $db_user = 'owner_parkir';
            $db_pass = 'OwnerBaru#2026'; // Pakai password baru hasil ALTER USER
            break;
        case 'admin':
        default:
            $db_user = 'admin_parkir';
            $db_pass = 'Admin#2026';
            break;
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Tampilkan error dalam bentuk Exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Hasil fetch berupa array asosiatif
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Gunakan prepared statement asli MySQL
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+07:00'", // SET TIMEZONE DATABASE KE WIB
        ];
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Jika gagal konek, tampilkan pesan error
        die("Koneksi Database Gagal: " . $e->getMessage());
    }
}
?>