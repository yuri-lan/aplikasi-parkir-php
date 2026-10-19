<?php
// includes/sidebar.php
/** @var string $base_url */
$role = $_SESSION['role'];

function isActive($page) {
    return basename($_SERVER['PHP_SELF']) == $page ? 'active' : '';
}
?>

<h5>Menu <?= ucfirst($role) ?></h5>

<?php if ($role === 'admin'): ?>
    <a href="<?= $base_url ?>index.php" class="<?= isActive('index.php') ?>"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
    <a href="<?= $base_url ?>master/user.php" class="<?= isActive('user.php') ?>"><i class="bi bi-people-fill"></i> Data User</a>
    <a href="<?= $base_url ?>master/tarif.php" class="<?= isActive('tarif.php') ?>"><i class="bi bi-wallet2"></i> Data Tarif</a>
    <a href="<?= $base_url ?>master/area.php" class="<?= isActive('area.php') ?>"><i class="bi bi-geo-alt-fill"></i> Area Parkir</a>
    <a href="<?= $base_url ?>master/kendaraan.php" class="<?= isActive('kendaraan.php') ?>"><i class="bi bi-car-front-fill"></i> Data Kendaraan</a>
    <a href="<?= $base_url ?>transaksi/masuk.php" class="<?= isActive('masuk.php') ?>"><i class="bi bi-box-arrow-in-right"></i> Parkir Masuk</a>
    <a href="<?= $base_url ?>transaksi/keluar.php" class="<?= isActive('keluar.php') ?>"><i class="bi bi-box-arrow-right"></i> Parkir Keluar</a>
    <a href="<?= $base_url ?>laporan/laporan.php" class="<?= isActive('laporan.php') ?>"><i class="bi bi-file-earmark-bar-graph-fill"></i> Laporan</a>
    <a href="<?= $base_url ?>master/log.php" class="<?= isActive('log.php') ?>"><i class="bi bi-clock-history"></i> Log Aktivitas</a>
    <a href="<?= $base_url ?>chat/chat.php" class="<?= isActive('chat.php') ?>"><i class="bi bi-chat-dots-fill"></i> Chat / Laporan</a>

<?php elseif ($role === 'petugas'): ?>
    <a href="<?= $base_url ?>index.php" class="<?= isActive('index.php') ?>"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
    <a href="<?= $base_url ?>master/kendaraan.php" class="<?= isActive('kendaraan.php') ?>"><i class="bi bi-car-front-fill"></i> Data Kendaraan</a>
    <a href="<?= $base_url ?>transaksi/masuk.php" class="<?= isActive('masuk.php') ?>"><i class="bi bi-box-arrow-in-right"></i> Parkir Masuk</a>
    <a href="<?= $base_url ?>transaksi/keluar.php" class="<?= isActive('keluar.php') ?>"><i class="bi bi-box-arrow-right"></i> Parkir Keluar</a>
    <a href="<?= $base_url ?>chat/chat.php" class="<?= isActive('chat.php') ?>"><i class="bi bi-chat-dots-fill"></i> Chat / Laporan</a>

<?php elseif ($role === 'owner'): ?>
    <a href="<?= $base_url ?>index.php" class="<?= isActive('index.php') ?>"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
    <a href="<?= $base_url ?>laporan/laporan.php" class="<?= isActive('laporan.php') ?>"><i class="bi bi-file-earmark-bar-graph-fill"></i> Laporan</a>
<?php endif; ?>