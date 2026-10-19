<?php
require_once 'config/database.php';

echo "<h2>Testing Koneksi PDO</h2>";

// 1. Tes Koneksi Admin
try {
    $pdoAdmin = getKoneksi('admin');
    echo "✅ <b>Admin:</b> Berhasil terhubung! ";
    $stmt = $pdoAdmin->query("SELECT COUNT(*) AS total FROM tb_user");
    echo "Total user: " . $stmt->fetch()['total'] . "<br>";
} catch (Exception $e) {
    echo "❌ <b>Admin:</b> Gagal - " . $e->getMessage() . "<br>";
}

// 2. Tes Koneksi Petugas
try {
    $pdoPetugas = getKoneksi('petugas');
    echo "✅ <b>Petugas:</b> Berhasil terhubung! ";
    $stmt = $pdoPetugas->query("SELECT COUNT(*) AS total FROM tb_transaksi");
    echo "Total transaksi: " . $stmt->fetch()['total'] . "<br>";
} catch (Exception $e) {
    echo "❌ <b>Petugas:</b> Gagal - " . $e->getMessage() . "<br>";
}

// 3. Tes Koneksi Owner (Hanya bisa akses view)
try {
    $pdoOwner = getKoneksi('owner');
    echo "✅ <b>Owner:</b> Berhasil terhubung! ";
    $stmt = $pdoOwner->query("SELECT COUNT(*) AS total FROM v_rekap_transaksi");
    echo "Total rekap: " . $stmt->fetch()['total'] . "<br>";
} catch (Exception $e) {
    echo "❌ <b>Owner:</b> Gagal - " . $e->getMessage() . "<br>";
}
?>