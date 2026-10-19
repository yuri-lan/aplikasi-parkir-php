<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') { exit; }

$pdo = getKoneksi('admin');
$is_edit = false;
$id_area = '';
$nama_area = '';
$kapasitas = '';
$pesan = '';

if (isset($_GET['id'])) {
    $is_edit = true;
    $id_area = $_GET['id'];
    $data = $pdo->prepare("SELECT * FROM tb_area_parkir WHERE id_area = ?");
    $data->execute([$id_area]);
    $d = $data->fetch();
    if ($d) {
        $nama_area = $d['nama_area'];
        $kapasitas = $d['kapasitas'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_area = trim($_POST['nama_area']);
    $kapasitas = $_POST['kapasitas'];

    if ($is_edit) {
        $pdo->prepare("UPDATE tb_area_parkir SET nama_area=?, kapasitas=? WHERE id_area=?")->execute([$nama_area, $kapasitas, $id_area]);
        $pesan = "Area berhasil diupdate!";
    } else {
        $pdo->prepare("INSERT INTO tb_area_parkir (nama_area, kapasitas) VALUES (?, ?)")->execute([$nama_area, $kapasitas]);
        $pesan = "Area baru berhasil ditambahkan!";
    }
    echo "<meta http-equiv='refresh' content='1;url=area.php'>";
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);"><?= $is_edit ? 'Edit Area' : 'Tambah Area' ?></h3>
    <a href="area.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<?php if ($pesan): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= $pesan ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card card-elegan p-4 p-md-5" style="max-width: 600px;">
    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Nama Area <span class="text-danger">*</span></label>
            <input type="text" name="nama_area" class="form-control" value="<?= htmlspecialchars($nama_area) ?>" required placeholder="Contoh: Area C - Motor">
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Kapasitas (Slot) <span class="text-danger">*</span></label>
            <input type="number" name="kapasitas" class="form-control" value="<?= htmlspecialchars($kapasitas) ?>" required placeholder="Contoh: 50">
        </div>
        <button type="submit" class="btn btn-aksen w-100 py-2"><i class="bi bi-save"></i> Simpan Area</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>