<?php
// auth/login.php
session_start();

if (isset($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

require_once '../config/database.php';

$error = '';

// --- LOGIKA UNTUK NAMPILIN KETERSEDIAAN PARKIR ---
$info_area = [];
try {
    $pdo = getKoneksi('admin');
    $stmt = $pdo->query("SELECT nama_area, kapasitas, terisi FROM tb_area_parkir ORDER BY id_area ASC");
    $info_area = $stmt->fetchAll();
} catch (Exception $e) { }

// --- LOGIKA LOGIN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password_hash = hash('sha256', $password);

    if (!empty($username) && !empty($password)) {
        try {
            $pdo = getKoneksi('admin');
            $stmt = $pdo->prepare("SELECT * FROM tb_user WHERE username = ? AND password = ? AND status_aktif = 1");
            $stmt->execute([$username, $password_hash]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['id_user']      = $user['id_user'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role']         = $user['role'];
                $_SESSION['username']     = $user['username'];

                if ($user['role'] !== 'owner') {
                    try {
                        $pdoRole = getKoneksi($user['role']);
                        $log = $pdoRole->prepare("INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES (?, ?)");
                        $log->execute([$user['id_user'], 'Login ke sistem']);
                    } catch (Exception $e) { }
                }

                header("Location: ../index.php");
                exit;
            } else {
                $error = "Username atau password salah, atau akun tidak aktif!";
            }
        } catch (Exception $e) {
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    } else {
        $error = "Username dan password wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Parkir</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🅿️</text></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f0f4f8;
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 1150px;
            background: #ffffff;
            border-radius: 30px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            min-height: 680px;
        }

        .left-panel {
            width: 55%; 
            background: linear-gradient(135deg, #3730A3 0%, #4F46E5 100%);
            position: relative;
            padding: 60px 50px; 
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: -50px; left: -50px;
            width: 400px; height: 400px;
            background: rgba(255,255,255,0.1);
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            animation: morph 8s ease-in-out infinite;
            z-index: 1;
        }
        .left-panel::after {
            content: '';
            position: absolute;
            bottom: -100px; right: -50px;
            width: 500px; height: 500px;
            background: rgba(255,255,255,0.05);
            border-radius: 60% 40% 30% 70% / 50% 60% 40% 50%;
            animation: morph 10s ease-in-out infinite reverse;
            z-index: 1;
        }

        @keyframes morph {
            0% { border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; transform: rotate(0deg); }
            50% { border-radius: 60% 40% 30% 70% / 50% 60% 40% 50%; transform: rotate(180deg); }
            100% { border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; transform: rotate(360deg); }
        }

        .left-content {
            position: relative;
            z-index: 2;
        }

        .left-content h1 {
            font-weight: 800;
            font-size: 2.8rem;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        /* Card Info Area */
        .area-box {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 20px 25px;
            margin-bottom: 20px;
            transition: all 0.3s;
            text-align: center; /* Semua teks ke tengah */
        }
        .area-box:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-5px);
        }

        /* Judul Area di Atas */
        .area-box h6 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        /* ANGKA RAKSASA DI TENGAH */
        .angka-besar {
            font-size: 5rem; /* Ukuran raksasa */
            font-weight: 800;
            line-height: 1;
            margin: 10px 0;
            text-shadow: 0 10px 25px rgba(0,0,0,0.3);
            /* Biar angkanya agak nyala */
            background: -webkit-linear-gradient(#a7f3d0, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .angka-besar.penuh {
            background: -webkit-linear-gradient(#fecaca, #ef4444);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Teks Keterangan Kecil di Bawah */
        .info-kecil {
            font-size: 0.85rem;
            opacity: 0.7;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* KOLOM KANAN: Form Login */
        .right-panel {
            width: 45%; 
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .brand-icon {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #4F46E5, #8B5CF6);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.8rem;
            margin-bottom: 25px;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .form-control {
            border-radius: 12px;
            padding: 14px 20px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            font-size: 1rem;
        }
        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            border-color: #4F46E5;
            background: #ffffff;
        }

        .btn-login {
            background: #4F46E5;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
            width: 100%;
            margin-top: 15px;
        }
        .btn-login:hover {
            background: #3730A3;
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 12px 25px rgba(79, 70, 229, 0.4);
        }

        @media (max-width: 992px) {
            .login-container { flex-direction: column; min-height: auto; }
            .left-panel, .right-panel { width: 100%; padding: 40px 30px; }
            .left-panel { min-height: 400px; }
            .left-content h1 { font-size: 2.2rem; }
            .angka-besar { font-size: 4rem; }
        }
    </style>
</head>
<body>

<div class="login-container">
    
    <!-- PANEL KIRI -->
    <div class="left-panel">
        <div class="left-content">
            <h1>Pantau Parkir<br>Lebih Mudah.</h1>
            <p style="font-size: 1.05rem; opacity: 0.9; margin-bottom: 40px; font-weight: 500;">
                Lihat ketersediaan lahan secara <i>real-time</i> sebelum Anda memarkirkan kendaraan.
            </p>

            <?php if (empty($info_area)): ?>
                <div class="alert alert-warning">Belum ada data area parkir.</div>
            <?php else: ?>
                <?php foreach ($info_area as $area): ?>
                    <?php 
                        $sisa = $area['kapasitas'] - $area['terisi']; 
                    ?>
                    <div class="area-box">
                        <!-- Judul Area -->
                        <h6 class="text-white"><i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($area['nama_area']) ?></h6>
                        
                        <!-- ANGKA RAKSASA -->
                        <?php if ($sisa > 0): ?>
                            <div class="angka-besar"><?= $sisa ?></div>
                            <div class="info-kecil text-white-50">Sisa Slot Kosong</div>
                        <?php else: ?>
                            <div class="angka-besar penuh" style="font-size: 3rem; margin: 20px 0;">PENUH</div>
                            <div class="info-kecil text-white-50">Tidak ada slot</div>
                        <?php endif; ?>

                        <!-- Keterangan Bawah -->
                        <div class="mt-3 pt-3 border-top border-light border-opacity-25 d-flex justify-content-between text-white-50" style="font-size: 0.85rem;">
                            <span>Terisi: <b class="text-white"><?= $area['terisi'] ?></b></span>
                            <span>Kapasitas: <b class="text-white"><?= $area['kapasitas'] ?></b></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- PANEL KANAN -->
    <div class="right-panel">
        <div class="brand-icon">
            <i class="bi bi-p-square-fill"></i>
        </div>
        
        <h3 class="fw-bold mb-1" style="color: #1e293b; font-size: 1.8rem;">Login your account</h3>
        <p class="text-muted mb-4" style="font-size: 0.95rem;">Masukkan kredensial Anda untuk melanjutkan.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 text-center small" style="border-radius: 12px; font-size: 0.9rem; border: none; background: #fee2e2; color: #b91c1c;">
                <i class="bi bi-exclamation-circle-fill me-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label text-muted fw-bold" style="font-size: 0.9rem;">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Ketik username Anda..." required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted fw-bold" style="font-size: 0.9rem;">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Ketik password Anda..." required>
            </div>
            
            <button type="submit" class="btn btn-login">
                Login <i class="bi bi-arrow-right ms-2"></i>
            </button>
        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>