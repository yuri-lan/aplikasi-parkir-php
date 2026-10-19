<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak! Halaman ini khusus Admin.'); window.location='".$base_url."index.php';</script>";
    exit;
}

$pdo = getKoneksi('admin');
$pesan = '';
$tipe_pesan = '';

// Proses Hapus User
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    if ($id_hapus == $_SESSION['id_user']) {
        $pesan = "Anda tidak bisa menghapus akun Anda sendiri!";
        $tipe_pesan = "danger";
    } else {
        try {
            $stmt_hapus = $pdo->prepare("DELETE FROM tb_user WHERE id_user = ?");
            $stmt_hapus->execute([$id_hapus]);
            $pesan = "Data user berhasil dihapus!";
            $tipe_pesan = "success";
            echo "<meta http-equiv='refresh' content='1;url=user.php'>";
        } catch (Exception $e) {
            $pesan = "Gagal menghapus user. Kemungkinan user memiliki relasi data.";
            $tipe_pesan = "danger";
        }
    }
}

$stmt = $pdo->query("SELECT * FROM tb_user ORDER BY id_user ASC");
$users = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);">Manajemen Data User</h3>
    <a href="user_form.php" class="btn btn-aksen"><i class="bi bi-person-plus-fill"></i> Tambah User Baru</a>
</div>

<?php if ($pesan): ?>
    <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i> <?= htmlspecialchars($pesan) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card card-elegan p-4">
    <table id="tabelUser" class="table table-hover align-middle w-100">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Lengkap</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $index => $user): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                    <td><span class="badge bg-light text-dark border"><i class="bi bi-person"></i> <?= htmlspecialchars($user['username']) ?></span></td>
                    <td>
                        <?php if ($user['role'] === 'admin'): ?>
                            <span class="badge bg-primary">Admin</span>
                        <?php elseif ($user['role'] === 'petugas'): ?>
                            <span class="badge bg-success">Petugas</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Owner</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($user['status_aktif'] == 1): ?>
                            <span class="badge bg-success-subtle text-success border border-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <a href="user_form.php?id=<?= $user['id_user'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <?php if ($user['id_user'] != $_SESSION['id_user']): ?>
                            <a href="user.php?hapus=<?= $user['id_user'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Yakin ingin menghapus user <?= htmlspecialchars($user['nama_lengkap']) ?>?')">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#tabelUser').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i> Export Excel',
                className: 'btn btn-success btn-sm me-2',
                title: 'Data User ParkirApp',
                exportOptions: { columns: [0, 1, 2, 3, 4] } // Kolom Aksi (5) tidak di-export
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-earmark-pdf"></i> Export PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Data User ParkirApp',
                exportOptions: { columns: [0, 1, 2, 3, 4] }
            }
        ],
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
            "zeroRecords": "Data tidak ditemukan",
            "paginate": {
                "first": "Awal",
                "last": "Akhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>