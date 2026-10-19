<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); window.location='".$base_url."index.php';</script>";
    exit;
}

$pdo = getKoneksi('admin');
$pesan = '';

// Proses Hapus Area
if (isset($_GET['hapus'])) {
    try {
        $pdo->prepare("DELETE FROM tb_area_parkir WHERE id_area = ?")->execute([$_GET['hapus']]);
        $pesan = "Area parkir berhasil dihapus!";
        echo "<meta http-equiv='refresh' content='1;url=area.php'>";
    } catch (Exception $e) {
        $pesan = "Gagal menghapus area. Data mungkin terpakai di transaksi.";
    }
}

$areas = $pdo->query("SELECT * FROM tb_area_parkir ORDER BY id_area ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);">Manajemen Area Parkir</h3>
    <a href="area_form.php" class="btn btn-aksen"><i class="bi bi-plus-circle-fill"></i> Tambah Area Baru</a>
</div>

<?php if ($pesan): ?>
    <div class="alert alert-info alert-dismissible fade show"><i class="bi bi-info-circle-fill me-2"></i> <?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card card-elegan p-4">
    <table id="tabelArea" class="table table-hover align-middle w-100">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Area</th>
                <th>Kapasitas</th>
                <th>Terisi</th>
                <th>Sisa Slot</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($areas as $index => $area): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($area['nama_area']) ?></td>
                    <td><?= $area['kapasitas'] ?> Slot</td>
                    <td><span class="badge bg-warning text-dark"><?= $area['terisi'] ?> Terisi</span></td>
                    <td>
                        <?php $sisa = $area['kapasitas'] - $area['terisi']; ?>
                        <span class="badge <?= $sisa > 0 ? 'bg-success' : 'bg-danger' ?>">
                            <?= $sisa ?> Slot
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="area_form.php?id=<?= $area['id_area'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="bi bi-pencil-square"></i> Edit</a>
                        <a href="area.php?hapus=<?= $area['id_area'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Yakin hapus area ini?')"><i class="bi bi-trash"></i> Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Tambahin inisialisasi DataTables ke footer global
</script>

<?php require_once '../includes/footer.php'; ?>