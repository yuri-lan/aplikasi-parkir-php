<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') { exit; }

$pdo = getKoneksi('admin');
$is_edit = false;
$id_tarif = '';
$jenis_kendaraan = '';
$tarif_per_jam = '';
$pesan = '';

if (isset($_GET['id'])) {
    $is_edit = true;
    $id_tarif = $_GET['id'];
    $data = $pdo->prepare("SELECT * FROM tb_tarif WHERE id_tarif = ?");
    $data->execute([$id_tarif]);
    $d = $data->fetch();
    if ($d) {
        $jenis_kendaraan = $d['jenis_kendaraan'];
        $tarif_per_jam = $d['tarif_per_jam'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_kendaraan = $_POST['jenis_kendaraan'];
    $tarif_per_jam = $_POST['tarif_per_jam'];

    if ($is_edit) {
        $pdo->prepare("UPDATE tb_tarif SET jenis_kendaraan=?, tarif_per_jam=? WHERE id_tarif=?")->execute([$jenis_kendaraan, $tarif_per_jam, $id_tarif]);
        $pesan = "Tarif berhasil diupdate!";
    } else {
        $pdo->prepare("INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam) VALUES (?, ?)")->execute([$jenis_kendaraan, $tarif_per_jam]);
        $pesan = "Tarif baru berhasil ditambahkan!";
    }
    echo "<meta http-equiv='refresh' content='1;url=tarif.php'>";
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);"><?= $is_edit ? 'Edit Tarif' : 'Tambah Tarif' ?></h3>
    <a href="tarif.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<?php if ($pesan): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= $pesan ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card card-elegan p-4 p-md-5" style="max-width: 600px;">
    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Jenis Kendaraan <span class="text-danger">*</span></label>
            <select name="jenis_kendaraan" class="form-select" required>
                <option value="motor" <?= $jenis_kendaraan === 'motor' ? 'selected' : '' ?>>Motor</option>
                <option value="mobil" <?= $jenis_kendaraan === 'mobil' ? 'selected' : '' ?>>Mobil</option>
                <option value="lainnya" <?= $jenis_kendaraan === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Tarif Per Jam (Rp) <span class="text-danger">*</span></label>
            <input type="number" name="tarif_per_jam" class="form-control" value="<?= htmlspecialchars($tarif_per_jam) ?>" required placeholder="Contoh: 2000">
        </div>
        <button type="submit" class="btn btn-aksen w-100 py-2"><i class="bi bi-save"></i> Simpan Tarif</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>