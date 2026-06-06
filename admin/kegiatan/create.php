<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama_kegiatan'] ?? '');
    $tanggal  = trim($_POST['tanggal'] ?? '');
    $lokasi   = trim($_POST['lokasi'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (!$nama)    $errors[] = 'Nama kegiatan wajib diisi.';
    if (!$tanggal) $errors[] = 'Tanggal wajib diisi.';

    if (!$errors) {
        $stmt = $conn->prepare("INSERT INTO kegiatan (nama_kegiatan, tanggal, lokasi, deskripsi) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $nama, $tanggal, $lokasi, $deskripsi);
        if ($stmt->execute()) {
            header('Location: index.php?success=Kegiatan berhasil ditambahkan');
            exit;
        } else {
            $errors[] = 'Terjadi kesalahan saat menyimpan data.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Kegiatan — Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../assets/style.css">
<style>
  :root{--bg:#F0F4FF;--surface:#fff;--primary:#3B5BDB;--primary-dark:#2f4ac4;--danger:#E03131;--text:#1A1D2E;--muted:#6B7280;--border:#E2E8F0;--radius:12px;--shadow:0 2px 16px rgba(0,0,0,.08)}
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
  .page-wrap{max-width:640px;margin:0 auto;padding:32px 20px}
  h1{font-size:1.6rem;font-weight:700;margin-bottom:4px}
  .sub{color:var(--muted);font-size:.9rem;margin-bottom:28px}
  .card{background:var(--surface);border-radius:var(--radius);padding:28px;box-shadow:var(--shadow)}
  .form-group{margin-bottom:20px}
  label{display:block;font-size:.82rem;font-weight:700;margin-bottom:7px;color:var(--text);text-transform:uppercase;letter-spacing:.04em}
  input,select,textarea{width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:inherit;font-size:.95rem;outline:none;transition:.2s;background:#fff;color:var(--text)}
  input:focus,select:focus,textarea:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(59,91,219,.1)}
  textarea{resize:vertical;min-height:90px}
  .btn{display:inline-flex;align-items:center;gap:6px;padding:10px 22px;border:none;border-radius:8px;font-family:inherit;font-size:.9rem;font-weight:700;cursor:pointer;transition:.2s;text-decoration:none}
  .btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:var(--primary-dark)}
  .btn-secondary{background:#F1F5F9;color:var(--text)}.btn-secondary:hover{background:#E2E8F0}
  .btn-group{display:flex;gap:10px;margin-top:8px}
  .error-box{background:#FFE3E3;color:#E03131;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:.88rem}
  .error-box ul{margin:6px 0 0 16px}
  .required{color:var(--danger)}
</style>
</head>
<body>
<?php include '../../includes/header.php'; ?>
<div class="page-wrap">
  <h1>➕ Tambah Kegiatan</h1>
  <p class="sub">Isi form di bawah untuk menambahkan kegiatan baru</p>

  <?php if($errors): ?>
  <div class="error-box">
    <strong>Terdapat kesalahan:</strong>
    <ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
  </div>
  <?php endif; ?>

  <div class="card">
    <form method="POST">
      <div class="form-group">
        <label>Nama Kegiatan <span class="required">*</span></label>
        <input type="text" name="nama_kegiatan" placeholder="Contoh: Rapat Bulanan Divisi IT"
               value="<?= htmlspecialchars($_POST['nama_kegiatan'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Tanggal <span class="required">*</span></label>
        <input type="date" name="tanggal" value="<?= htmlspecialchars($_POST['tanggal'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Lokasi</label>
        <input type="text" name="lokasi" placeholder="Contoh: Ruang Rapat A"
               value="<?= htmlspecialchars($_POST['lokasi'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="deskripsi" placeholder="Keterangan tambahan tentang kegiatan ini…"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
      </div>
      <div class="btn-group">
        <button type="submit" class="btn btn-primary">💾 Simpan Kegiatan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
</body>
</html>