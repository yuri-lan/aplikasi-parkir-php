<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); window.location='".$base_url."index.php';</script>";
    exit;
}

$pdo = getKoneksi('admin');
$pesan = '';

// Proses Hapus Tarif
if (isset($_GET['hapus'])) {
    try {
        $pdo->prepare("DELETE FROM tb_tarif WHERE id_tarif = ?")->execute([$_GET['hapus']]);
        $pesan = "Tarif berhasil dihapus!";
        echo "<meta http-equiv='refresh' content='1;url=tarif.php'>";
    } catch (Exception $e) {
        $pesan = "Gagal menghapus tarif. Data mungkin terpakai di transaksi.";
    }
}

$tarifs = $pdo->query("SELECT * FROM tb_tarif ORDER BY id_tarif ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);">Manajemen Tarif Parkir</h3>
    <a href="tarif_form.php" class="btn btn-aksen"><i class="bi bi-plus-circle-fill"></i> Tambah Tarif Baru</a>
</div>

<?php if ($pesan): ?>
    <div class="alert alert-info alert-dismissible fade show"><i class="bi bi-info-circle-fill me-2"></i> <?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card card-elegan p-4">
    <table id="tabelTarif" class="table table-hover align-middle w-100">
        <thead>
            <tr>
                <th>No</th>
                <th>Jenis Kendaraan</th>
                <th>Tarif Per Jam</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tarifs as $index => $tarif): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td class="fw-semibold text-capitalize"><?= htmlspecialchars($tarif['jenis_kendaraan']) ?></td>
                    <td>Rp <?= number_format($tarif['tarif_per_jam'], 0, ',', '.') ?></td>
                    <td class="text-end">
                        <a href="tarif_form.php?id=<?= $tarif['id_tarif'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="bi bi-pencil-square"></i> Edit</a>
                        <a href="tarif.php?hapus=<?= $tarif['id_tarif'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Yakin hapus tarif ini?')"><i class="bi bi-trash"></i> Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#tabelTarif').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i> Export Excel',
                className: 'btn btn-success btn-sm me-2',
                title: 'Data Tarif ParkirApp',
                exportOptions: { columns: [0, 1, 2] } // Hanya export kolom No, Jenis, Tarif
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-earmark-pdf"></i> Export PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Data Tarif ParkirApp',
                exportOptions: { columns: [0, 1, 2] }
            }
        ],
        "language": { 
            "search": "Cari:", 
            "lengthMenu": "Tampilkan _MENU_ data", 
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data", 
            "zeroRecords": "Data tidak ditemukan", 
            "paginate": { "first": "Awal", "last": "Akhir", "next": "Selanjutnya", "previous": "Sebelumnya" } 
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>