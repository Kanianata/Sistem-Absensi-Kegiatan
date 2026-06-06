<?php
// ============================================================
// includes/auth.php
// Helper autentikasi: cek session, guard halaman, dan role
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Pastikan pengguna sudah login.
 * Jika belum, redirect ke login.php
 */
function requireLogin(): void {
    if (empty($_SESSION['id'])) {
        header('Location: ' . getBaseUrl() . '/login.php');
        exit;
    }
}

/**
 * Pastikan pengguna adalah admin.
 * Jika bukan, redirect ke dashboard user.
 */
function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ' . getBaseUrl() . '/user/dashboard.php');
        exit;
    }
}

/**
 * Pastikan pengguna adalah user biasa (bukan admin).
 */
function requireUser(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'user') {
        header('Location: ' . getBaseUrl() . '/admin/dashboard.php');
        exit;
    }
}

/**
 * Cek apakah sudah login (boolean).
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['id']);
}

/**
 * Cek apakah role-nya admin (boolean).
 */
function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

/**
 * Mendapatkan base URL project (tanpa trailing slash).
 * Berguna untuk redirect yang akurat di semua kedalaman folder.
 */
function getBaseUrl(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'];
    // Ambil path hingga folder project (absensi_kegiatan)
    $script   = $_SERVER['SCRIPT_NAME'];
    // Cari posisi folder project dalam URL
    $parts    = explode('/', trim($script, '/'));
    // Folder pertama adalah nama project di htdocs
    $base     = '/' . ($parts[0] ?? '');
    return $protocol . '://' . $host . $base;
}

/**
 * Escape output untuk mencegah XSS.
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Set pesan flash (disimpan di session, ditampilkan sekali).
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Ambil dan hapus pesan flash.
 */
function getFlash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}