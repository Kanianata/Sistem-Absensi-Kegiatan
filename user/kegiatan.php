<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();
$conn = getDB();

$user_id = $_SESSION['id'];
$search  = $_GET['search'] ?? '';
$filter  = $_GET['filter'] ?? 'semua'; // semua | belum | sudah

$sql = "SELECT k.*,
               (SELECT id FROM absensi WHERE user_id = ? AND kegiatan_id = k.id LIMIT 1) AS absensi_id,
               (SELECT status FROM absensi WHERE user_id = ? AND kegiatan_id = k.id LIMIT 1) AS status_absen
        FROM kegiatan k
        WHERE 1=1";
$params = [$user_id, $user_id];
$types  = 'ii';

if ($search) {
    $sql    .= " AND (k.nama_kegiatan LIKE ? OR k.lokasi LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types   .= 'ss';
}
if ($filter === 'belum') {
    $sql .= " AND (SELECT id FROM absensi WHERE user_id = ? AND kegiatan_id = k.id LIMIT 1) IS NULL AND k.tanggal >= CURDATE()";
    $params[] = $user_id; $types .= 'i';
} elseif ($filter === 'sudah') {
    $sql .= " AND (SELECT id FROM absensi WHERE user_id = ? AND kegiatan_id = k.id LIMIT 1) IS NOT NULL";
    $params[] = $user_id; $types .= 'i';
}

$sql .= " ORDER BY k.tanggal DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Kegiatan</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<style>
  :root{--bg:#F0F4FF;--surface:#fff;--primary:#3B5BDB;--primary-dark:#2f4ac4;--text:#1A1D2E;--muted:#6B7280;--border:#E2E8F0;--radius:12px;--shadow:0 2px 16px rgba(0,0,0,.08)}
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
  .page-wrap{max-width:960px;margin:0 auto;padding:32px 20px}
  h1{font-size:1.6rem;font-weight:700;margin-bottom:4px}
  .sub{color:var(--muted);font-size:.9rem;margin-bottom:24px}

  .toolbar{display:flex;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px}
  .search-wrap{display:flex;gap:8px;flex:1;min-width:200px}
  .search-wrap input{flex:1;padding:9px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:inherit;font-size:.9rem;outline:none;transition:.2s}
  .search-wrap input:focus{border-color:var(--primary)}
  .tabs{display:flex;gap:6px}
  .tab{padding:8px 16px;border-radius:8px;font-size:.85rem;font-weight:600;cursor:pointer;text-decoration:none;border:1.5px solid var(--border);color:var(--muted);background:var(--surface);transition:.2s}
  .tab.active,.tab:hover{border-color:var(--primary);color:var(--primary);background:#EEF2FF}
  .tab.active{background:var(--primary);color:#fff;border-color:var(--primary)}

  .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:none;border-radius:8px;font-family:inherit;font-size:.85rem;font-weight:600;cursor:pointer;transition:.2s;text-decoration:none}
  .btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:var(--primary-dark)}
  .btn-sm{padding:6px 12px;font-size:.78rem}
  .btn-disabled{background:#E2E8F0;color:var(--muted);cursor:default;pointer-events:none}

  .kegiatan-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
  .kegiatan-card{background:var(--surface);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);display:flex;flex-direction:column;gap:10px;transition:.2s}
  .kegiatan-card:hover{transform:translateY(-2px);box-shadow:0 6px 24px rgba(0,0,0,.12)}
  .keg-header{display:flex;align-items:flex-start;justify-content:space-between;gap:8px}
  .keg-name{font-size:1rem;font-weight:700;line-height:1.3}
  .keg-meta{font-size:.8rem;color:var(--muted);display:flex;flex-direction:column;gap:3px}
  .keg-meta span{display:flex;align-items:center;gap:4px}
  .keg-footer{margin-top:auto;padding-top:10px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}

  .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700}
  .badge-hadir{background:#D3F9D8;color:#2F9E44}
  .badge-izin{background:#FFF3BF;color:#F08C00}
  .badge-sakit{background:#FFE8CC;color:#D9480F}
  .badge-alfa{background:#FFE3E3;color:#E03131}
  .badge-selesai{background:#E2E8F0;color:#6B7280}
  .badge-aktif{background:#D3F9D8;color:#2F9E44}
  .badge-mendatang{background:#DBE9FF;color:#3B5BDB}

  .empty{text-align:center;padding:60px 20px;color:var(--muted)}
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="page-wrap">
  <h1>🗓 Daftar Kegiatan</h1>
  <p class="sub">Lihat kegiatan yang tersedia dan status absensimu</p>

  <div class="toolbar">
    <form method="GET" class="search-wrap" style="flex-direction:row">
      <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
      <input type="text" name="search" placeholder="Cari kegiatan…" value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-primary">🔍</button>
    </form>
    <div class="tabs">
      <a href="?filter=semua" class="tab <?= $filter==='semua'?'active':'' ?>">Semua</a>
      <a href="?filter=belum" class="tab <?= $filter==='belum'?'active':'' ?>">Belum Absen</a>
      <a href="?filter=sudah" class="tab <?= $filter==='sudah'?'active':'' ?>">Sudah Absen</a>
    </div>
  </div>

  <?php if($result->num_rows > 0): ?>
  <div class="kegiatan-grid">
    <?php while($row = $result->fetch_assoc()): ?>
    <?php
      $today   = date('Y-m-d');
      $tgl     = $row['tanggal'];
      $dayKls  = $tgl < $today ? 'selesai' : ($tgl == $today ? 'aktif' : 'mendatang');
      $dayLbl  = $tgl < $today ? 'Selesai' : ($tgl == $today ? 'Hari Ini' : 'Mendatang');
      $sudah   = !is_null($row['absensi_id']);
      $bisaAbsen = !$sudah && $tgl >= $today;
    ?>
    <div class="kegiatan-card">
      <div class="keg-header">
        <div class="keg-name"><?= htmlspecialchars($row['nama_kegiatan']) ?></div>
        <span class="badge badge-<?= $dayKls ?>"><?= $dayLbl ?></span>
      </div>
      <div class="keg-meta">
        <span>📅 <?= date('d M Y', strtotime($row['tanggal'])) ?></span>
        <?php if($row['lokasi']): ?><span>📍 <?= htmlspecialchars($row['lokasi']) ?></span><?php endif; ?>
        <?php if($row['deskripsi']): ?><span style="margin-top:2px;color:var(--text)"><?= htmlspecialchars(mb_substr($row['deskripsi'], 0, 80)) ?><?= strlen($row['deskripsi']) > 80 ? '…' : '' ?></span><?php endif; ?>
      </div>
      <div class="keg-footer">
        <?php if($sudah): ?>
          <?php $sc = in_array($row['status_absen'], ['hadir','izin','sakit','alfa']) ? $row['status_absen'] : 'alfa'; ?>
          <span class="badge badge-<?= $sc ?>">✓ <?= ucfirst($row['status_absen']) ?></span>
          <span style="font-size:.78rem;color:var(--muted)">Sudah diabsen</span>
        <?php elseif($bisaAbsen): ?>
          <span style="font-size:.8rem;color:var(--muted)">Belum absen</span>
          <a href="absen.php?kegiatan_id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">📝 Absen Sekarang</a>
        <?php else: ?>
          <span style="font-size:.8rem;color:var(--muted)">Belum diabsen</span>
          <span class="btn btn-disabled btn-sm">Tidak tersedia</span>
        <?php endif; ?>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
  <?php else: ?>
  <div class="empty">
    <p style="font-size:2rem;margin-bottom:8px">📭</p>
    <p>Tidak ada kegiatan yang ditemukan.</p>
  </div>
  <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>