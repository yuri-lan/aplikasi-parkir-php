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

// Proses Hapus Kendaraan (Cuma Admin yang bisa)
if (isset($_GET['hapus']) && $_SESSION['role'] === 'admin') {
    try {
        $pdo->prepare("DELETE FROM tb_kendaraan WHERE id_kendaraan = ?")->execute([$_GET['hapus']]);
        $pesan = "Kendaraan berhasil dihapus!";
        echo "<meta http-equiv='refresh' content='1;url=kendaraan.php'>";
    } catch (Exception $e) {
        $pesan = "Gagal menghapus kendaraan. Kendaraan mungkin memiliki riwayat transaksi.";
    }
}

// Ambil data kendaraan + STATUS PARKIR (Beda query buat Admin & Petugas)
if ($_SESSION['role'] === 'admin') {
    $sql = "SELECT k.*, u.nama_lengkap,
            (SELECT COUNT(*) FROM tb_transaksi t WHERE t.id_kendaraan = k.id_kendaraan AND t.status = 'masuk') AS sedang_parkir
            FROM tb_kendaraan k 
            JOIN tb_user u ON k.id_user = u.id_user 
            ORDER BY k.id_kendaraan DESC";
} else {
    $sql = "SELECT k.*,
            (SELECT COUNT(*) FROM tb_transaksi t WHERE t.id_kendaraan = k.id_kendaraan AND t.status = 'masuk') AS sedang_parkir
            FROM tb_kendaraan k 
            ORDER BY k.id_kendaraan DESC";
}
$kendaraans = $pdo->query($sql)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);">Manajemen Data Kendaraan</h3>
    <a href="kendaraan_form.php" class="btn btn-aksen"><i class="bi bi-plus-circle-fill"></i> Tambah Kendaraan</a>
</div>

<?php if ($pesan): ?>
    <div class="alert alert-info alert-dismissible fade show"><i class="bi bi-info-circle-fill me-2"></i> <?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card card-elegan p-4">
    <table id="tabelKendaraan" class="table table-hover align-middle w-100">
        <thead>
            <tr>
                <th>No</th>
                <th>Plat Nomor</th>
                <th>Jenis</th>
                <th>Warna</th>
                <th>Pemilik</th>
                <th>Status</th>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <th>Diinput Oleh</th>
                <?php endif; ?>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kendaraans as $index => $k): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td class="fw-bold fs-6"><?= htmlspecialchars($k['plat_nomor']) ?></td>
                    <td class="text-capitalize"><?= htmlspecialchars($k['jenis_kendaraan']) ?></td>
                    <td><?= htmlspecialchars($k['warna'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($k['pemilik'] ?? '-') ?></td>
                    
                    <td>
                        <?php if ($k['sedang_parkir'] > 0): ?>
                            <span class="badge bg-success" style="font-size: 0.9rem; padding: 8px 15px; border-radius: 8px;">
                                <i class="bi bi-p-square-fill me-1"></i> Sedang Parkir
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary" style="font-size: 0.9rem; padding: 8px 15px; border-radius: 8px;">
                                <i class="bi bi-box-arrow-right me-1"></i> Di Luar
                            </span>
                        <?php endif; ?>
                    </td>

                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($k['nama_lengkap']) ?></span></td>
                    <?php endif; ?>
                    
                    <td class="text-end">
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <!-- Admin: Bisa Edit & Hapus -->
                            <a href="kendaraan_form.php?id=<?= $k['id_kendaraan'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="bi bi-pencil-square"></i> Edit</a>
                            <a href="kendaraan.php?hapus=<?= $k['id_kendaraan'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Yakin hapus kendaraan ini?')"><i class="bi bi-trash"></i> Hapus</a>
                        <?php else: ?>
                            <!-- Petugas: Cuma bisa Lapor -->
                            <a href="<?= $base_url ?>chat/form_lapor.php?id=<?= $k['id_kendaraan'] ?>" class="btn btn-sm btn-warning rounded-pill px-3 fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Lapor Kesalahan</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>