<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Pastikan cuma Admin yang bisa akses
if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak! Halaman ini khusus Admin.'); window.location='".$base_url."index.php';</script>";
    exit;
}

$pdo = getKoneksi('admin');

// Ambil data log + join ke tb_user buat dapet nama lengkap & role-nya
$sql = "SELECT l.*, u.nama_lengkap, u.role 
        FROM tb_log_aktivitas l
        JOIN tb_user u ON l.id_user = u.id_user
        ORDER BY l.waktu_aktivitas DESC";
$logs = $pdo->query($sql)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);">Log Aktivitas Sistem</h3>
</div>

<div class="card card-elegan p-4">
    <table id="tabelLog" class="table table-hover align-middle w-100">
        <thead>
            <tr>
                <th>No</th>
                <th>Waktu</th>
                <th>Nama Pengguna</th>
                <th>Role</th>
                <th>Aktivitas</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $index => $log): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= date('d-m-Y H:i:s', strtotime($log['waktu_aktivitas'])) ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($log['nama_lengkap']) ?></td>
                    <td>
                        <?php if ($log['role'] === 'admin'): ?>
                            <span class="badge bg-primary">Admin</span>
                        <?php elseif ($log['role'] === 'petugas'): ?>
                            <span class="badge bg-success">Petugas</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Owner</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($log['aktivitas']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>