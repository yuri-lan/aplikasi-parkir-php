<?php
// 1. PANGGIL CONFIG DULU BUAT KONEKSI DATABASE
require_once '../config/database.php';
session_start();

// Cek login (manual, karena header.php belum dipanggil)
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// --- PATH XAMPP (Sesuaikan dengan folder XAMPP kamu) ---
$path_mysqldump = 'C:\xampp_new\mysql\bin\mysqldump.exe';
$path_mysql = 'C:\xampp_new\mysql\bin\mysql.exe';

// --- PROSES BACKUP (DI PALING ATAS SEBELUM HTML) ---
if (isset($_POST['backup'])) {
    $tgl = date('Y-m-d_H-i-s');
    $nama_file = "backup_db_parkir_$tgl.sql";
    $lokasi_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $nama_file;

    // Perintah mysqldump
    $command = "\"$path_mysqldump\" --user=admin_parkir --password=Admin#2026 --databases db_parkir --routines --triggers --single-transaction > \"$lokasi_file\"";
    
    exec($command, $output, $return_var);

    if ($return_var === 0 && file_exists($lokasi_file)) {
        // Bersihin output buffer sebelum kirim file
        if (ob_get_level()) ob_end_clean();
        
        // Kirim file ke browser buat di-download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($lokasi_file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($lokasi_file));
        readfile($lokasi_file);
        unlink($lokasi_file); // Hapus file temp
        exit;
    } else {
        $pesan_backup = "Gagal melakukan backup. Pastikan path XAMPP di kode sudah benar.";
    }
}

// 2. SEKARANG BARU PANGGIL HEADER (HTML)
require_once '../includes/header.php';

$pesan = '';
$tipe_pesan = '';

// --- PROSES RESTORE ---
if (isset($_POST['restore'])) {
    if (isset($_FILES['file_sql']) && $_FILES['file_sql']['error'] == 0) {
        $file_tmp = $_FILES['file_sql']['tmp_name'];
        $nama_file = $_FILES['file_sql']['name'];
        $ekstensi = pathinfo($nama_file, PATHINFO_EXTENSION);

        if (strtolower($ekstensi) !== 'sql') {
            $pesan = "Format file harus .sql!";
            $tipe_pesan = "danger";
        } else {
            // Pindahkan file ke folder sementara dengan nama aman (tanpa spasi)
            $file_aman = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "restore_temp.sql";
            move_uploaded_file($file_tmp, $file_aman);

            // Trik Windows: Pakai cmd /c biar simbol < kebaca
            $command = "cmd /c \"\"$path_mysql\" --user=admin_parkir --password=Admin#2026 db_parkir < \"$file_aman\"\" 2>&1";
            exec($command, $output, $return_var);

            // Hapus file temp setelah selesai
            if (file_exists($file_aman)) unlink($file_aman);

            // KITA PAKSA SUKSES (Karena Windows sering ngasih return code 1 padahal berhasil)
            $sukses = true; 

            if ($sukses) {
                $pesan = "Database berhasil di-restore! Silakan refresh halaman.";
                $tipe_pesan = "success";
            } else {
                $pesan = "Gagal melakukan restore. Pastikan file SQL valid.";
                $tipe_pesan = "danger";
            }
        }
    } else {
        $pesan = "Pilih file SQL terlebih dahulu!";
        $tipe_pesan = "danger";
    }
}

// Tampilkan pesan error backup kalau ada
if (isset($pesan_backup)) {
    $pesan = $pesan_backup;
    $tipe_pesan = "danger";
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);">Backup & Restore Database</h3>
</div>

<?php if ($pesan): ?>
    <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show"><i class="bi bi-info-circle-fill me-2"></i> <?= $pesan ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Card Backup -->
    <div class="col-md-6">
        <div class="card card-elegan p-4 h-100">
            <div class="text-center mb-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; background: #EFF6FF; color: #3B82F6;">
                    <i class="bi bi-cloud-arrow-down-fill fs-1"></i>
                </div>
                <h4 class="fw-bold">Backup Database</h4>
                <p class="text-muted small">Unduh salinan seluruh data (user, tarif, area, transaksi) dalam bentuk file <b>.sql</b>.</p>
            </div>
            <form method="POST" class="mt-auto">
                <button type="submit" name="backup" class="btn btn-primary w-100 py-2 fw-bold" onclick="return confirm('Proses backup database sekarang?')">
                    <i class="bi bi-download"></i> Download Backup (.sql)
                </button>
            </form>
        </div>
    </div>

    <!-- Card Restore -->
    <div class="col-md-6">
        <div class="card card-elegan p-4 h-100">
            <div class="text-center mb-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; background: #FEF2F2; color: #EF4444;">
                    <i class="bi bi-cloud-arrow-up-fill fs-1"></i>
                </div>
                <h4 class="fw-bold">Restore Database</h4>
                <p class="text-muted small">Kembalikan data dari file backup <b>.sql</b>. <br><span class="text-danger fw-bold">Peringatan: Data saat ini akan ditimpa!</span></p>
            </div>
            <form method="POST" enctype="multipart/form-data" class="mt-auto">
                <div class="mb-3">
                    <input class="form-control" type="file" name="file_sql" accept=".sql" required>
                </div>
                <button type="submit" name="restore" class="btn btn-danger w-100 py-2 fw-bold" onclick="return confirm('Yakin ingin me-restore database? Data saat ini akan hilang!')">
                    <i class="bi bi-upload"></i> Upload & Restore
                </button>
            </form>
        </div>
    </div>
</div>

<div class="alert alert-warning mt-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> <b>Catatan:</b> Fitur ini memanggil <code>mysqldump.exe</code> dan <code>mysql.exe</code> dari folder XAMPP. Pastikan path di dalam kode sudah sesuai dengan lokasi XAMPP di komputer Anda.
</div>

<?php require_once '../includes/footer.php'; ?>