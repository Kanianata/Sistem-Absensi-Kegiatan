<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'user';

    if (!$nama)     $errors[] = 'Nama wajib diisi.';
    if (!$username) $errors[] = 'Username wajib diisi.';
    if (!$password) $errors[] = 'Password wajib diisi.';
    if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
    if (!in_array($role, ['admin', 'user'])) $errors[] = 'Role tidak valid.';

    // Cek duplikat username
    if (!$errors) {
        $chk = $conn->prepare("SELECT id_user FROM users WHERE username = ?");
        $chk->bind_param('s', $username);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) $errors[] = 'Username sudah digunakan.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $nama, $username, $hash, $role);
        if ($stmt->execute()) {
            header('Location: index.php?success=User berhasil ditambahkan');
            exit;
        } else {
            $errors[] = 'Gagal menyimpan data user.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah User — Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../assets/style.css">
<style>
  :root{--bg:#F0F4FF;--surface:#fff;--primary:#3B5BDB;--primary-dark:#2f4ac4;--danger:#E03131;--text:#1A1D2E;--muted:#6B7280;--border:#E2E8F0;--radius:12px;--shadow:0 2px 16px rgba(0,0,0,.08)}
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
  .page-wrap{max-width:620px;margin:0 auto;padding:32px 20px}
  h1{font-size:1.6rem;font-weight:700;margin-bottom:4px}
  .sub{color:var(--muted);font-size:.9rem;margin-bottom:28px}
  .card{background:var(--surface);border-radius:var(--radius);padding:28px;box-shadow:var(--shadow)}
  .form-group{margin-bottom:20px}
  .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  label{display:block;font-size:.82rem;font-weight:700;margin-bottom:7px;text-transform:uppercase;letter-spacing:.04em}
  input,select{width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:inherit;font-size:.95rem;outline:none;transition:.2s;background:#fff}
  input:focus,select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(59,91,219,.1)}
  .hint{font-size:.78rem;color:var(--muted);margin-top:4px}
  .btn{display:inline-flex;align-items:center;gap:6px;padding:10px 22px;border:none;border-radius:8px;font-family:inherit;font-size:.9rem;font-weight:700;cursor:pointer;transition:.2s;text-decoration:none}
  .btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:var(--primary-dark)}
  .btn-secondary{background:#F1F5F9;color:var(--text)}.btn-secondary:hover{background:#E2E8F0}
  .btn-group{display:flex;gap:10px;margin-top:8px}
  .error-box{background:#FFE3E3;color:#E03131;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:.88rem}
  .error-box ul{margin:6px 0 0 16px}
  .required{color:var(--danger)}
  .divider{border:none;border-top:1px solid var(--border);margin:20px 0}
</style>
</head>
<body>
<?php include '../../includes/header.php'; ?>
<div class="page-wrap">
  <h1>➕ Tambah User</h1>
  <p class="sub">Buat akun baru untuk peserta atau admin sistem</p>

  <?php if($errors): ?>
  <div class="error-box">
    <strong>Terdapat kesalahan:</strong>
    <ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
  </div>
  <?php endif; ?>

  <div class="card">
    <form method="POST">
      <div class="form-row">
        <div class="form-group">
          <label>Nama Lengkap <span class="required">*</span></label>
          <input type="text" name="nama" placeholder="Budi Santoso" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>Username <span class="required">*</span></label>
          <input type="text" name="username" placeholder="budisantoso" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="budi@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <hr class="divider">
      <div class="form-row">
        <div class="form-group">
          <label>Password <span class="required">*</span></label>
          <input type="password" name="password" placeholder="Minimal 6 karakter" required>
          <p class="hint">Minimal 6 karakter</p>
        </div>
        <div class="form-group">
          <label>Role <span class="required">*</span></label>
          <select name="role">
            <option value="user" <?= ($_POST['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>User (Peserta)</option>
            <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
          </select>
        </div>
      </div>
      <div class="btn-group">
        <button type="submit" class="btn btn-primary">💾 Simpan User</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
</body>
</html>

