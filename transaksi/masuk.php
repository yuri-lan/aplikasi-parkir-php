<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Admin & Petugas boleh akses, Owner dilarang
if ($_SESSION['role'] === 'owner') {
    echo "<script>alert('Akses ditolak!'); window.location='".$base_url."index.php';</script>";
    exit;
}

$pdo = getKoneksi($_SESSION['role']);
$pesan = '';
$tipe_pesan = '';

// Proses Tambah Transaksi Masuk (Input Plat Bebas + Warna/Pemilik)
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
        // 1. Cek kapasitas area dulu
        $stmt_cek = $pdo->prepare("SELECT kapasitas, terisi FROM tb_area_parkir WHERE id_area = ?");
        $stmt_cek->execute([$id_area]);
        $area = $stmt_cek->fetch();

        if ($area['terisi'] >= $area['kapasitas']) {
            $pesan = "Gagal! Area parkir sudah penuh.";
            $tipe_pesan = "danger";
        } else {
            // 2. Cek apakah plat nomor sudah ada
            $stmt_plat = $pdo->prepare("SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor = ?");
            $stmt_plat->execute([$plat_nomor]);
            $kendaraan_ada = $stmt_plat->fetch();

            if ($kendaraan_ada) {
                $id_kendaraan = $kendaraan_ada['id_kendaraan'];
                // Opsional: Kalau data warna/pemilik baru diisi, bisa diupdate (tapi kita skip dulu biar aman)
            } else {
                // Kalau belum ada, daftarin otomatis beserta warna & pemilik
                $sql_insert_kendaraan = "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, pemilik, id_user) VALUES (?, ?, ?, ?, ?)";
                $pdo->prepare($sql_insert_kendaraan)->execute([$plat_nomor, $jenis_kendaraan, $warna, $pemilik, $id_user]);
                $id_kendaraan = $pdo->lastInsertId();
            }

            // 3. Insert ke tb_transaksi
            $sql_trx = "INSERT INTO tb_transaksi (id_kendaraan, waktu_masuk, id_tarif, status, id_user, id_area) VALUES (?, ?, ?, 'masuk', ?, ?)";
            $pdo->prepare($sql_trx)->execute([$id_kendaraan, $waktu_masuk, $id_tarif, $id_user, $id_area]);

            // 4. Update kapasitas area
            $pdo->prepare("UPDATE tb_area_parkir SET terisi = terisi + 1 WHERE id_area = ?")->execute([$id_area]);

            // 5. Log
            $log = $pdo->prepare("INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES (?, ?)");
            $log->execute([$id_user, "Kendaraan masuk ($plat_nomor) di Area ID: $id_area"]);

            $pesan = "Transaksi masuk untuk <b>$plat_nomor</b> berhasil dicatat!";
            $tipe_pesan = "success";
            echo "<meta http-equiv='refresh' content='2;url=masuk.php'>";
        }
    } catch (Exception $e) {
        $pesan = "Terjadi kesalahan: " . $e->getMessage();
        $tipe_pesan = "danger";
    }
}

$tarifs = $pdo->query("SELECT * FROM tb_tarif ORDER BY jenis_kendaraan ASC")->fetchAll();
$areas = $pdo->query("SELECT * FROM tb_area_parkir WHERE terisi < kapasitas ORDER BY nama_area ASC")->fetchAll();

if ($_SESSION['role'] === 'admin') {
    $sql_trx = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.warna, k.pemilik, a.nama_area, u.nama_lengkap 
                FROM tb_transaksi t
                JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                JOIN tb_area_parkir a ON t.id_area = a.id_area
                JOIN tb_user u ON t.id_user = u.id_user
                WHERE t.status = 'masuk' ORDER BY t.id_parkir DESC";
} else {
    $sql_trx = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.warna, k.pemilik, a.nama_area 
                FROM tb_transaksi t
                JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                JOIN tb_area_parkir a ON t.id_area = a.id_area
                WHERE t.status = 'masuk' ORDER BY t.id_parkir DESC";
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
    <!-- Form Input Masuk -->
    <div class="col-md-5">
        <div class="card card-elegan p-4">
            <h5 class="fw-bold mb-3">Input Kendaraan Masuk</h5>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Plat Nomor <span class="text-danger">*</span></label>
                    <input type="text" name="plat_nomor" class="form-control text-uppercase" placeholder="Contoh: B 1234 XYZ" required autofocus>
                </div>
                
                <!-- TAMBAHAN KOLOM WARNA & PEMILIK -->
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Warna</label>
                        <input type="text" name="warna" class="form-control" placeholder="Contoh: Hitam">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Pemilik</label>
                        <input type="text" name="pemilik" class="form-control" placeholder="Nama pemilik">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Jenis Kendaraan <span class="text-danger">*</span></label>
                    <select name="jenis_kendaraan" class="form-select" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="motor">Motor</option>
                        <option value="mobil">Mobil</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Tarif <span class="text-danger">*</span></label>
                    <select name="id_tarif" class="form-select" required>
                        <option value="">-- Pilih Tarif --</option>
                        <?php foreach ($tarifs as $t): ?>
                            <option value="<?= $t['id_tarif'] ?>"><?= ucfirst($t['jenis_kendaraan']) ?> - Rp <?= number_format($t['tarif_per_jam'], 0, ',', '.') ?>/jam</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Pilih Area Parkir (Tersedia) <span class="text-danger">*</span></label>
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

    <!-- Tabel Kendaraan Sedang Parkir -->
    <div class="col-md-7">
        <div class="card card-elegan p-4">
            <h5 class="fw-bold mb-3">Kendaraan Sedang Parkir</h5>
            <div class="table-responsive">
                <table id="tabelMasuk" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Plat Nomor</th>
                            <th>Jenis</th>
                            <th>Warna</th>
                            <th>Pemilik</th>
                            <th>Area</th>
                            <th>Waktu Masuk</th>
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
                                <td class="text-capitalize"><?= htmlspecialchars($trx['jenis_kendaraan']) ?></td>
                                <td><?= htmlspecialchars($trx['warna'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($trx['pemilik'] ?? '-') ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($trx['nama_area']) ?></span></td>
                                <td><?= date('d-m-Y H:i', strtotime($trx['waktu_masuk'])) ?></td>
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

<?php require_once '../includes/footer.php'; ?>