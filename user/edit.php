<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$db   = getDB();
$stmt = $db->prepare("SELECT id_user AS id, nama, username, role FROM users WHERE id_user = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) { setFlash('danger', 'Pengguna tidak ditemukan.'); header('Location: index.php'); exit; }

$errors = [];
$input  = $user;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['nama']     = trim($_POST['nama']     ?? '');
    $input['username'] = trim($_POST['username'] ?? '');
    $input['role']     = trim($_POST['role']     ?? 'user');
    $password          = trim($_POST['password'] ?? '');
    $password_confirm  = trim($_POST['password_confirm'] ?? '');

    if ($input['nama'] === '')     $errors[] = 'Nama wajib diisi.';
    if ($input['username'] === '') $errors[] = 'Username wajib diisi.';
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $input['username']))
        $errors[] = 'Username hanya huruf, angka, underscore (3–50 karakter).';
    if ($password !== '' && strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($password !== '' && $password !== $password_confirm) $errors[] = 'Konfirmasi password tidak cocok.';
    if (!in_array($input['role'], ['admin', 'user'])) $errors[] = 'Role tidak valid.';

    if (empty($errors)) {
        $chk = $db->prepare("SELECT id_user FROM users WHERE username = ? AND id_user != ?");
        $chk->bind_param('si', $input['username'], $id);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) $errors[] = 'Username sudah digunakan pengguna lain.';
        $chk->close();
    }

    if (empty($errors)) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET nama=?, username=?, password=?, role=? WHERE id_user=?");
            $stmt->bind_param('ssssi', $input['nama'], $input['username'], $hash, $input['role'], $id);
        } else {
            $stmt = $db->prepare("UPDATE users SET nama=?, username=?, role=? WHERE id_user=?");
            $stmt->bind_param('sssi', $input['nama'], $input['username'], $input['role'], $id);
        }
        $stmt->execute();
        $stmt->close();
        $db->close();

        setFlash('success', 'Data pengguna berhasil diperbarui.');
        header('Location: index.php');
        exit;
    }
}
$db->close();

$pageTitle = 'Edit Pengguna';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex align-items-center mb-4">
  <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
  <h4 class="fw-bold mb-0"><i class="bi bi-person-gear me-2 text-warning"></i>Edit Pengguna</h4>
</div>

<div class="card" style="max-width:580px">
  <div class="card-header bg-warning text-dark">Form Edit Pengguna</div>
  <div class="card-body">
    <?php if ($errors): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err) echo "<li>".e($err)."</li>"; ?></ul></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" name="nama" class="form-control" value="<?= e($input['nama']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
        <input type="text" name="username" class="form-control" value="<?= e($input['username']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Password Baru</label>
        <input type="password" name="password" class="form-control">
        <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
        <input type="password" name="password_confirm" class="form-control">
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
        <select name="role" class="form-select" <?= $id == $_SESSION['id'] ? 'disabled' : '' ?>>
          <option value="user"  <?= $input['role']==='user'  ? 'selected':'' ?>>User</option>
          <option value="admin" <?= $input['role']==='admin' ? 'selected':'' ?>>Admin</option>
        </select>
        <?php if ($id == $_SESSION['id']): ?>
          <input type="hidden" name="role" value="<?= e($input['role']) ?>">
          <div class="form-text text-warning">Role tidak dapat diubah untuk akun sendiri.</div>
        <?php endif; ?>
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Update</button>
        <a href="index.php" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>