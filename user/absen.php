<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();
$conn = getDB();

$user_id    = $_SESSION['id'];
$kegiatan_id = intval($_GET['kegiatan_id'] ?? 0);

if (!$kegiatan_id) {
    header('Location: kegiatan.php');
    exit;
}

// Cek kegiatan ada dan tanggalnya valid
$stmt = $conn->prepare("SELECT * FROM kegiatan WHERE id = ?");
$stmt->bind_param('i', $kegiatan_id);
$stmt->execute();
$kegiatan = $stmt->get_result()->fetch_assoc();

if (!$kegiatan) {
    header('Location: kegiatan.php');
    exit;
}

// Cek sudah absen belum
$cek = $conn->prepare("SELECT id FROM absensi WHERE user_id = ? AND kegiatan_id = ?");
$cek->bind_param('ii', $user_id, $kegiatan_id);
$cek->execute();
if ($cek->get_result()->num_rows > 0) {
    header('Location: kegiatan.php?error=Kamu sudah absen di kegiatan ini');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status     = $_POST['status'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (!in_array($status, ['hadir', 'izin', 'sakit', 'alfa'])) {
        $errors[] = 'Status tidak valid.';
    }

    if (!$errors) {
        $stmt = $conn->prepare("INSERT INTO absensi (user_id, kegiatan_id, status, keterangan, waktu_absen) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param('iiss', $user_id, $kegiatan_id, $status, $keterangan);
        if ($stmt->execute()) {
            header('Location: riwayat.php?success=Absensi berhasil dicatat');
            exit;
        } else {
            $errors[] = 'Gagal menyimpan absensi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Absen Kegiatan</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<style>
  :root{--bg:#F0F4FF;--surface:#fff;--primary:#3B5BDB;--primary-dark:#2f4ac4;--danger:#E03131;--text:#1A1D2E;--muted:#6B7280;--border:#E2E8F0;--radius:12px;--shadow:0 2px 16px rgba(0,0,0,.08)}
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
  .page-wrap{max-width:560px;margin:0 auto;padding:32px 20px}
  h1{font-size:1.6rem;font-weight:700;margin-bottom:4px}
  .sub{color:var(--muted);font-size:.9rem;margin-bottom:24px}
  .info-card{background:var(--surface);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);margin-bottom:20px}
  .info-card h3{font-size:1.1rem;font-weight:700;margin-bottom:8px}
  .info-card p{font-size:.88rem;color:var(--muted);margin-bottom:4px}
  .card{background:var(--surface);border-radius:var(--radius);padding:28px;box-shadow:var(--shadow)}
  .form-group{margin-bottom:20px}
  label{display:block;font-size:.82rem;font-weight:700;margin-bottom:7px;text-transform:uppercase;letter-spacing:.04em}
  select,textarea{width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:inherit;font-size:.95rem;outline:none;transition:.2s;background:#fff}
  select:focus,textarea:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(59,91,219,.1)}
  textarea{resize:vertical;min-height:80px}
  .btn{display:inline-flex;align-items:center;gap:6px;padding:10px 22px;border:none;border-radius:8px;font-family:inherit;font-size:.9rem;font-weight:700;cursor:pointer;transition:.2s;text-decoration:none}
  .btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:var(--primary-dark)}
  .btn-secondary{background:#F1F5F9;color:var(--text)}.btn-secondary:hover{background:#E2E8F0}
  .btn-group{display:flex;gap:10px;margin-top:8px}
  .error-box{background:#FFE3E3;color:#E03131;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:.88rem}
  .error-box ul{margin:6px 0 0 16px}
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="page-wrap">
  <h1>📝 Form Absensi</h1>
  <p class="sub">Catat kehadiran kamu untuk kegiatan ini</p>

  <div class="info-card">
    <h3><?= htmlspecialchars($kegiatan['nama_kegiatan']) ?></h3>
    <p>📅 <?= date('d M Y', strtotime($kegiatan['tanggal'])) ?></p>
    <?php if($kegiatan['lokasi']): ?>
    <p>📍 <?= htmlspecialchars($kegiatan['lokasi']) ?></p>
    <?php endif; ?>
  </div>

  <?php if($errors): ?>
  <div class="error-box">
    <ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
  </div>
  <?php endif; ?>

  <div class="card">
    <form method="POST">
      <div class="form-group">
        <label>Status Kehadiran *</label>
        <select name="status" required>
          <option value="">-- Pilih Status --</option>
          <option value="hadir">✅ Hadir</option>
          <option value="izin">📝 Izin</option>
          <option value="sakit">🤒 Sakit</option>
          <option value="alfa">❌ Alfa</option>
        </select>
      </div>
      <div class="form-group">
        <label>Keterangan</label>
        <textarea name="keterangan" placeholder="Opsional — isi jika ada keterangan tambahan"></textarea>
      </div>
      <div class="btn-group">
        <button type="submit" class="btn btn-primary">✅ Kirim Absensi</button>
        <a href="kegiatan.php" class="btn btn-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>