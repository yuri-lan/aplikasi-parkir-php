<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Admin & Owner boleh akses
if ($_SESSION['role'] === 'petugas') {
    echo "<script>alert('Akses ditolak! Halaman ini khusus Admin & Owner.'); window.location='".$base_url."index.php';</script>";
    exit;
}

$pdo = getKoneksi($_SESSION['role']);

// Default filter tanggal: Awal bulan ini sampai hari ini
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_sampai = isset($_GET['tgl_sampai']) ? $_GET['tgl_sampai'] : date('Y-m-d');

try {
    if ($_SESSION['role'] === 'admin') {
        // Admin pakai tabel asli (bisa join ke tb_user buat nama petugas)
        $sql = "SELECT t.id_parkir, k.plat_nomor, k.jenis_kendaraan, a.nama_area, 
                       t.waktu_masuk, t.waktu_keluar, t.durasi_jam, t.biaya_total, u.nama_lengkap AS petugas
                FROM tb_transaksi t
                JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                JOIN tb_area_parkir a ON t.id_area = a.id_area
                JOIN tb_user u ON t.id_user = u.id_user
                WHERE t.status = 'keluar' 
                AND DATE(t.waktu_masuk) >= ? AND DATE(t.waktu_masuk) <= ?
                ORDER BY t.waktu_keluar DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tgl_mulai, $tgl_sampai]);
    } else {
        // Owner pakai VIEW (nggak ada nama petugas)
        $sql = "SELECT id_parkir, plat_nomor, jenis_kendaraan, nama_area, 
                       waktu_masuk, waktu_keluar, durasi_jam, biaya_total
                FROM v_rekap_transaksi
                WHERE status = 'keluar' 
                AND DATE(waktu_masuk) >= ? AND DATE(waktu_masuk) <= ?
                ORDER BY waktu_keluar DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tgl_mulai, $tgl_sampai]);
    }
    $laporan = $stmt->fetchAll();

    // Hitung total pendapatan
    $total_pendapatan = 0;
    foreach ($laporan as $row) {
        $total_pendapatan += $row['biaya_total'];
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    $laporan = [];
    $total_pendapatan = 0;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);">Laporan Transaksi Parkir</h3>
</div>

<!-- Form Filter Tanggal -->
<div class="card card-elegan p-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Dari Tanggal</label>
            <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($tgl_mulai) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Sampai Tanggal</label>
            <input type="date" name="tgl_sampai" class="form-control" value="<?= htmlspecialchars($tgl_sampai) ?>" required>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-aksen w-100 py-2"><i class="bi bi-funnel-fill"></i> Terapkan Filter</button>
        </div>
    </form>
</div>

<!-- Card Ringkasan Pendapatan -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card card-elegan p-4 border-start border-4" style="border-left-color: #10B981 !important;">
            <h6 class="text-muted fw-semibold mb-2">Total Pendapatan (Periode Ini)</h6>
            <h2 class="fw-bold mb-0 text-success">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h2>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-elegan p-4 border-start border-4" style="border-left-color: #3B82F6 !important;">
            <h6 class="text-muted fw-semibold mb-2">Total Transaksi Selesai</h6>
            <h2 class="fw-bold mb-0 text-primary"><?= count($laporan) ?> Transaksi</h2>
        </div>
    </div>
</div>

<!-- Tabel Laporan -->
<div class="card card-elegan p-4">
    <table id="tabelLaporan" class="table table-hover align-middle w-100">
        <thead>
            <tr>
                <th>No</th>
                <th>Plat Nomor</th>
                <th>Area</th>
                <th>Waktu Masuk</th>
                <th>Waktu Keluar</th>
                <th>Durasi</th>
                <th>Biaya</th>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <th>Petugas</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($laporan as $index => $row): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($row['plat_nomor']) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($row['nama_area']) ?></span></td>
                    <td><?= date('d-m-Y H:i', strtotime($row['waktu_masuk'])) ?></td>
                    <td><?= date('d-m-Y H:i', strtotime($row['waktu_keluar'])) ?></td>
                    <td><?= $row['durasi_jam'] ?> Jam</td>
                    <td class="fw-bold text-success">Rp <?= number_format($row['biaya_total'], 0, ',', '.') ?></td>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <td><?= htmlspecialchars($row['petugas']) ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>