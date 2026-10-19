<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] === 'owner') { 
    echo "<script>alert('Akses ditolak!'); window.location='".$base_url."index.php';</script>";
    exit; 
}

$pdo = getKoneksi($_SESSION['role']);
$role = $_SESSION['role'];
$pesan = '';

// Proses Admin Approve/Edit Data
if ($role === 'admin' && isset($_POST['approve'])) {
    $id_laporan = $_POST['id_laporan'];
    $id_kendaraan = $_POST['id_kendaraan'];
    $plat = strtoupper(trim($_POST['plat_nomor']));
    $jenis = $_POST['jenis_kendaraan'];
    $warna = trim($_POST['warna']);
    $pemilik = trim($_POST['pemilik']);

    try {
        // Update kendaraan
        $pdo->prepare("UPDATE tb_kendaraan SET plat_nomor=?, jenis_kendaraan=?, warna=?, pemilik=? WHERE id_kendaraan=?")->execute([$plat, $jenis, $warna, $pemilik, $id_kendaraan]);
        // Update status laporan
        $pdo->prepare("UPDATE tb_laporan SET status='selesai' WHERE id_laporan=?")->execute([$id_laporan]);
        $pesan = "Data kendaraan berhasil diperbarui dan laporan diselesaikan!";
    } catch (Exception $e) {
        $pesan = "Gagal update: " . $e->getMessage();
    }
}

// Ambil data laporan (Beda query buat Admin & Petugas biar gak error Access Denied)
if ($role === 'admin') {
    $sql = "SELECT l.*, k.plat_nomor AS plat_asli, k.jenis_kendaraan AS jenis_asli, u.nama_lengkap AS nama_petugas 
            FROM tb_laporan l 
            JOIN tb_kendaraan k ON l.id_kendaraan = k.id_kendaraan 
            JOIN tb_user u ON l.id_petugas = u.id_user 
            ORDER BY l.waktu_lapor DESC";
} else {
    $sql = "SELECT l.*, k.plat_nomor AS plat_asli, k.jenis_kendaraan AS jenis_asli, 'Petugas' AS nama_petugas 
            FROM tb_laporan l 
            JOIN tb_kendaraan k ON l.id_kendaraan = k.id_kendaraan 
            ORDER BY l.waktu_lapor DESC";
}
$laporans = $pdo->query($sql)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);">Pusat Laporan & Koreksi Data</h3>
</div>

<?php if ($pesan): ?><div class="alert alert-success"><?= $pesan ?></div><?php endif; ?>

<div class="row g-4">
    <?php if (empty($laporans)): ?>
        <div class="col-12"><div class="alert alert-info"><i class="bi bi-info-circle-fill me-2"></i> Belum ada laporan masuk.</div></div>
    <?php endif; ?>

    <?php foreach ($laporans as $l): ?>
    <div class="col-md-6">
        <div class="card card-elegan p-4 h-100 border-start border-4" style="border-left-color: <?= $l['status'] == 'pending' ? '#f59e0b' : '#10b981' ?> !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge <?= $l['status'] == 'pending' ? 'bg-warning text-dark' : 'bg-success' ?>">
                    <?= $l['status'] == 'pending' ? 'Menunggu Respon' : 'Selesai' ?>
                </span>
                <small class="text-muted"><?= date('d M Y H:i', strtotime($l['waktu_lapor'])) ?></small>
            </div>
            
            <div class="mb-2"><i class="bi bi-person-fill"></i> Dilaporkan oleh: <b><?= htmlspecialchars($l['nama_petugas']) ?></b></div>
            
            <div class="p-3 bg-light rounded-3 mb-3">
                <div class="text-danger small mb-1 fw-bold">Data Lama:</div>
                <div class="small text-muted text-decoration-line-through mb-2"><?= htmlspecialchars($l['data_lama']) ?></div>
                
                <div class="text-success small mb-1 fw-bold">Data Baru (Yang Diajukan):</div>
                <div class="small text-dark"><?= htmlspecialchars($l['data_baru']) ?></div>
            </div>

            <div class="mb-3 small">
                <i class="bi bi-chat-left-text"></i> <b>Alasan/Pesan:</b> <br>
                <em>"<?= htmlspecialchars($l['pesan_tambahan']) ?>"</em>
            </div>

            <?php if ($role === 'admin' && $l['status'] === 'pending'): ?>
                <form method="POST" class="mt-auto border-top pt-3">
                    <input type="hidden" name="id_laporan" value="<?= $l['id_laporan'] ?>">
                    <input type="hidden" name="id_kendaraan" value="<?= $l['id_kendaraan'] ?>">
                    
                    <p class="small text-muted mb-2">Terapkan data baru ini?</p>
                    <?php 
                        // Parsing data baru
                        $parse = explode(', ', $l['data_baru']);
                    ?>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><input type="text" name="plat_nomor" class="form-control form-control-sm" value="<?= str_replace('Plat: ', '', $parse[0]) ?>" required></div>
                        <div class="col-6"><input type="text" name="jenis_kendaraan" class="form-control form-control-sm" value="<?= str_replace('Jenis: ', '', $parse[1]) ?>" required></div>
                        <div class="col-6"><input type="text" name="warna" class="form-control form-control-sm" value="<?= str_replace('Warna: ', '', $parse[2]) ?>" required></div>
                        <div class="col-6"><input type="text" name="pemilik" class="form-control form-control-sm" value="<?= str_replace('Pemilik: ', '', $parse[3]) ?>" required></div>
                    </div>
                    <button type="submit" name="approve" class="btn btn-sm btn-aksen w-100"><i class="bi bi-check-circle-fill"></i> Terapkan & Selesaikan</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once '../includes/footer.php'; ?>