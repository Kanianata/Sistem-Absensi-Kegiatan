<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: index.php?error=ID tidak valid'); exit;
}

// Tidak boleh hapus diri sendiri
if ($id === intval($_SESSION['id'])) {
    header('Location: index.php?error=Tidak dapat menghapus akun sendiri'); exit;
}

// Hapus absensi terkait
$s = $conn->prepare("DELETE FROM absensi WHERE user_id = ?");
$s->bind_param('i', $id);
$s->execute();

// Hapus user
$stmt = $conn->prepare("DELETE FROM users WHERE id_user = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    header('Location: index.php?success=User berhasil dihapus');
} else {
    header('Location: index.php?error=User tidak ditemukan atau gagal dihapus');
}
exit;