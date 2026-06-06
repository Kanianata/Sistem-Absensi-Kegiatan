<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();
$conn = getDB();

$user_id = $_SESSION['id'];

// Statistik absensi user
$stats = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(status='hadir') AS hadir,
        SUM(status='izin')  AS izin,
        SUM(status='sakit') AS sakit,
        SUM(status='alfa')  AS alfa
    FROM absensi WHERE user_id = $user_id
")->fetch_assoc();

// Kegiatan mendatang (belum diabsen)
$upcoming = $conn->query("
    SELECT k.*
    FROM kegiatan k
    WHERE k.tanggal >= CURDATE()
      AND k.id NOT IN (SELECT kegiatan_id FROM absensi WHERE user_id = $user_id)
    ORDER BY k.tanggal ASC
    LIMIT 5
");

// 5 absensi terakhir
$recent = $conn->query("
    SELECT a.status, a.waktu_absen, k.nama_kegiatan
    FROM absensi a
    JOIN kegiatan k ON a.kegiatan_id = k.id
    WHERE a.user_id = $user_id
    ORDER BY a.waktu_absen DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Peserta</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<style>
  :root{--bg:#F0F4FF;--surface:#fff;--primary:#3B5BDB;--primary-dark:#2f4ac4;--text:#1A1D2E;--muted:#6B7280;--border:#E2E8F0;--radius:12px;--shadow:0 2px 16px rgba(0,0,0,.08)}
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
  .page-wrap{max-width:1000px;margin:0 auto;padding:32px 20px}

  /* Welcome banner */
  .welcome{background:linear-gradient(135deg,var(--primary) 0%,#5C7CFA 100%);color:#fff;border-radius:var(--radius);padding:28px 32px;margin-bottom:28px;position:relative;overflow:hidden}
  .welcome::before{content:'';position:absolute;right:-40px;top:-40px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.08)}
  .welcome::after{content:'';position:absolute;right:60px;bottom:-60px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.05)}
  .welcome h2{font-size:1.5rem;font-weight:800;margin-bottom:4px}
  .welcome p{font-size:.92rem;opacity:.85}

  /* Stats */
  .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px;margin-bottom:28px}
  .stat-card{background:var(--surface);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);text-align:center}
  .stat-card .icon{font-size:1.8rem;margin-bottom:8px}
  .stat-card .value{font-size:2rem;font-weight:800;line-height:1}
  .stat-card .label{font-size:.78rem;color:var(--muted);margin-top:4px;text-transform:uppercase;letter-spacing:.05em;font-weight:600}
  .stat-hadir .value{color:#2F9E44}
  .stat-izin  .value{color:#F08C00}
  .stat-sakit .value{color:#D9480F}
  .stat-alfa  .value{color:#E03131}

  /* Two column */
  .two-col{display:grid;grid-template-columns:1fr 1fr;gap:20px}
  @media(max-width:640px){.two-col{grid-template-columns:1fr}}

  .card{background:var(--surface);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
  .card-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
  .card-header h3{font-size:.95rem;font-weight:700}
  .card-body{padding:4px 0}

  .list-item{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid #F1F5F9}
  .list-item:last-child{border-bottom:none}
  .list-item:hover{background:#FAFBFF}
  .list-title{font-weight:600;font-size:.88rem}
  .list-sub{font-size:.75rem;color:var(--muted);margin-top:2px}

  .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700}
  .badge-hadir{background:#D3F9D8;color:#2F9E44}
  .badge-izin{background:#FFF3BF;color:#F08C00}
  .badge-sakit{background:#FFE8CC;color:#D9480F}
  .badge-alfa{background:#FFE3E3;color:#E03131}
  .badge-upcoming{background:#DBE9FF;color:#3B5BDB}

  .btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border:none;border-radius:8px;font-family:inherit;font-size:.82rem;font-weight:600;cursor:pointer;transition:.2s;text-decoration:none}
  .btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:var(--primary-dark)}
  .btn-ghost{background:transparent;color:var(--primary);font-size:.82rem;font-weight:600;text-decoration:none;padding:0}
  .btn-ghost:hover{text-decoration:underline}

  .empty{padding:24px;text-align:center;color:var(--muted);font-size:.88rem}
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="page-wrap">

  <!-- Welcome -->
  <div class="welcome">
    <h2>👋 Halo, <?= htmlspecialchars($_SESSION['nama'] ?? 'Peserta') ?>!</h2>
    <p>Selamat datang di sistem absensi kegiatan. Pantau kehadiran dan kegiatan kamu di sini.</p>
  </div>

  <!-- Stats -->
  <div class="stat-grid">
    <div class="stat-card">
      <div class="icon">📊</div>
      <div class="value"><?= $stats['total'] ?></div>
      <div class="label">Total Absensi</div>
    </div>
    <div class="stat-card stat-hadir">
      <div class="icon">✅</div>
      <div class="value"><?= $stats['hadir'] ?></div>
      <div class="label">Hadir</div>
    </div>
    <div class="stat-card stat-izin">
      <div class="icon">📝</div>
      <div class="value"><?= $stats['izin'] ?></div>
      <div class="label">Izin</div>
    </div>
    <div class="stat-card stat-sakit">
      <div class="icon">🤒</div>
      <div class="value"><?= $stats['sakit'] ?></div>
      <div class="label">Sakit</div>
    </div>
    <div class="stat-card stat-alfa">
      <div class="icon">❌</div>
      <div class="value"><?= $stats['alfa'] ?></div>
      <div class="label">Alfa</div>
    </div>
  </div>

  <!-- Two column -->
  <div class="two-col">
    <!-- Kegiatan mendatang -->
    <div class="card">
      <div class="card-header">
        <h3>🗓 Kegiatan Mendatang</h3>
        <a href="kegiatan.php" class="btn-ghost">Lihat semua →</a>
      </div>
      <div class="card-body">
        <?php if($upcoming->num_rows > 0): ?>
          <?php while($row = $upcoming->fetch_assoc()): ?>
          <div class="list-item">
            <div>
              <div class="list-title"><?= htmlspecialchars($row['nama_kegiatan']) ?></div>
              <div class="list-sub">📅 <?= date('d M Y', strtotime($row['tanggal'])) ?><?= $row['lokasi'] ? ' · 📍 ' . htmlspecialchars($row['lokasi']) : '' ?></div>
            </div>
            <a href="absen.php?kegiatan_id=<?= $row['id'] ?>" class="btn btn-primary">Absen</a>
          </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="empty">🎉 Tidak ada kegiatan mendatang yang belum diabsen.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Riwayat terakhir -->
    <div class="card">
      <div class="card-header">
        <h3>🕐 Absensi Terakhir</h3>
        <a href="riwayat.php" class="btn-ghost">Lihat semua →</a>
      </div>
      <div class="card-body">
        <?php if($recent->num_rows > 0): ?>
          <?php while($row = $recent->fetch_assoc()): ?>
          <?php $cls = in_array($row['status'], ['hadir','izin','sakit','alfa']) ? $row['status'] : 'alfa'; ?>
          <div class="list-item">
            <div>
              <div class="list-title"><?= htmlspecialchars($row['nama_kegiatan']) ?></div>
              <div class="list-sub"><?= date('d M Y, H:i', strtotime($row['waktu_absen'])) ?></div>
            </div>
            <span class="badge badge-<?= $cls ?>"><?= ucfirst($row['status']) ?></span>
          </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="empty">Belum ada riwayat absensi.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>