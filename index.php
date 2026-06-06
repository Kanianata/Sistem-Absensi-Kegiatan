<?php
// ============================================================
// index.php — Entry point, redirect sesuai status login
// ============================================================
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
} else {
    header('Location: login.php');
}
exit;