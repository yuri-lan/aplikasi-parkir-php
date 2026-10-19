<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak! Halaman ini khusus Admin.'); window.location='".$base_url."index.php';</script>";
    exit;
}

$pdo = getKoneksi('admin');
$is_edit = false;
$id_user = '';
$nama_lengkap = '';
$username = '';
$role = 'petugas';
$status_aktif = 1;
$pesan = '';
$tipe_pesan = '';

if (isset($_GET['id'])) {
    $is_edit = true;
    $id_user = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM tb_user WHERE id_user = ?");
    $stmt->execute([$id_user]);
    $data = $stmt->fetch();

    if ($data) {
        $nama_lengkap = $data['nama_lengkap'];
        $username     = $data['username'];
        $role         = $data['role'];
        $status_aktif = $data['status_aktif'];
    } else {
        echo "<script>alert('Data user tidak ditemukan!'); window.location='user.php';</script>";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username     = trim($_POST['username']);
    $role         = $_POST['role'];
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;
    $password     = $_POST['password'];

    if (empty($nama_lengkap) || empty($username)) {
        $pesan = "Nama Lengkap dan Username wajib diisi!";
        $tipe_pesan = "danger";
    } else {
        try {
            if ($is_edit) {
                if (!empty($password)) {
                    $password_hash = hash('sha256', $password);
                    $sql = "UPDATE tb_user SET nama_lengkap=?, username=?, password=?, role=?, status_aktif=? WHERE id_user=?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nama_lengkap, $username, $password_hash, $role, $status_aktif, $id_user]);
                } else {
                    $sql = "UPDATE tb_user SET nama_lengkap=?, username=?, role=?, status_aktif=? WHERE id_user=?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nama_lengkap, $username, $role, $status_aktif, $id_user]);
                }
                $pesan = "Data user berhasil diperbarui!";
                $tipe_pesan = "success";
                echo "<meta http-equiv='refresh' content='1;url=user.php'>";
            } else {
                if (empty($password)) {
                    $pesan = "Password wajib diisi untuk user baru!";
                    $tipe_pesan = "danger";
                } else {
                    $password_hash = hash('sha256', $password);
                    $sql = "INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nama_lengkap, $username, $password_hash, $role, $status_aktif]);
                    $pesan = "User baru berhasil ditambahkan!";
                    $tipe_pesan = "success";
                    echo "<meta http-equiv='refresh' content='1;url=user.php'>";
                }
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $pesan = "Username '<b>$username</b>' sudah digunakan. Silakan pilih username lain.";
            } else {
                $pesan = "Terjadi kesalahan: " . $e->getMessage();
            }
            $tipe_pesan = "danger";
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color: var(--warna-teks-aksen);">
        <?= $is_edit ? 'Edit Data User' : 'Tambah User Baru' ?>
    </h3>
    <a href="user.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<?php if ($pesan): ?>
    <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i> <?= $pesan ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card card-elegan p-4 p-md-5" style="max-width: 800px;">
    <form method="POST" action="">
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($nama_lengkap) ?>" required placeholder="Contoh: Budi Santoso">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" required placeholder="Contoh: budi123">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Role / Hak Akses <span class="text-danger">*</span></label>
                <select name="role" class="form-select" required>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="petugas" <?= $role === 'petugas' ? 'selected' : '' ?>>Petugas</option>
                    <option value="owner" <?= $role === 'owner' ? 'selected' : '' ?>>Owner</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Password <?= $is_edit ? '(Kosongkan jika tidak diubah)' : '<span class="text-danger">*</span>' ?></label>
                <input type="password" name="password" class="form-control" <?= $is_edit ? '' : 'required' ?> placeholder="Masukkan password">
            </div>

            <div class="col-md-12 mt-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="status_aktif" id="status_aktif" value="1" <?= $status_aktif == 1 ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold" for="status_aktif">Akun Aktif (Bisa login)</label>
                </div>
            </div>
        </div>

        <hr class="my-4">
        <div class="d-flex justify-content-end gap-2">
            <button type="reset" class="btn btn-light border px-4">Reset</button>
            <button type="submit" class="btn btn-aksen px-4"><i class="bi bi-save"></i> Simpan Data</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>