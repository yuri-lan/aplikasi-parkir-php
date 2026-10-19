<?php
// index.php (Dashboard Utama)
require_once 'includes/header.php';
require_once 'config/database.php';

$pdo = getKoneksi($_SESSION['role']);
$total_user = 0; $total_kendaraan = 0; $kendaraan_masuk = 0; $kapasitas_area = 0;
$chart_labels = []; $chart_data = [];
$list_area = []; $aktivitas_terbaru = [];

try {
    if ($_SESSION['role'] === 'admin') {
        $total_user = $pdo->query("SELECT COUNT(*) FROM tb_user")->fetchColumn();
        $total_kendaraan = $pdo->query("SELECT COUNT(*) FROM tb_kendaraan")->fetchColumn();
        $kendaraan_masuk = $pdo->query("SELECT COUNT(*) FROM tb_transaksi WHERE status = 'masuk'")->fetchColumn();
        $kapasitas_area = $pdo->query("SELECT SUM(kapasitas) FROM tb_area_parkir")->fetchColumn();
        
        $stmt = $pdo->query("SELECT DATE(waktu_keluar) as tgl, SUM(biaya_total) as total FROM tb_transaksi WHERE status='keluar' AND waktu_keluar >= DATE(NOW()) - INTERVAL 6 DAY GROUP BY DATE(waktu_keluar) ORDER BY tgl ASC");
        $list_area = $pdo->query("SELECT * FROM tb_area_parkir ORDER BY id_area ASC")->fetchAll();
        $aktivitas_terbaru = $pdo->query("SELECT l.*, u.nama_lengkap FROM tb_log_aktivitas l JOIN tb_user u ON l.id_user = u.id_user ORDER BY l.waktu_aktivitas DESC LIMIT 5")->fetchAll();
        
    } elseif ($_SESSION['role'] === 'petugas') {
        $total_kendaraan = $pdo->query("SELECT COUNT(*) FROM tb_kendaraan")->fetchColumn();
        $kendaraan_masuk = $pdo->query("SELECT COUNT(*) FROM tb_transaksi WHERE status = 'masuk'")->fetchColumn();
        $kapasitas_area = $pdo->query("SELECT SUM(kapasitas) FROM tb_area_parkir")->fetchColumn();
        $list_area = $pdo->query("SELECT * FROM tb_area_parkir ORDER BY id_area ASC")->fetchAll();
        
    } elseif ($_SESSION['role'] === 'owner') {
        // OWNER PAKAI VIEW (Sesuai Hak Akses DCL)
        $total_kendaraan = $pdo->query("SELECT total FROM v_total_kendaraan")->fetchColumn();
        $kendaraan_masuk = $pdo->query("SELECT COUNT(*) FROM v_rekap_transaksi WHERE status = 'masuk'")->fetchColumn();
        $stmt = $pdo->query("SELECT DATE(waktu_keluar) as tgl, SUM(biaya_total) as total FROM v_rekap_transaksi WHERE status='keluar' AND waktu_keluar >= DATE(NOW()) - INTERVAL 6 DAY GROUP BY DATE(waktu_keluar) ORDER BY tgl ASC");
        $list_area = $pdo->query("SELECT * FROM v_status_area ORDER BY id_area ASC")->fetchAll(); 
    }

    if (isset($stmt)) {
        while ($row = $stmt->fetch()) {
            $chart_labels[] = date('d M', strtotime($row['tgl']));
            $chart_data[] = $row['total'];
        }
    }
} catch (Exception $e) { }
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);">Dashboard <?= ucfirst($_SESSION['role']) ?></h3>
    <span class="text-muted small"><i class="bi bi-calendar3"></i> <?= date('d F Y') ?></span>
</div>

<!-- BARIS 1: Card Statistik -->
<div class="row g-3 g-md-4 mb-4">
    <?php if ($_SESSION['role'] === 'admin'): ?>
    <div class="col-6 col-md-3">
        <div class="card card-elegan p-3 p-md-4 h-100 border-start border-4" style="border-left-color: #8B5CF6 !important;">
            <div class="d-flex justify-content-between align-items-center mb-2 mb-md-3">
                <div class="rounded-3 p-2 p-md-3" style="background: #F5F3FF; color: #8B5CF6;"><i class="bi bi-people-fill fs-4 fs-md-3"></i></div>
                <h3 class="fw-bold mb-0"><?= $total_user ?></h3>
            </div>
            <h6 class="text-muted fw-semibold mb-0" style="font-size: 0.8rem;">Total Pengguna</h6>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-6 col-md-3">
        <div class="card card-elegan p-3 p-md-4 h-100 border-start border-4" style="border-left-color: #3B82F6 !important;">
            <div class="d-flex justify-content-between align-items-center mb-2 mb-md-3">
                <div class="rounded-3 p-2 p-md-3" style="background: #EFF6FF; color: #3B82F6;"><i class="bi bi-car-front-fill fs-4 fs-md-3"></i></div>
                <h3 class="fw-bold mb-0"><?= $total_kendaraan ?></h3>
            </div>
            <h6 class="text-muted fw-semibold mb-0" style="font-size: 0.8rem;">Kendaraan</h6>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card card-elegan p-3 p-md-4 h-100 border-start border-4" style="border-left-color: #10B981 !important;">
            <div class="d-flex justify-content-between align-items-center mb-2 mb-md-3">
                <div class="rounded-3 p-2 p-md-3" style="background: #ECFDF5; color: #10B981;"><i class="bi bi-p-square-fill fs-4 fs-md-3"></i></div>
                <h3 class="fw-bold mb-0"><?= $kendaraan_masuk ?></h3>
            </div>
            <h6 class="text-muted fw-semibold mb-0" style="font-size: 0.8rem;">Sedang Parkir</h6>
        </div>
    </div>

    <?php if ($_SESSION['role'] !== 'owner'): ?>
    <div class="col-6 col-md-3">
        <div class="card card-elegan p-3 p-md-4 h-100 border-start border-4" style="border-left-color: #F59E0B !important;">
            <div class="d-flex justify-content-between align-items-center mb-2 mb-md-3">
                <div class="rounded-3 p-2 p-md-3" style="background: #FFFBEB; color: #F59E0B;"><i class="bi bi-grid-3x3-gap-fill fs-4 fs-md-3"></i></div>
                <h3 class="fw-bold mb-0"><?= $kapasitas_area ?></h3>
            </div>
            <h6 class="text-muted fw-semibold mb-0" style="font-size: 0.8rem;">Total Kapasitas</h6>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- BARIS 2: Grafik (Kiri) & Area (Kanan) -->
<div class="row g-4 mb-4">
    <!-- GRAFIK (Cuma buat Admin & Owner) -->
    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'owner'): ?>
    <div class="col-12 <?= $_SESSION['role'] === 'admin' ? 'col-lg-8' : 'col-lg-12' ?>">
        <div class="card card-elegan p-3 p-md-4 h-100">
            <h5 class="fw-bold mb-3 mb-md-4" style="font-size: 1.1rem;">Grafik Pendapatan (7 Hari Terakhir)</h5>
            <div style="position: relative; height: 250px; width: 100%;">
                <canvas id="chartPendapatan"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- STATUS AREA (Buat semua role) -->
    <div class="col-12 <?= ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'owner') ? 'col-lg-4' : 'col-lg-12' ?>">
        <div class="card card-elegan p-3 p-md-4 h-100">
            <h5 class="fw-bold mb-3 mb-md-4" style="font-size: 1.1rem;"><i class="bi bi-geo-alt-fill me-1"></i> Status Area Parkir</h5>
            
            <div class="d-flex flex-column gap-3">
                <?php if(empty($list_area)): ?>
                    <div class="text-muted small">Data area tidak tersedia.</div>
                <?php else: ?>
                    <?php foreach ($list_area as $area): 
                        $sisa = $area['kapasitas'] - $area['terisi'];
                        $persen = ($area['kapasitas'] > 0) ? ($area['terisi'] / $area['kapasitas']) * 100 : 0;
                        $warna_bar = ($persen > 80) ? 'bg-danger' : (($persen > 50) ? 'bg-warning' : 'bg-success');
                    ?>
                    <div>
                        <div class="d-flex justify-content-between align-items-end mb-1">
                            <span class="fw-semibold text-dark-mode-target" style="font-size: 0.9rem;"><?= htmlspecialchars($area['nama_area']) ?></span>
                            <span class="fw-bold" style="font-size: 1.1rem; color: <?= ($sisa > 0) ? '#10b981' : '#ef4444' ?>;">
                                <?= $sisa > 0 ? "Sisa $sisa" : "PENUH" ?>
                            </span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 10px;">
                            <div class="progress-bar <?= $warna_bar ?>" role="progressbar" style="width: <?= $persen ?>%; border-radius: 10px;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- BARIS 3: Tabel Aktivitas Terbaru (Cuma buat Admin) -->
<?php if ($_SESSION['role'] === 'admin' && !empty($aktivitas_terbaru)): ?>
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card card-elegan p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0" style="font-size: 1.1rem;"><i class="bi bi-clock-history me-1"></i> Aktivitas Terbaru</h5>
                <a href="master/log.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aktivitas_terbaru as $log): ?>
                        <tr>
                            <td class="text-muted small"><?= date('d M Y, H:i', strtotime($log['waktu_aktivitas'])) ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($log['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($log['aktivitas']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- SCRIPT GRAFIK -->
<?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'owner'): ?>
<script src="<?= $base_url ?>assets/js/chart.js"></script>
<script>
    window.onload = function() {
        const canvas = document.getElementById('chartPendapatan');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            const chartLabels = <?= json_encode($chart_labels) ?>;
            const chartData = <?= json_encode($chart_data) ?>;
            const colorAksen = getComputedStyle(document.documentElement).getPropertyValue('--warna-aksen').trim();

            // Biar grafiknya ga aneh kalau kosong
            const finalLabels = chartLabels.length ? chartLabels : ['Belum ada data'];
            const finalData = chartData.length ? chartData : [0];

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: finalLabels,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: finalData,
                        borderColor: colorAksen,
                        backgroundColor: colorAksen + '33',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: colorAksen,
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            ticks: { 
                                precision: 0, // Biar ga ada koma (Rp 0.5)
                                callback: function(value) { return 'Rp ' + value.toLocaleString('id-ID'); } 
                            } 
                        }
                    }
                }
            });
        }
    };
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>