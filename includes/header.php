<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    header("Location: /parkir_app/auth/login.php");
    exit;
}

$role = $_SESSION['role'];

// Warna aksen elegan & cerah per role
$warna_aksen = '#4F46E5'; 
$bg_icon = '#EEF2FF'; 
$warna_teks_aksen = '#3730A3';

if ($role === 'petugas') {
    $warna_aksen = '#10B981'; 
    $bg_icon = '#ECFDF5';
    $warna_teks_aksen = '#065F46';
} elseif ($role === 'owner') {
    $warna_aksen = '#8B5CF6'; 
    $bg_icon = '#F5F3FF';
    $warna_teks_aksen = '#5B21B6';
}

// BASE URL ABSOLUTE
$base_url = '/parkir_app/';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Parkir - <?= ucfirst($role) ?></title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🅿️</text></svg>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        
        :root {
            --warna-aksen: <?= $warna_aksen ?>;
            --bg-icon: <?= $bg_icon ?>;
            --warna-teks-aksen: <?= $warna_teks_aksen ?>;
        }
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #F8FAFC;
            color: #334155;
            overflow-x: hidden; /* Cegah scroll ke samping di HP */
        }

        .navbar-top {
            background-color: #ffffff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            border-bottom: 1px solid #e2e8f0;
        }
        .navbar-brand { color: var(--warna-teks-aksen) !important; font-weight: 700; font-size: 1.25rem; }
        
        /* Sidebar Desktop */
        .sidebar-wrapper { padding: 20px 15px; }
        .sidebar-menu {
            background: #ffffff;
            border-radius: 20px;
            padding: 20px 0;
            box-shadow: 0 4px 25px rgba(0,0,0,0.04);
            height: calc(100vh - 100px);
            position: sticky;
            top: 80px;
            overflow-y: auto;
        }
        .sidebar-menu h5 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            font-weight: 700;
            padding: 0 25px;
            margin-bottom: 15px;
        }
        .sidebar-menu a {
            color: #64748b;
            text-decoration: none;
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            margin: 2px 15px;
            border-radius: 12px;
        }
        .sidebar-menu a i { font-size: 1.2rem; }
        .sidebar-menu a:hover {
            background-color: var(--bg-icon);
            color: var(--warna-teks-aksen);
        }
        .sidebar-menu a.active {
            background-color: var(--warna-aksen);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        /* --- STYLING OFFCANVAS (MENU DI HP) --- */
        .offcanvas-body .sidebar-menu {
            box-shadow: none;
            height: auto;
            padding: 0;
        }
        .offcanvas-body .sidebar-menu a {
            margin: 5px 0;
        }

        .card-elegan {
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.04);
            background: #ffffff;
        }
        
        .profil-badge {
            background: var(--bg-icon);
            color: var(--warna-teks-aksen);
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .btn-aksen {
            background-color: var(--warna-aksen);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s;
        }
        .btn-aksen:hover {
            opacity: 0.9;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }

        /* Styling DataTables (Tetap) */
        .dt-buttons { margin-bottom: 15px; }
        .dataTables_wrapper .dataTables_length select, .dataTables_wrapper .dataTables_filter input { border-radius: 10px; padding: 5px 15px; border: 1px solid #cbd5e1; outline: none; }
        .table thead th { border-bottom: 2px solid #e2e8f0; font-size: 0.85rem; text-transform: uppercase; color: #64748b; }
        
        /* --- DARK MODE THEME --- */
        body.dark-mode { background-color: #0f172a !important; color: #cbd5e1 !important; }
        body.dark-mode .navbar-top, body.dark-mode .sidebar-menu, body.dark-mode .card-elegan, body.dark-mode .card, body.dark-mode .offcanvas {
            background-color: #1e293b !important;
            color: #cbd5e1 !important;
            border-color: #334155 !important;
        }
        body.dark-mode .navbar-brand { color: #e2e8f0 !important; }
        body.dark-mode .sidebar-menu a { color: #94a3b8; }
        body.dark-mode .sidebar-menu a:hover { background-color: #334155; color: #fff; }
        body.dark-mode .sidebar-menu h5 { color: #64748b; }
        body.dark-mode .text-muted { color: #94a3b8 !important; }
        body.dark-mode .table { color: #cbd5e1; border-color: #334155; }
        body.dark-mode .table > :not(caption) > * > * { background-color: transparent !important; border-color: #334155 !important; }
        body.dark-mode .table thead th { background-color: #1e293b !important; color: #94a3b8 !important; }
        body.dark-mode .table-hover tbody tr:hover > * { background-color: #334155 !important; color: #fff !important; }
        body.dark-mode .badge.bg-light { background-color: #334155 !important; color: #e2e8f0 !important; border-color: #475569 !important; }
        body.dark-mode .form-control, body.dark-mode .form-select { background-color: #0f172a !important; color: #e2e8f0 !important; border-color: #334155 !important; }
        body.dark-mode .dataTables_wrapper .dataTables_length select, body.dark-mode .dataTables_wrapper .dataTables_filter input { background-color: #1e293b !important; color: #fff !important; border-color: #334155 !important; }
    </style>
</head>
<body>
    <script>
        if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-mode'); }
    </script>

<!-- Navbar Atas -->
<nav class="navbar navbar-expand-lg navbar-top fixed-top py-3">
  <div class="container-fluid px-3 px-md-4">
    
    <!-- Tombol Hamburger (Cuma muncul di HP) -->
    <button class="navbar-toggler border-0 d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile" aria-controls="sidebarMobile">
        <i class="bi bi-list fs-2" style="color: var(--warna-aksen);"></i>
    </button>

    <a class="navbar-brand" href="<?= $base_url ?>index.php">
        <i class="bi bi-p-square-fill"></i> ParkirApp
    </a>
    
    <div class="d-flex align-items-center gap-2 gap-md-3 ms-auto">
      
      <!-- Tombol Dark Mode -->
      <button class="btn btn-light border rounded-circle shadow-sm d-none d-md-block" id="themeToggle" style="width: 40px; height: 40px; padding: 0;">
          <i class="bi bi-moon-stars-fill text-dark" id="themeIcon"></i>
      </button>

      <!-- Profil Badge (Sembunyiin teks role di HP) -->
      <div class="profil-badge d-none d-md-block">
        <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['nama_lengkap']) ?> 
        <span class="opacity-50 mx-1">|</span> <?= ucfirst($role) ?>
      </div>
      
      <!-- Profil Icon aja di HP -->
      <div class="profil-badge d-md-none" style="padding: 5px 10px;">
          <i class="bi bi-person-circle"></i>
      </div>

      <a href="<?= $base_url ?>auth/logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">
          <i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Logout</span>
      </a>
    </div>
  </div>
</nav>

<div style="height: 85px;"></div>

<div class="container-fluid">
  <div class="row">
    <!-- SIDEBAR DESKTOP (Sembunyiin di HP) -->
    <div class="col-md-2 sidebar-wrapper d-none d-lg-block">
      <div class="sidebar-menu">
          <?php include __DIR__ . '/sidebar.php'; ?>
      </div>
    </div>
    
    <!-- KONTEN UTAMA (Lebar Full di HP) -->
    <div class="col-12 col-lg-10 p-3 p-md-4 pe-lg-5">