<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] === 'owner') {
    echo "<script>alert('Akses ditolak!'); window.location='".$base_url."index.php';</script>";
    exit;
}

$pdo = getKoneksi($_SESSION['role']);
$pesan = '';
$tipe_pesan = '';
$struk = null; // Variabel buat nyimpen data struk

// Proses Transaksi Keluar (Berdasarkan Plat Nomor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plat_nomor'])) {
    $plat_nomor = strtoupper(trim($_POST['plat_nomor']));
    $waktu_keluar = date('Y-m-d H:i:s');
    $id_user = $_SESSION['id_user'];

    try {
        // 1. Cari transaksi aktif berdasarkan plat nomor
        $sql_cari = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, a.nama_area, tar.tarif_per_jam 
                     FROM tb_transaksi t
                     JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                     JOIN tb_area_parkir a ON t.id_area = a.id_area
                     JOIN tb_tarif tar ON t.id_tarif = tar.id_tarif
                     WHERE k.plat_nomor = ? AND t.status = 'masuk'";
        $stmt = $pdo->prepare($sql_cari);
        $stmt->execute([$plat_nomor]);
        $trx = $stmt->fetch();

        if ($trx) {
            // 2. Hitung Durasi (Detik)
            $masuk = strtotime($trx['waktu_masuk']);
            $keluar = strtotime($waktu_keluar);
            $selisih_detik = $keluar - $masuk;
            
            // 3. Hitung Jam (Pembulatan ke atas, minimal 1 jam)
            $durasi_jam = ceil($selisih_detik / 3600);
            if ($durasi_jam < 1) { $durasi_jam = 1; }

            // 4. Hitung Biaya Total
            $biaya_total = $durasi_jam * $trx['tarif_per_jam'];

            // 5. Update tb_transaksi
            $sql_update = "UPDATE tb_transaksi SET waktu_keluar=?, durasi_jam=?, biaya_total=?, status='keluar' WHERE id_parkir=?";
            $pdo->prepare($sql_update)->execute([$waktu_keluar, $durasi_jam, $biaya_total, $trx['id_parkir']]);

            // 6. Update Area (kurangi 1)
            $pdo->prepare("UPDATE tb_area_parkir SET terisi = terisi - 1 WHERE id_area = ?")->execute([$trx['id_area']]);

            // 7. Log
            $log = $pdo->prepare("INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES (?, ?)");
            $log->execute([$id_user, "Proses keluar $plat_nomor, Biaya: Rp $biaya_total"]);

            // 8. Simpan data buat Modal Struk
            $struk = [
                'plat' => $plat_nomor,
                'masuk' => $trx['waktu_masuk'],
                'keluar' => $waktu_keluar,
                'durasi' => $durasi_jam,
                'tarif' => $trx['tarif_per_jam'],
                'total' => $biaya_total,
                'area' => $trx['nama_area']
            ];
            $pesan = "Kendaraan berhasil diproses keluar!";
            $tipe_pesan = "success";
        } else {
            $pesan = "Kendaraan dengan plat <b>$plat_nomor</b> tidak ditemukan atau belum masuk!";
            $tipe_pesan = "danger";
        }
    } catch (Exception $e) {
        $pesan = "Error: " . $e->getMessage();
        $tipe_pesan = "danger";
    }
}

// Ambil transaksi yang masih masuk (buat ditampilin di tabel bawah)
if ($_SESSION['role'] === 'admin') {
    $sql_trx = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, a.nama_area, tar.tarif_per_jam, u.nama_lengkap
                FROM tb_transaksi t
                JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                JOIN tb_area_parkir a ON t.id_area = a.id_area
                JOIN tb_tarif tar ON t.id_tarif = tar.id_tarif
                JOIN tb_user u ON t.id_user = u.id_user
                WHERE t.status = 'masuk' ORDER BY t.id_parkir DESC";
} else {
    $sql_trx = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, a.nama_area, tar.tarif_per_jam
                FROM tb_transaksi t
                JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                JOIN tb_area_parkir a ON t.id_area = a.id_area
                JOIN tb_tarif tar ON t.id_tarif = tar.id_tarif
                WHERE t.status = 'masuk' ORDER BY t.id_parkir DESC";
}
$transaksi_aktif = $pdo->query($sql_trx)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);">Transaksi Parkir Keluar</h3>
</div>

<!-- Alert Pesan -->
<?php if ($pesan): ?>
    <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show shadow-sm" style="font-size: 1.1rem;">
        <i class="bi bi-info-circle-fill me-2"></i> <?= $pesan ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Form Pencarian & Proses Keluar -->
    <div class="col-md-5">
        <div class="card card-elegan p-4">
            <h5 class="fw-bold mb-3">Proses Kendaraan Keluar</h5>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Masukkan Plat Nomor <span class="text-danger">*</span></label>
                    <input type="text" name="plat_nomor" class="form-control text-uppercase form-control-lg" placeholder="Contoh: B 1234 XYZ" required autofocus>
                    <div class="form-text small">Ketik plat nomor kendaraan yang ingin keluar.</div>
                </div>
                <button type="submit" class="btn btn-warning w-100 py-2 fw-bold text-dark"><i class="bi bi-box-arrow-right"></i> Cari & Proses Keluar</button>
            </form>
        </div>
    </div>

    <!-- Tabel Kendaraan Sedang Parkir -->
    <div class="col-md-7">
        <div class="card card-elegan p-4">
            <h5 class="fw-bold mb-3">Kendaraan Sedang Parkir</h5>
            <div class="table-responsive">
                <table id="tabelKeluar" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Plat Nomor</th>
                            <th>Area</th>
                            <th>Waktu Masuk</th>
                            <th>Tarif/Jam</th>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <th>Petugas</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transaksi_aktif as $index => $trx): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($trx['plat_nomor']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($trx['nama_area']) ?></span></td>
                                <td><?= date('d-m-Y H:i', strtotime($trx['waktu_masuk'])) ?></td>
                                <td>Rp <?= number_format($trx['tarif_per_jam'], 0, ',', '.') ?></td>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <td><?= htmlspecialchars($trx['nama_lengkap']) ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL STRUK PEMBAYARAN -->
<?php if ($struk): ?>
<div class="modal fade show" id="modalStruk" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                </div>
                <h4 class="fw-bold mb-1">Pembayaran Berhasil</h4>
                <p class="text-muted small mb-4">Struk Parkir Digital</p>

                <div class="text-start p-3 rounded-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Plat Nomor</span>
                        <span class="fw-bold fs-5"><?= $struk['plat'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Area</span>
                        <span class="fw-semibold"><?= $struk['area'] ?></span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between mb-1 small">
                        <span class="text-muted">Masuk</span>
                        <span><?= date('d-m-Y H:i', strtotime($struk['masuk'])) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Keluar</span>
                        <span><?= date('d-m-Y H:i', strtotime($struk['keluar'])) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Durasi</span>
                        <span><?= $struk['durasi'] ?> Jam</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Tarif / Jam</span>
                        <span>Rp <?= number_format($struk['tarif'], 0, ',', '.') ?></span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="fw-bold">Total Biaya</span>
                        <span class="fw-bold fs-4 text-success">Rp <?= number_format($struk['total'], 0, ',', '.') ?></span>
                    </div>
                </div>

                <button type="button" class="btn btn-primary w-100 mt-4 py-2" onclick="window.location.href='keluar.php'">
                    <i class="bi bi-arrow-clockwise"></i> Tutup & Proses Lagi
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    // Biar pas modal muncul, scroll-nya ketahan
    document.body.style.overflow = 'hidden';
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>