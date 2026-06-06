<?php
// ============================================================
// includes/header.php
// ============================================================

if (session_status() === PHP_SESSION_NONE) session_start();

$pageTitle = $pageTitle ?? 'Sistem Absensi Kegiatan';
$namaUser  = $_SESSION['nama'] ?? '';
$roleUser  = $_SESSION['role'] ?? '';
$basePath  = getBaseUrl();
$current   = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> — Absensi Kegiatan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $basePath ?>/assets/style.css" rel="stylesheet">
  <style>
    .navbar .nav-link.active {
      background-color: rgba(255,255,255,0.2);
      border-radius: 6px;
      color: #fff !important;
      font-weight: 600;
    }
  </style>
</head>
<body>

<!-- ========== NAVBAR ========== -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="<?= $basePath ?>/">
      <i class="bi bi-calendar2-check-fill me-2"></i>AbsensiKu
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <?php if (isLoggedIn()): ?>
        <ul class="navbar-nav me-auto">
          <?php if ($roleUser === 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link <?= $current === 'dashboard.php' ? 'active' : '' ?>" href="<?= $basePath ?>/admin/dashboard.php">
                <i class="bi bi-speedometer2 me-1"></i>Dashboard
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= in_array($current, ['index.php','create.php','edit.php']) && strpos($_SERVER['PHP_SELF'],'/kegiatan/') !== false ? 'active' : '' ?>" href="<?= $basePath ?>/admin/kegiatan/index.php">
                <i class="bi bi-calendar3 me-1"></i>Kegiatan
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= in_array($current, ['index.php','create.php','edit.php']) && strpos($_SERVER['PHP_SELF'],'/user/') !== false ? 'active' : '' ?>" href="<?= $basePath ?>/admin/user/index.php">
                <i class="bi bi-people-fill me-1"></i>Pengguna
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= in_array($current, ['index.php','delete.php']) && strpos($_SERVER['PHP_SELF'],'/absensi/') !== false ? 'active' : '' ?>" href="<?= $basePath ?>/admin/absensi/index.php">
                <i class="bi bi-clipboard-data me-1"></i>Data Absensi
              </a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link <?= $current === 'dashboard.php' ? 'active' : '' ?>" href="<?= $basePath ?>/user/dashboard.php">
                <i class="bi bi-house-fill me-1"></i>Dashboard
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current === 'kegiatan.php' ? 'active' : '' ?>" href="<?= $basePath ?>/user/kegiatan.php">
                <i class="bi bi-calendar3 me-1"></i>Kegiatan
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current === 'riwayat.php' ? 'active' : '' ?>" href="<?= $basePath ?>/user/riwayat.php">
                <i class="bi bi-clock-history me-1"></i>Riwayat Absensi
              </a>
            </li>
          <?php endif; ?>
        </ul>

        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item me-2">
            <span class="badge bg-light text-primary fs-6 px-3 py-2">
              <i class="bi bi-person-circle me-1"></i><?= e($namaUser) ?>
              <span class="badge bg-primary ms-1"><?= e(strtoupper($roleUser)) ?></span>
            </span>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-light btn-sm" href="<?= $basePath ?>/logout.php">
              <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
          </li>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</nav>
<!-- ========== END NAVBAR ========== -->

<div class="container-fluid py-4 px-4">

<?php
$flash = getFlash();
if ($flash):
    $alertClass = match($flash['type']) {
        'success' => 'alert-success',
        'danger'  => 'alert-danger',
        'warning' => 'alert-warning',
        default   => 'alert-info',
    };
    $icon = match($flash['type']) {
        'success' => 'bi-check-circle-fill',
        'danger'  => 'bi-x-circle-fill',
        'warning' => 'bi-exclamation-triangle-fill',
        default   => 'bi-info-circle-fill',
    };
?>
  <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
    <i class="bi <?= $icon ?> me-2"></i><?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>