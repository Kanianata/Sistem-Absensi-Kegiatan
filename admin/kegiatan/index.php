<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();

$search = $_GET['search'] ?? '';
$sql    = "SELECT k.*, COUNT(a.id) AS jumlah_absensi
           FROM kegiatan k
           LEFT JOIN absensi a ON k.id = a.kegiatan_id"
        . ($search ? " WHERE k.nama_kegiatan LIKE ?" : '')
        . " GROUP BY k.id ORDER BY k.tanggal DESC";

$stmt = $conn->prepare($sql);
if ($search) {
    $like = "%$search%";
    $stmt->bind_param('s', $like);
}
$stmt->execute();
$result = $stmt->get_result();

$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Kegiatan — Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../assets/style.css">
<style>
  :root{
    --bg:#F0F4FF;--surface:#fff;--primary:#3B5BDB;--primary-dark:#2f4ac4;
    --danger:#E03131;--success:#2F9E44;
    --text:#1A1D2E;--muted:#6B7280;--border:#E2E8F0;
    --radius:12px;--shadow:0 2px 16px rgba(0,0,0,.08);
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
  .page-wrap{max-width:1100px;margin:0 auto;padding:32px 20px}
  h1{font-size:1.6rem;font-weight:700;margin-bottom:4px}
  .sub{color:var(--muted);font-size:.9rem;margin-bottom:24px}

  .alert{padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:.88rem;font-weight:500}
  .alert-success{background:#D3F9D8;color:#2F9E44}
  .alert-error{background:#FFE3E3;color:#E03131}

  .toolbar{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px}
  .search-wrap{display:flex;gap:8px}
  .search-wrap input{padding:9px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:inherit;font-size:.9rem;outline:none;min-width:240px;transition:.2s}
  .search-wrap input:focus{border-color:var(--primary)}
  .btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border:none;border-radius:8px;font-family:inherit;font-size:.9rem;font-weight:600;cursor:pointer;transition:.2s;text-decoration:none}
  .btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:var(--primary-dark)}
  .btn-danger{background:var(--danger);color:#fff}.btn-danger:hover{opacity:.9}
  .btn-secondary{background:#F1F5F9;color:var(--text)}.btn-secondary:hover{background:#E2E8F0}
  .btn-sm{padding:6px 12px;font-size:.78rem}

  .table-card{background:var(--surface);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
  table{width:100%;border-collapse:collapse}
  th{background:#F8FAFF;text-align:left;padding:11px 16px;font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);font-weight:700;border-bottom:1px solid var(--border)}
  td{padding:13px 16px;font-size:.88rem;border-bottom:1px solid #F1F5F9;vertical-align:middle}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:#FAFBFF}

  .status-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700}
  .status-aktif{background:#D3F9D8;color:#2F9E44}
  .status-selesai{background:#E2E8F0;color:#6B7280}
  .status-mendatang{background:#DBE9FF;color:#3B5BDB}

  .action-group{display:flex;gap:6px}
  .empty{text-align:center;padding:48px 20px;color:var(--muted)}
</style>
</head>
<body>
<?php include '../../includes/header.php'; ?>
<div class="page-wrap">
  <h1>🗓 Data Kegiatan</h1>
  <p class="sub">Kelola daftar kegiatan yang tersedia dalam sistem</p>

  <?php if($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if($error): ?><div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="toolbar">
    <form method="GET" class="search-wrap">
      <input type="text" name="search" placeholder="Cari nama kegiatan…" value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-primary">🔍</button>
      <?php if($search): ?><a href="index.php" class="btn btn-secondary">Reset</a><?php endif; ?>
    </form>
    <a href="create.php" class="btn btn-primary">+ Tambah Kegiatan</a>
  </div>

  <div class="table-card">
    <?php if($result->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Nama Kegiatan</th>
          <th>Tanggal</th>
          <th>Lokasi</th>
          <th>Status</th>
          <th>Peserta Absen</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; while($row = $result->fetch_assoc()): ?>
        <?php
          $today    = date('Y-m-d');
          $tgl      = $row['tanggal'];
          $statusKls = $tgl < $today ? 'selesai' : ($tgl == $today ? 'aktif' : 'mendatang');
          $statusLbl = $tgl < $today ? 'Selesai' : ($tgl == $today ? 'Hari Ini' : 'Mendatang');
        ?>
        <tr>
          <td style="color:var(--muted);font-size:.8rem"><?= $no++ ?></td>
          <td><strong><?= htmlspecialchars($row['nama_kegiatan']) ?></strong></td>
          <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
          <td style="color:var(--muted)"><?= htmlspecialchars($row['lokasi'] ?? '-') ?></td>
          <td><span class="status-badge status-<?= $statusKls ?>"><?= $statusLbl ?></span></td>
          <td style="font-weight:600;color:var(--primary)"><?= $row['jumlah_absensi'] ?> orang</td>
          <td>
            <div class="action-group">
              <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm">✏️ Edit</a>
              <a href="delete.php?id=<?= $row['id'] ?>"
                 class="btn btn-danger btn-sm"
                 onclick="return confirm('Hapus kegiatan ini? Semua absensi terkait juga akan terhapus.')">🗑</a>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="empty">
      <p>📭 Belum ada data kegiatan. <a href="create.php" style="color:var(--primary)">Tambah sekarang</a>.</p>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
</body>
</html>