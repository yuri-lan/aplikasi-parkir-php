<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'petugas') { header("Location: ../index.php"); exit; }

$pdo = getKoneksi('petugas');
$id_kendaraan = $_GET['id'];
$pesan = '';

// Ambil data lama
$stmt = $pdo->prepare("SELECT * FROM tb_kendaraan WHERE id_kendaraan = ?");
$stmt->execute([$id_kendaraan]);
$k = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_lama = "Plat: {$k['plat_nomor']}, Jenis: {$k['jenis_kendaraan']}, Warna: {$k['warna']}, Pemilik: {$k['pemilik']}";
    $data_baru = "Plat: {$_POST['plat_nomor']}, Jenis: {$_POST['jenis_kendaraan']}, Warna: {$_POST['warna']}, Pemilik: {$_POST['pemilik']}";
    $pesan_tambahan = $_POST['pesan_tambahan'];
    $id_petugas = $_SESSION['id_user'];

    try {
        $sql = "INSERT INTO tb_laporan (id_kendaraan, id_petugas, data_lama, data_baru, pesan_tambahan) VALUES (?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$id_kendaraan, $id_petugas, $data_lama, $data_baru, $pesan_tambahan]);
        $pesan = "Laporan berhasil dikirim ke Admin!";
        echo "<meta http-equiv='refresh' content='2;url=chat.php'>";
    } catch (Exception $e) {
        $pesan = "Gagal mengirim laporan: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);">Lapor Kesalahan Data</h3>
    <a href="../master/kendaraan.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<?php if ($pesan): ?><div class="alert alert-success"><?= $pesan ?></div><?php endif; ?>

<div class="card card-elegan p-4">
    <form method="POST">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 text-muted">
                    <h6 class="fw-bold mb-2">Data Lama (Saat Ini)</h6>
                    <div>Plat: <b><?= $k['plat_nomor'] ?></b></div>
                    <div>Jenis: <?= $k['jenis_kendaraan'] ?></div>
                    <div>Warna: <?= $k['warna'] ?></div>
                    <div>Pemilik: <?= $k['pemilik'] ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded-3" style="background: var(--bg-icon);">
                    <h6 class="fw-bold mb-2" style="color: var(--warna-teks-aksen);">Data Baru (Yang Benar)</h6>
                    <div class="mb-2">
                        <input type="text" name="plat_nomor" class="form-control form-control-sm" value="<?= $k['plat_nomor'] ?>" required>
                    </div>
                    <div class="mb-2">
                        <select name="jenis_kendaraan" class="form-select form-select-sm" required>
                            <option value="motor" <?= $k['jenis_kendaraan'] == 'motor' ? 'selected' : '' ?>>Motor</option>
                            <option value="mobil" <?= $k['jenis_kendaraan'] == 'mobil' ? 'selected' : '' ?>>Mobil</option>
                            <option value="lainnya" <?= $k['jenis_kendaraan'] == 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="warna" class="form-control form-control-sm" value="<?= $k['warna'] ?>" placeholder="Warna">
                    </div>
                    <div>
                        <input type="text" name="pemilik" class="form-control form-control-sm" value="<?= $k['pemilik'] ?>" placeholder="Pemilik">
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label fw-bold">Pesan Tambahan / Alasan Koreksi</label>
            <textarea name="pesan_tambahan" class="form-control" rows="3" placeholder="Contoh: Plat di STNK ternyata BH 1234 XY, tolong diperbaiki." required></textarea>
        </div>
        <button type="submit" class="btn btn-aksen w-100 py-2"><i class="bi bi-send-fill"></i> Kirim Laporan ke Admin</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>