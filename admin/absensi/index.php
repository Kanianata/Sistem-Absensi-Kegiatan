<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();

// Filter
$filter_kegiatan = $_GET['kegiatan_id'] ?? '';
$filter_tanggal  = $_GET['tanggal'] ?? '';
$search          = $_GET['search'] ?? '';

$where  = [];
$params = [];
$types  = '';

if ($filter_kegiatan) {
    $where[]  = 'a.kegiatan_id = ?';
    $params[] = $filter_kegiatan;
    $types   .= 'i';
}
if ($filter_tanggal) {
    $where[]  = 'DATE(a.waktu_absen) = ?';
    $params[] = $filter_tanggal;
    $types   .= 's';
}
if ($search) {
    $where[]  = '(u.nama LIKE ? OR u.username LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types   .= 'ss';
}

$sql = "SELECT a.id, u.nama, u.username, k.nama_kegiatan, a.status, a.waktu_absen, a.keterangan
        FROM absensi a
        JOIN users u ON a.user_id = u.id_user
        JOIN kegiatan k ON a.kegiatan_id = k.id"
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
    . " ORDER BY a.waktu_absen DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// List kegiatan untuk filter dropdown
$kegiatan_list = $conn->query("SELECT id, nama_kegiatan FROM kegiatan ORDER BY nama_kegiatan")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Absensi — Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../assets/style.css">
<style>
  :root{
    --bg:#F0F4FF;--surface:#fff;--primary:#3B5BDB;--primary-dark:#2f4ac4;
    --danger:#E03131;--success:#2F9E44;--warning:#F08C00;
    --text:#1A1D2E;--muted:#6B7280;--border:#E2E8F0;
    --radius:12px;--shadow:0 2px 16px rgba(0,0,0,.08);
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
  .page-wrap{max-width:1200px;margin:0 auto;padding:32px 20px}
  h1{font-size:1.6rem;font-weight:700;margin-bottom:4px}
  .sub{color:var(--muted);font-size:.9rem;margin-bottom:24px}

  /* Filter card */
  .filter-card{background:var(--surface);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);margin-bottom:24px}
  .filter-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;align-items:end}
  .form-group label{display:block;font-size:.8rem;font-weight:600;margin-bottom:6px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em}
  .form-group input,.form-group select{width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-family:inherit;font-size:.9rem;outline:none;transition:.2s}
  .form-group input:focus,.form-group select:focus{border-color:var(--primary)}
  .btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border:none;border-radius:8px;font-family:inherit;font-size:.9rem;font-weight:600;cursor:pointer;transition:.2s;text-decoration:none}
  .btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:var(--primary-dark)}
  .btn-danger{background:var(--danger);color:#fff}.btn-danger:hover{opacity:.9}
  .btn-ghost{background:#F1F5F9;color:var(--text)}.btn-ghost:hover{background:#E2E8F0}

  /* Table */
  .table-card{background:var(--surface);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
  .table-header{display:flex;align-items:center;justify-content:space-between;padding:18px 20px;border-bottom:1px solid var(--border)}
  .table-header h2{font-size:1rem;font-weight:700}
  .count-badge{background:var(--bg);color:var(--primary);font-size:.78rem;font-weight:700;padding:4px 10px;border-radius:20px}
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

  .empty{text-align:center;padding:48px 20px;color:var(--muted)}
  .empty svg{margin-bottom:12px;opacity:.4}
</style>
</head>
<body>
<?php include '../../includes/header.php'; ?>
<div class="page-wrap">
  <h1>📋 Data Absensi</h1>
  <p class="sub">Kelola seluruh catatan kehadiran peserta kegiatan</p>

  <!-- Filter -->
  <div class="filter-card">
    <form method="GET">
      <div class="filter-grid">
        <div class="form-group">
          <label>Kegiatan</label>
          <select name="kegiatan_id">
            <option value="">Semua Kegiatan</option>
            <?php foreach($kegiatan_list as $k): ?>
            <option value="<?= $k['id'] ?>" <?= $filter_kegiatan == $k['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($k['nama_kegiatan']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Tanggal</label>
          <input type="date" name="tanggal" value="<?= htmlspecialchars($filter_tanggal) ?>">
        </div>
        <div class="form-group">
          <label>Cari Peserta</label>
          <input type="text" name="search" placeholder="Nama / username…" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="form-group">
          <label>&nbsp;</label>
          <div style="display:flex;gap:8px">
            <button type="submit" class="btn btn-primary">🔍 Filter</button>
            <a href="index.php" class="btn btn-ghost">Reset</a>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Table -->
  <div class="table-card">
    <div class="table-header">
      <h2>Daftar Absensi</h2>
      <?php $total = $result->num_rows; ?>
      <span class="count-badge"><?= $total ?> data</span>
    </div>
    <?php if($total > 0): ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Peserta</th>
          <th>Kegiatan</th>
          <th>Status</th>
          <th>Waktu Absen</th>
          <th>Keterangan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; while($row = $result->fetch_assoc()): ?>
        <tr>
          <td style="color:var(--muted);font-size:.8rem"><?= $no++ ?></td>
          <td>
            <div style="font-weight:600"><?= htmlspecialchars($row['nama']) ?></div>
            <div style="font-size:.78rem;color:var(--muted)">@<?= htmlspecialchars($row['username']) ?></div>
          </td>
          <td><?= htmlspecialchars($row['nama_kegiatan']) ?></td>
          <td>
            <?php
              $cls = match($row['status']) {
                'hadir'  => 'hadir',
                'izin'   => 'izin',
                'sakit'  => 'sakit',
                default  => 'alfa'
              };
            ?>
            <span class="badge badge-<?= $cls ?>"><?= ucfirst($row['status']) ?></span>
          </td>
          <td style="font-size:.82rem"><?= date('d M Y, H:i', strtotime($row['waktu_absen'])) ?></td>
          <td style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars($row['keterangan'] ?: '-') ?></td>
          <td>
            <a href="delete.php?id=<?= $row['id'] ?>"
               class="btn btn-danger"
               style="padding:6px 12px;font-size:.78rem"
               onclick="return confirm('Hapus absensi ini?')">🗑 Hapus</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="empty">
      <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      <p>Tidak ada data absensi ditemukan.</p>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
</body>
</html>