<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] === 'owner') {
    echo "<script>alert('Akses ditolak!'); window.location='".$base_url."index.php';</script>";
    exit;
}

$pdo = getKoneksi($_SESSION['role']);
$pesan = ''; $tipe_pesan = '';
$struk_masuk = null; // Buat nampilin modal tiket

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plat_nomor = strtoupper(trim($_POST['plat_nomor']));
    $jenis_kendaraan = $_POST['jenis_kendaraan'];
    $warna = !empty($_POST['warna']) ? trim($_POST['warna']) : null;
    $pemilik = !empty($_POST['pemilik']) ? trim($_POST['pemilik']) : null;
    $id_tarif = $_POST['id_tarif'];
    $id_area = $_POST['id_area'];
    $waktu_masuk = date('Y-m-d H:i:s');
    $id_user = $_SESSION['id_user'];

    try {
        $stmt_cek = $pdo->prepare("SELECT kapasitas, terisi FROM tb_area_parkir WHERE id_area = ?");
        $stmt_cek->execute([$id_area]);
        $area = $stmt_cek->fetch();

        if ($area['terisi'] >= $area['kapasitas']) {
            $pesan = "Gagal! Area parkir sudah penuh.";
            $tipe_pesan = "danger";
        } else {
            $stmt_plat = $pdo->prepare("SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor = ?");
            $stmt_plat->execute([$plat_nomor]);
            $kendaraan_ada = $stmt_plat->fetch();

            if ($kendaraan_ada) {
                $id_kendaraan = $kendaraan_ada['id_kendaraan'];
            } else {
                $sql_insert_kendaraan = "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, pemilik, id_user) VALUES (?, ?, ?, ?, ?)";
                $pdo->prepare($sql_insert_kendaraan)->execute([$plat_nomor, $jenis_kendaraan, $warna, $pemilik, $id_user]);
                $id_kendaraan = $pdo->lastInsertId();
            }

            // BIKIN KODE TIKET UNIK (TKT-XXXX)
            $kode_tiket = "TKT-" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

            // Insert Transaksi + Kode Tiket
            $sql_trx = "INSERT INTO tb_transaksi (kode_tiket, id_kendaraan, waktu_masuk, id_tarif, status, id_user, id_area) VALUES (?, ?, ?, ?, 'masuk', ?, ?)";
            $pdo->prepare($sql_trx)->execute([$kode_tiket, $id_kendaraan, $waktu_masuk, $id_tarif, $id_user, $id_area]);

            $pdo->prepare("UPDATE tb_area_parkir SET terisi = terisi + 1 WHERE id_area = ?")->execute([$id_area]);

            $log = $pdo->prepare("INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES (?, ?)");
            $log->execute([$id_user, "Kendaraan masuk ($plat_nomor) | Tiket: $kode_tiket"]);

            // Siapin data buat modal struk masuk
            $struk_masuk = [
                'kode' => $kode_tiket,
                'plat' => $plat_nomor,
                'area' => $pdo->query("SELECT nama_area FROM tb_area_parkir WHERE id_area = $id_area")->fetchColumn(),
                'masuk' => $waktu_masuk
            ];
            $pesan = "Transaksi masuk berhasil!";
            $tipe_pesan = "success";
        }
    } catch (Exception $e) {
        $pesan = "Terjadi kesalahan: " . $e->getMessage();
        $tipe_pesan = "danger";
    }
}

$tarifs = $pdo->query("SELECT * FROM tb_tarif ORDER BY jenis_kendaraan ASC")->fetchAll();
$areas = $pdo->query("SELECT * FROM tb_area_parkir WHERE terisi < kapasitas ORDER BY nama_area ASC")->fetchAll();

if ($_SESSION['role'] === 'admin') {
    $sql_trx = "SELECT t.*, k.plat_nomor, a.nama_area, u.nama_lengkap FROM tb_transaksi t JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan JOIN tb_area_parkir a ON t.id_area = a.id_area JOIN tb_user u ON t.id_user = u.id_user WHERE t.status = 'masuk' ORDER BY t.id_parkir DESC";
} else {
    $sql_trx = "SELECT t.*, k.plat_nomor, a.nama_area FROM tb_transaksi t JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan JOIN tb_area_parkir a ON t.id_area = a.id_area WHERE t.status = 'masuk' ORDER BY t.id_parkir DESC";
}
$transaksi_aktif = $pdo->query($sql_trx)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);">Transaksi Parkir Masuk</h3>
</div>

<?php if ($pesan): ?>
    <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show"><i class="bi bi-info-circle-fill me-2"></i> <?= $pesan ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card card-elegan p-4">
            <h5 class="fw-bold mb-3">Input Kendaraan Masuk</h5>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Plat Nomor <span class="text-danger">*</span></label>
                    <input type="text" name="plat_nomor" class="form-control text-uppercase" placeholder="B 1234 XYZ" required autofocus>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><input type="text" name="warna" class="form-control" placeholder="Warna"></div>
                    <div class="col-6"><input type="text" name="pemilik" class="form-control" placeholder="Pemilik"></div>
                </div>
                <div class="mb-3">
                    <select name="jenis_kendaraan" class="form-select" required>
                        <option value="">-- Jenis Kendaraan --</option>
                        <option value="motor">Motor</option>
                        <option value="mobil">Mobil</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="mb-3">
                    <select name="id_tarif" class="form-select" required>
                        <option value="">-- Pilih Tarif --</option>
                        <?php foreach ($tarifs as $t): ?>
                            <option value="<?= $t['id_tarif'] ?>"><?= ucfirst($t['jenis_kendaraan']) ?> - Rp <?= number_format($t['tarif_per_jam'], 0, ',', '.') ?>/jam</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <select name="id_area" class="form-select" required>
                        <option value="">-- Pilih Area --</option>
                        <?php foreach ($areas as $a): ?>
                            <option value="<?= $a['id_area'] ?>"><?= htmlspecialchars($a['nama_area']) ?> (Sisa: <?= $a['kapasitas'] - $a['terisi'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-aksen w-100 py-2"><i class="bi bi-box-arrow-in-right"></i> Proses Masuk</button>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card card-elegan p-4">
            <h5 class="fw-bold mb-3">Kendaraan Sedang Parkir</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="tabelMasuk">
                    <thead><tr><th>No</th><th>Kode</th><th>Plat</th><th>Area</th><th>Masuk</th></tr></thead>
                    <tbody>
                        <?php foreach ($transaksi_aktif as $index => $trx): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="fw-bold text-primary"><?= $trx['kode_tiket'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($trx['plat_nomor']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($trx['nama_area']) ?></span></td>
                                <td><?= date('d-m H:i', strtotime($trx['waktu_masuk'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL STRUK MASUK (TIKET) -->
<?php if ($struk_masuk): ?>
<div class="modal fade show" id="modalTiket" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-body p-4 text-center">
                <h4 class="fw-bold mb-1 text-primary">Tiket Parkir</h4>
                <p class="text-muted small mb-4">Simpan struk ini atau scan QR saat keluar</p>

                <div class="text-start p-3 rounded-3 mb-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                    <div class="text-center mb-3">
                        <canvas id="qrcode" class="mb-2"></canvas>
                        <div class="fw-bold fs-4 text-dark" id="kode_tiket_text"><?= $struk_masuk['kode'] ?></div>
                    </div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Plat Nomor</span><span class="fw-bold"><?= $struk_masuk['plat'] ?></span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Area</span><span class="fw-semibold"><?= $struk_masuk['area'] ?></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Waktu Masuk</span><span><?= date('d-m-Y H:i', strtotime($struk_masuk['masuk'])) ?></span></div>
                </div>

                <button type="button" class="btn btn-primary w-100 mt-2 py-2" onclick="window.location.href='masuk.php'">
                    <i class="bi bi-printer"></i> Cetak & Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT BUAT GENERATE QR CODE -->
<script src="<?= $base_url ?>assets/js/qrcode.min.js"></script>
<script>
    // Generate QR Code otomatis dari kode tiket
    new QRCode(document.getElementById("qrcode"), {
        text: "<?= $struk_masuk['kode'] ?>", // Isinya kode tiket
        width: 150,
        height: 150,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
    document.body.style.overflow = 'hidden';
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>