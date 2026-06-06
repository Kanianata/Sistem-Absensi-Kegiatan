<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php?error=ID tidak valid'); exit; }

// Hapus absensi terkait terlebih dahulu
$s = $conn->prepare("DELETE FROM absensi WHERE kegiatan_id = ?");
$s->bind_param('i', $id);
$s->execute();

// Hapus kegiatan
$stmt = $conn->prepare("DELETE FROM kegiatan WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    header('Location: index.php?success=Kegiatan berhasil dihapus');
} else {
    header('Location: index.php?error=Kegiatan tidak ditemukan atau gagal dihapus');
}
exit;