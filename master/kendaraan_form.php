<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] === 'owner') { exit; }

$pdo = getKoneksi($_SESSION['role']);
$is_edit = false;
$id_kendaraan = '';
$plat_nomor = '';
$jenis_kendaraan = 'motor';
$warna = '';
$pemilik = '';
$pesan = '';

if (isset($_GET['id'])) {
    $is_edit = true;
    $id_kendaraan = $_GET['id'];
    $data = $pdo->prepare("SELECT * FROM tb_kendaraan WHERE id_kendaraan = ?");
    $data->execute([$id_kendaraan]);
    $d = $data->fetch();
    if ($d) {
        $plat_nomor = $d['plat_nomor'];
        $jenis_kendaraan = $d['jenis_kendaraan'];
        $warna = $d['warna'];
        $pemilik = $d['pemilik'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plat_nomor = strtoupper(trim($_POST['plat_nomor'])); // Otomatis jadi huruf besar
    $jenis_kendaraan = $_POST['jenis_kendaraan'];
    $warna = trim($_POST['warna']);
    $pemilik = trim($_POST['pemilik']);
    $id_user = $_SESSION['id_user']; // Ambil ID user yang lagi login

    try {
        if ($is_edit) {
            $sql = "UPDATE tb_kendaraan SET plat_nomor=?, jenis_kendaraan=?, warna=?, pemilik=? WHERE id_kendaraan=?";
            $pdo->prepare($sql)->execute([$plat_nomor, $jenis_kendaraan, $warna, $pemilik, $id_kendaraan]);
            $pesan = "Data kendaraan berhasil diupdate!";
        } else {
            $sql = "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, pemilik, id_user) VALUES (?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$plat_nomor, $jenis_kendaraan, $warna, $pemilik, $id_user]);
            $pesan = "Kendaraan baru berhasil ditambahkan!";
        }
        echo "<meta http-equiv='refresh' content='1;url=kendaraan.php'>";
    } catch (PDOException $e) {
        $pesan = "Error: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);"><?= $is_edit ? 'Edit Kendaraan' : 'Tambah Kendaraan' ?></h3>
    <a href="kendaraan.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<?php if ($pesan): ?>
    <div class="alert alert-info alert-dismissible fade show"><?= $pesan ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card card-elegan p-4 p-md-5" style="max-width: 800px;">
    <form method="POST">
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Plat Nomor <span class="text-danger">*</span></label>
                <input type="text" name="plat_nomor" class="form-control text-uppercase" value="<?= htmlspecialchars($plat_nomor) ?>" required placeholder="Contoh: M 1234 AB">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Jenis Kendaraan <span class="text-danger">*</span></label>
                <select name="jenis_kendaraan" class="form-select" required>
                    <option value="motor" <?= $jenis_kendaraan === 'motor' ? 'selected' : '' ?>>Motor</option>
                    <option value="mobil" <?= $jenis_kendaraan === 'mobil' ? 'selected' : '' ?>>Mobil</option>
                    <option value="lainnya" <?= $jenis_kendaraan === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Warna</label>
                <input type="text" name="warna" class="form-control" value="<?= htmlspecialchars($warna) ?>" placeholder="Contoh: Hitam">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Pemilik</label>
                <input type="text" name="pemilik" class="form-control" value="<?= htmlspecialchars($pemilik) ?>" placeholder="Nama pemilik kendaraan">
            </div>
        </div>
        <hr class="my-4">
        <button type="submit" class="btn btn-aksen w-100 py-2"><i class="bi bi-save"></i> Simpan Kendaraan</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>