<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] === 'owner') {
    echo "<script>alert('Akses ditolak!'); window.location='".$base_url."index.php';</script>";
    exit;
}

$pdo = getKoneksi($_SESSION['role']);
$pesan = ''; 
$tipe_pesan = '';
$struk = null; // <-- INI DIA VARIABEL YANG KETINGGALAN TADI

// PROSES TRANSAKSI KELUAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kode_input'])) {
    $kode_input = strtoupper(trim($_POST['kode_input']));
    $metode_bayar = $_POST['metode_bayar'];
    $uang_bayar = isset($_POST['uang_bayar']) ? (int)$_POST['uang_bayar'] : 0;
    $waktu_keluar = date('Y-m-d H:i:s');
    $id_user = $_SESSION['id_user'];

    try {
        $sql_cari = "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, a.nama_area, tar.tarif_per_jam 
                     FROM tb_transaksi t
                     JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                     JOIN tb_area_parkir a ON t.id_area = a.id_area
                     JOIN tb_tarif tar ON t.id_tarif = tar.id_tarif
                     WHERE (t.kode_tiket = ? OR k.plat_nomor = ?) AND t.status = 'masuk'";
        $stmt = $pdo->prepare($sql_cari);
        $stmt->execute([$kode_input, $kode_input]);
        $trx = $stmt->fetch();

        if ($trx) {
            $masuk = strtotime($trx['waktu_masuk']);
            $keluar = strtotime($waktu_keluar);
            $durasi_jam = ceil(($keluar - $masuk) / 3600);
            if ($durasi_jam < 1) { $durasi_jam = 1; }
            $biaya_total = $durasi_jam * $trx['tarif_per_jam'];

            if ($metode_bayar === 'cash' && $uang_bayar < $biaya_total) {
                $pesan = "Uang yang dibayarkan kurang dari total tagihan!";
                $tipe_pesan = "danger";
            } else {
                $kembalian = ($metode_bayar === 'cash') ? ($uang_bayar - $biaya_total) : 0;

                $sql_update = "UPDATE tb_transaksi SET waktu_keluar=?, durasi_jam=?, biaya_total=?, status='keluar', metode_bayar=? WHERE id_parkir=?";
                $pdo->prepare($sql_update)->execute([$waktu_keluar, $durasi_jam, $biaya_total, $metode_bayar, $trx['id_parkir']]);

                $pdo->prepare("UPDATE tb_area_parkir SET terisi = terisi - 1 WHERE id_area = ?")->execute([$trx['id_area']]);

                $log = $pdo->prepare("INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES (?, ?)");
                $log->execute([$id_user, "Proses keluar {$trx['plat_nomor']} | Bayar: $metode_bayar | Total: Rp $biaya_total"]);

                // Siapin Data Struk
                $struk = [
                    'kode' => $trx['kode_tiket'],
                    'plat' => $trx['plat_nomor'],
                    'masuk' => $trx['waktu_masuk'],
                    'keluar' => $waktu_keluar,
                    'durasi' => $durasi_jam,
                    'tarif' => $trx['tarif_per_jam'],
                    'total' => $biaya_total,
                    'metode' => $metode_bayar,
                    'bayar' => $uang_bayar,
                    'kembali' => $kembalian,
                    'area' => $trx['nama_area']
                ];
                $pesan = "Kendaraan berhasil diproses keluar!";
                $tipe_pesan = "success";
            }
        } else {
            $pesan = "Tiket / Plat nomor <b>$kode_input</b> tidak ditemukan atau sudah keluar!";
            $tipe_pesan = "danger";
        }
    } catch (Exception $e) {
        $pesan = "Error: " . $e->getMessage();
        $tipe_pesan = "danger";
    }
}

if ($_SESSION['role'] === 'admin') {
    $sql_trx = "SELECT t.*, k.plat_nomor, a.nama_area, tar.tarif_per_jam FROM tb_transaksi t JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan JOIN tb_area_parkir a ON t.id_area = a.id_area JOIN tb_tarif tar ON t.id_tarif = tar.id_tarif WHERE t.status = 'masuk' ORDER BY t.id_parkir DESC";
} else {
    $sql_trx = "SELECT t.*, k.plat_nomor, a.nama_area, tar.tarif_per_jam FROM tb_transaksi t JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan JOIN tb_area_parkir a ON t.id_area = a.id_area JOIN tb_tarif tar ON t.id_tarif = tar.id_tarif WHERE t.status = 'masuk' ORDER BY t.id_parkir DESC";
}
$transaksi_aktif = $pdo->query($sql_trx)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);">Transaksi Parkir Keluar</h3>
</div>

<?php if ($pesan && !$struk): ?>
    <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show"><i class="bi bi-info-circle-fill me-2"></i> <?= $pesan ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card card-elegan p-4">
            <h5 class="fw-bold mb-3">Cari & Bayar Parkir</h5>
            <form method="POST" id="formBayar">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Scan / Input Kode Tiket / Plat</label>
                    <input type="text" name="kode_input" class="form-control form-control-lg text-uppercase fw-bold text-center" placeholder="TKT-XXXX atau B 1234 XYZ" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Metode Pembayaran</label>
                    <select name="metode_bayar" id="metode_bayar" class="form-select" onchange="togglePembayaran()" required>
                        <option value="cash">Cash (Tunai)</option>
                        <option value="qris">QRIS (Scan Barcode)</option>
                    </select>
                </div>

                <div id="form_cash" class="mb-3">
                    <label class="form-label fw-semibold">Uang Diterima (Rp)</label>
                    <input type="number" name="uang_bayar" id="uang_bayar" class="form-control" placeholder="Contoh: 10000">
                </div>

                <div id="form_qris" class="mb-3 d-none text-center p-3 bg-light rounded-3 border">
                    <p class="small text-muted mb-2">Silakan scan QRIS di bawah:</p>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=ParkirApp_QRIS_Payment" alt="QRIS" class="img-fluid rounded mb-2">
                    <div class="fw-bold text-primary">ParkirApp Official</div>
                </div>

                <button type="submit" class="btn btn-warning w-100 py-2 fw-bold text-dark"><i class="bi bi-wallet2"></i> Proses & Bayar</button>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card card-elegan p-4">
            <h5 class="fw-bold mb-3">Kendaraan Sedang Parkir</h5>
            <div class="table-responsive">
                <table id="tabelKeluar" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>Kode Tiket</th>
                            <th>Plat Nomor</th>
                            <th>Area</th>
                            <th>Waktu Masuk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transaksi_aktif as $trx): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= $trx['kode_tiket'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($trx['plat_nomor']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($trx['nama_area']) ?></span></td>
                                <td><?= date('d-m H:i', strtotime($trx['waktu_masuk'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function togglePembayaran() {
    const metode = document.getElementById('metode_bayar').value;
    const formCash = document.getElementById('form_cash');
    const formQris = document.getElementById('form_qris');
    const inputUang = document.getElementById('uang_bayar');

    if (metode === 'qris') {
        formCash.classList.add('d-none');
        formQris.classList.remove('d-none');
        inputUang.removeAttribute('required');
        inputUang.value = ''; 
    } else {
        formCash.classList.remove('d-none');
        formQris.classList.add('d-none');
        inputUang.setAttribute('required', 'required');
    }
}
togglePembayaran();
</script>

<!-- MODAL STRUK KELUAR (Bisa Di-print) -->
<?php if ($struk): ?>
<div class="modal fade show" id="modalStruk" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                </div>
                <h4 class="fw-bold mb-1">Pembayaran Berhasil</h4>
                <p class="text-muted small mb-4">Struk Parkir Digital</p>

                <div id="area-cetak" class="text-start p-3 rounded-3 mb-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                    <div class="text-center mb-3">
                        <h5 class="fw-bold mb-0">PARKIR APP</h5>
                        <div class="small text-muted">Jl. Raya Parkir No. 1</div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Kode Tiket</span>
                        <span class="fw-bold"><?= $struk['kode'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Plat Nomor</span>
                        <span class="fw-bold fs-5"><?= $struk['plat'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span class="text-muted">Masuk</span>
                        <span><?= date('d-m-Y H:i', strtotime($struk['masuk'])) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Keluar</span>
                        <span><?= date('d-m-Y H:i', strtotime($struk['keluar'])) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Durasi</span>
                        <span><?= $struk['durasi'] ?> Jam</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Metode Bayar</span>
                        <span class="fw-bold text-uppercase"><?= $struk['metode'] ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-bold text-muted">Total Biaya</span>
                        <span class="fw-bold fs-5 text-dark">Rp <?= number_format($struk['total'], 0, ',', '.') ?></span>
                    </div>
                    <?php if ($struk['metode'] === 'cash'): ?>
                    <div class="d-flex justify-content-between mb-1 small text-muted">
                        <span>Uang Dibayar</span>
                        <span>Rp <?= number_format($struk['bayar'], 0, ',', '.') ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 fw-bold text-success">
                        <span>Kembalian</span>
                        <span>Rp <?= number_format($struk['kembali'], 0, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <div class="text-center small text-muted mt-3">
                        Terima kasih telah menggunakan layanan kami.<br>
                        Barang bawaan Anda adalah tanggung jawab Anda.
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success w-50 py-2 fw-bold" onclick="cetakStruk()">
                        <i class="bi bi-printer"></i> Cetak
                    </button>
                    <button type="button" class="btn btn-primary w-50 py-2 fw-bold" onclick="window.location.href='keluar.php'">
                        <i class="bi bi-arrow-clockwise"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.body.style.overflow = 'hidden';

    // FUNGSI BUAT NGE-PRINT STRUK
    function cetakStruk() {
        var isiStruk = document.getElementById('area-cetak').innerHTML;
        var jendelaPrint = window.open('', '', 'width=400,height=600');
        
        jendelaPrint.document.write(`
            <html>
            <head>
                <title>Cetak Struk Parkir</title>
                <style>
                    body { font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; margin: 0; padding: 10px; }
                    hr { border-top: 1px dashed #000; margin: 5px 0; }
                    .text-center { text-align: center; }
                    .fw-bold { font-weight: bold; }
                    .d-flex { display: flex; justify-content: space-between; margin-bottom: 3px; }
                    .fs-5 { font-size: 16px; }
                    .small { font-size: 11px; }
                    .text-muted { color: #555; }
                    h5 { margin: 0 0 5px 0; font-size: 16px; }
                </style>
            </head>
            <body>
                ${isiStruk}
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(function() { window.close(); }, 500);
                    }
                <\/script>
            </body>
            </html>
        `);
        jendelaPrint.document.close();
    }
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>