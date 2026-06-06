<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
requireAdmin();

$db    = getDB();
$users = $db->query("SELECT id_user AS id, nama, username, role, created_at FROM users ORDER BY role, nama");
$db->close();

$pageTitle = 'Manajemen Pengguna';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>Manajemen Pengguna</h4>
  <a href="create.php" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Tambah Pengguna</a>
</div>

<div class="card">
  <div class="card-header bg-primary text-white"><i class="bi bi-list-ul me-2"></i>Daftar Pengguna</div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th width="40">#</th>
          <th>Nama</th>
          <th>Username</th>
          <th>Role</th>
          <th>Terdaftar</th>
          <th width="130">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($users && $users->num_rows > 0):
          $no = 1;
          while ($row = $users->fetch_assoc()):
            $uid = $row['id'];
        ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= e($row['nama']) ?></td>
          <td><code><?= e($row['username']) ?></code></td>
          <td>
            <span class="badge <?= $row['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?>">
              <?= strtoupper(e($row['role'])) ?>
            </span>
          </td>
          <td class="small text-muted"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
          <td>
            <a href="edit.php?id=<?= $uid ?>" class="btn btn-warning btn-action me-1" title="Edit">
              <i class="bi bi-pencil-fill"></i>
            </a>
            <?php if ($uid != $_SESSION['id']): ?>
            <a href="delete.php?id=<?= $uid ?>" class="btn btn-danger btn-action"
               title="Hapus"
               onclick="return confirm('Hapus pengguna ini?')">
              <i class="bi bi-trash-fill"></i>
            </a>
            <?php else: ?>
            <button class="btn btn-secondary btn-action" disabled>
              <i class="bi bi-trash-fill"></i>
            </button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6"><div class="empty-state"><i class="bi bi-people d-block mb-2"></i>Belum ada pengguna</div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>