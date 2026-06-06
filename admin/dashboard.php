<?php
// ============================================================
// admin/dashboard.php — Dashboard utama admin
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
requireAdmin();

$db = getDB();

// Ambil statistik
$totalUser     = $db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetch_row()[0];
$totalKegiatan = $db->query("SELECT COUNT(*) FROM kegiatan")->fetch_row()[0];
$totalAbsensi  = $db->query("SELECT COUNT(*) FROM absensi")->fetch_row()[0];
$totalHadir    = $db->query("SELECT COUNT(*) FROM absensi WHERE status='hadir'")->fetch_row()[0];

// Absensi terbaru (5 data)
$recentAbsensi = $db->query("
    SELECT a.waktu_absen, a.status, u.nama AS nama_user, k.nama_kegiatan
    FROM absensi a
    JOIN users u    ON a.user_id = u.id_user
    JOIN kegiatan k ON a.kegiatan_id = k.id
    ORDER BY a.waktu_absen DESC
    LIMIT 5
");

// Kegiatan mendatang
$upcomingKegiatan = $db->query("
    SELECT nama_kegiatan, tanggal, lokasi
    FROM kegiatan
    WHERE tanggal >= CURDATE()
    ORDER BY tanggal ASC
    LIMIT 5
");

$pageTitle = 'Dashboard Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard Admin</h4>
  <span class="text-muted small"><i class="bi bi-calendar me-1"></i><?= date('d F Y') ?></span>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="card stat-card text-white" style="background:linear-gradient(135deg,#0d6efd,#084298)">
      <div class="card-body d-flex align-items-center gap-3">
        <i class="bi bi-people-fill stat-icon"></i>
        <div>
          <div class="fs-2 fw-bold"><?= $totalUser ?></div>
          <div class="small opacity-75">Total Pengguna</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card stat-card text-white" style="background:linear-gradient(135deg,#198754,#0a5c36)">
      <div class="card-body d-flex align-items-center gap-3">
        <i class="bi bi-calendar3 stat-icon"></i>
        <div>
          <div class="fs-2 fw-bold"><?= $totalKegiatan ?></div>
          <div class="small opacity-75">Total Kegiatan</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card stat-card text-white" style="background:linear-gradient(135deg,#fd7e14,#a34a00)">
      <div class="card-body d-flex align-items-center gap-3">
        <i class="bi bi-clipboard-data stat-icon"></i>
        <div>
          <div class="fs-2 fw-bold"><?= $totalAbsensi ?></div>
          <div class="small opacity-75">Total Absensi</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card stat-card text-white" style="background:linear-gradient(135deg,#0dcaf0,#086b85)">
      <div class="card-body d-flex align-items-center gap-3">
        <i class="bi bi-check2-circle stat-icon"></i>
        <div>
          <div class="fs-2 fw-bold"><?= $totalHadir ?></div>
          <div class="small opacity-75">Total Hadir</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Absensi Terbaru -->
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2"></i>Absensi Terbaru</span>
        <a href="absensi/index.php" class="btn btn-sm btn-light">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Pengguna</th>
              <th>Kegiatan</th>
              <th>Status</th>
              <th>Waktu</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($recentAbsensi->num_rows > 0): ?>
              <?php while ($row = $recentAbsensi->fetch_assoc()): ?>
              <tr>
                <td><?= e($row['nama_user']) ?></td>
                <td class="text-truncate" style="max-width:150px"><?= e($row['nama_kegiatan']) ?></td>
                <td>
                  <span class="badge badge-<?= $row['status'] ?>">
                    <?= ucfirst(e($row['status'])) ?>
                  </span>
                </td>
                <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($row['waktu_absen'])) ?></td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="4" class="text-center text-muted py-3">Belum ada data absensi</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Kegiatan Mendatang -->
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-calendar-event me-2"></i>Kegiatan Mendatang</span>
        <a href="kegiatan/index.php" class="btn btn-sm btn-light">Kelola</a>
      </div>
      <div class="card-body p-0">
        <?php if ($upcomingKegiatan->num_rows > 0): ?>
          <ul class="list-group list-group-flush">
            <?php while ($k = $upcomingKegiatan->fetch_assoc()): ?>
            <li class="list-group-item">
              <div class="fw-semibold"><?= e($k['nama_kegiatan']) ?></div>
              <div class="small text-muted">
                <i class="bi bi-calendar2 me-1"></i><?= date('d M Y', strtotime($k['tanggal'])) ?>
                &nbsp;<i class="bi bi-geo-alt me-1"></i><?= e($k['lokasi']) ?>
              </div>
            </li>
            <?php endwhile; ?>
          </ul>
        <?php else: ?>
          <div class="empty-state"><i class="bi bi-calendar-x d-block mb-2"></i>Tidak ada kegiatan mendatang</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>