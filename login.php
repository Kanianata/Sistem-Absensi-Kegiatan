<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id_user AS id, nama, username, password, role FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();
        $db->close();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION["id"]  = $user["id"];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: user/dashboard.php');
            }
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Sistem Absensi Kegiatan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body class="login-wrapper">

<div class="login-card card p-4 p-sm-5">
  <div class="text-center mb-4">
    <div class="login-logo mb-2"><i class="bi bi-calendar2-check-fill"></i></div>
    <h3 class="fw-bold text-primary">AbsensiKu</h3>
    <p class="text-muted small">Sistem Absensi Kegiatan</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger py-2 small">
      <i class="bi bi-exclamation-triangle-fill me-1"></i><?= e($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="login.php">
    <div class="mb-3">
      <label for="username" class="form-label fw-semibold">Username</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-person"></i></span>
        <input type="text" id="username" name="username" class="form-control"
               placeholder="Masukkan username"
               value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
      </div>
    </div>
    <div class="mb-4">
      <label for="password" class="form-label fw-semibold">Password</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-lock"></i></span>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="Masukkan password" required>
      </div>
    </div>
    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
      <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
    </button>
  </form>

  <div class="mt-4 p-3 bg-light rounded small text-muted">
    <p class="mb-1 fw-semibold">Akun Demo:</p>
    <p class="mb-0"><i class="bi bi-shield-fill text-primary me-1"></i><strong>Admin</strong> — username: <code>admin</code> | pass: <code>password</code></p>
    <p class="mb-0"><i class="bi bi-person-fill text-success me-1"></i><strong>User</strong> — username: <code>naufa</code> | pass: <code>password</code></p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>