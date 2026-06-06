<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: index.php?error=ID tidak valid');
    exit;
}

$stmt = $conn->prepare("DELETE FROM absensi WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    header('Location: index.php?success=Data absensi berhasil dihapus');
} else {
    header('Location: index.php?error=Data tidak ditemukan atau gagal dihapus');
}
exit;