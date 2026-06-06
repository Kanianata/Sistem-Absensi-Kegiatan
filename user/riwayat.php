<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();
$conn = getDB();

$user_id = $_SESSION['id'];
$filter_status = $_GET['status'] ?? '';
$search        = $_GET['search'] ?? '';

$sql    = "SELECT a.id, a.status, a.waktu_absen, a.keterangan, k.nama_kegiatan, k.tanggal, k.lokasi
           FROM absensi a
           JOIN kegiatan k ON a.kegiatan_id = k.id
           WHERE a.user_id = ?";
$params = [$user_id];
$types  = 'i';

if ($filter_status) {
    $sql    .= " AND a.status = ?";
    $params[] = $filter_status; $types .= 's';
}
if ($search) {
    $sql    .= " AND k.nama_kegiatan LIKE ?";
    $params[] = "%$search%"; $types .= 's';
}
$sql .= " ORDER BY a.waktu_absen DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Rekap
$rekap = $conn->query("SELECT status, COUNT(*) AS jml FROM absensi WHERE user_id = $user_id GROUP BY status")->fetch_all(MYSQLI_ASSOC);
$rekap_map = array_column($rekap, 'jml', 'status');
$total = array_sum(array_column($rekap, 'jml'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Absensi</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<style>
  :root{--bg:#F0F4FF;--surface:#fff;--primary:#3B5BDB;--primary-dark:#2f4ac4;--text:#1A1D2E;--muted:#6B7280;--border:#E2E8F0;--radius:12px;--shadow:0 2px 16px rgba(0,0,0,.08)}
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
  .page-wrap{max-width:900px;margin:0 auto;padding:32px 20px}
  h1{font-size:1.6rem;font-weight:700;margin-bottom:4px}
  .sub{color:var(--muted);font-size:.9rem;margin-bottom:24px}

  /* Rekap cards */
  .rekap-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:24px}
  @media(max-width:600px){.rekap-grid{grid-template-columns:repeat(2,1fr)}}
  .rekap-card{background:var(--surface);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow);text-align:center}
  .rekap-card .val{font-size:1.7rem;font-weight:800;line-height:1}
  .rekap-card .lbl{font-size:.72rem;color:var(--muted);margin-top:4px;text-transform:uppercase;letter-spacing:.05em;font-weight:600}
  .c-hadir{color:#2F9E44}.c-izin{color:#F08C00}.c-sakit{color:#D9480F}.c-alfa{color:#E03131}.c-total{color:var(--primary)}

  /* Progress bar */
  .progress-wrap{background:var(--surface);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);margin-bottom:24px}
  .progress-wrap h3{font-size:.88rem;font-weight:700;margin-bottom:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em}
  .prog-bar{display:flex;height:14px;border-radius:99px;overflow:hidden;gap:2px}
  .seg{transition:.5s}
  .seg-hadir{background:#2F9E44}.seg-izin{background:#F08C00}.seg-sakit{background:#D9480F}.seg-alfa{background:#E03131}
  .prog-legend{display:flex;gap:16px;margin-top:10px;flex-wrap:wrap}
  .leg-item{display:flex;align-items:center;gap:5px;font-size:.78rem;color:var(--muted)}
  .leg-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}

  /* Filter */
  .toolbar{display:flex;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px}
  .search-wrap{display:flex;gap:8px}
  .search-wrap input{padding:9px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:inherit;font-size:.9rem;outline:none;min-width:220px;transition:.2s}
  .search-wrap input:focus{border-color:var(--primary)}
  .tabs{display:flex;gap:6px;flex-wrap:wrap}
  .tab{padding:7px 14px;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;text-decoration:none;border:1.5px solid var(--border);color:var(--muted);background:var(--surface);transition:.2s}
  .tab:hover{border-color:var(--primary);color:var(--primary)}
  .tab.active{background:var(--primary);color:#fff;border-color:var(--primary)}

  .btn{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border:none;border-radius:8px;font-family:inherit;font-size:.88rem;font-weight:600;cursor:pointer;transition:.2s;text-decoration:none}
  .btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:var(--primary-dark)}

  /* Table */
  .table-card{background:var(--surface);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
  table{width:100%;border-collapse:collapse}
  th{background:#F8FAFF;text-align:left;padding:11px 16px;font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);font-weight:700;border-bottom:1px solid var(--border)}
  td{padding:13px 16px;font-size:.88rem;border-bottom:1px solid #F1F5F9;vertical-align:middle}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:#FAFBFF}

  .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700}
  .badge-hadir{background:#D3F9D8;color:#2F9E44}
  .badge-izin{background:#FFF3BF;color:#F08C00}
  .badge-sakit{background:#FFE8CC;color:#D9480F}
  .badge-alfa{background:#FFE3E3;color:#E03131}
  .empty{text-align:center;padding:48px;color:var(--muted)}
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="page-wrap">
  <h1>📄 Riwayat Absensi</h1>
  <p class="sub">Catatan kehadiran kamu di semua kegiatan</p>

  <!-- Rekap -->
  <div class="rekap-grid">
    <div class="rekap-card"><div class="val c-total"><?= $total ?></div><div class="lbl">Total</div></div>
    <div class="rekap-card"><div class="val c-hadir"><?= $rekap_map['hadir'] ?? 0 ?></div><div class="lbl">Hadir</div></div>
    <div class="rekap-card"><div class="val c-izin"><?= $rekap_map['izin'] ?? 0 ?></div><div class="lbl">Izin</div></div>
    <div class="rekap-card"><div class="val c-sakit"><?= $rekap_map['sakit'] ?? 0 ?></div><div class="lbl">Sakit</div></div>
    <div class="rekap-card"><div class="val c-alfa"><?= $rekap_map['alfa'] ?? 0 ?></div><div class="lbl">Alfa</div></div>
  </div>

  <!-- Progress bar -->
  <?php if($total > 0): ?>
  <div class="progress-wrap">
    <h3>Komposisi Kehadiran</h3>
    <div class="prog-bar">
      <?php foreach(['hadir','izin','sakit','alfa'] as $s): ?>
        <?php $pct = round(($rekap_map[$s] ?? 0) / $total * 100, 1); ?>
        <?php if($pct > 0): ?>
        <div class="seg seg-<?= $s ?>" style="width:<?= $pct ?>%" title="<?= ucfirst($s) ?>: <?= $pct ?>%"></div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <div class="prog-legend">
      <?php foreach(['hadir'=>'#2F9E44','izin'=>'#F08C00','sakit'=>'#D9480F','alfa'=>'#E03131'] as $s => $c): ?>
      <div class="leg-item">
        <div class="leg-dot" style="background:<?= $c ?>"></div>
        <?= ucfirst($s) ?> (<?= round(($rekap_map[$s] ?? 0) / max($total,1) * 100, 1) ?>%)
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Filter -->
  <div class="toolbar">
    <form method="GET" class="search-wrap">
      <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
      <input type="text" name="search" placeholder="Cari nama kegiatan…" value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-primary">🔍</button>
    </form>
    <div class="tabs">
      <a href="?status=" class="tab <?= $filter_status===''?'active':'' ?>">Semua</a>
      <a href="?status=hadir" class="tab <?= $filter_status==='hadir'?'active':'' ?>">Hadir</a>
      <a href="?status=izin" class="tab <?= $filter_status==='izin'?'active':'' ?>">Izin</a>
      <a href="?status=sakit" class="tab <?= $filter_status==='sakit'?'active':'' ?>">Sakit</a>
      <a href="?status=alfa" class="tab <?= $filter_status==='alfa'?'active':'' ?>">Alfa</a>
    </div>
  </div>

  <!-- Table -->
  <div class="table-card">
    <?php if($result->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Kegiatan</th>
          <th>Tgl Kegiatan</th>
          <th>Status</th>
          <th>Waktu Absen</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; while($row = $result->fetch_assoc()): ?>
        <?php $cls = in_array($row['status'], ['hadir','izin','sakit','alfa']) ? $row['status'] : 'alfa'; ?>
        <tr>
          <td style="color:var(--muted);font-size:.8rem"><?= $no++ ?></td>
          <td>
            <div style="font-weight:600"><?= htmlspecialchars($row['nama_kegiatan']) ?></div>
            <?php if($row['lokasi']): ?><div style="font-size:.78rem;color:var(--muted)">📍 <?= htmlspecialchars($row['lokasi']) ?></div><?php endif; ?>
          </td>
          <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
          <td><span class="badge badge-<?= $cls ?>"><?= ucfirst($row['status']) ?></span></td>
          <td style="font-size:.82rem;color:var(--muted)"><?= date('d M Y, H:i', strtotime($row['waktu_absen'])) ?></td>
          <td style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($row['keterangan'] ?: '-') ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="empty"><p>📭 Belum ada riwayat absensi.</p></div>
    <?php endif; ?>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>