<?php
// ============================================================
// logout.php — Menghancurkan session dan redirect ke login
// ============================================================
require_once __DIR__ . '/includes/auth.php';

// Hapus semua data session
$_SESSION = [];

// Hapus cookie session jika ada
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();
header('Location: ' . getBaseUrl() . '/login.php');
exit;