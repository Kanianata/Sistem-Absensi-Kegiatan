<?php
// ============================================================
// config/database.php
// Konfigurasi koneksi ke database MySQL/MariaDB
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Sesuaikan dengan user MySQL Anda
define('DB_PASS', '');           // Sesuaikan dengan password MySQL Anda
define('DB_NAME', 'absensi_kegiatan');
define('DB_CHARSET', 'utf8mb4');

/**
 * Membuat koneksi MySQLi dan mengembalikan objek koneksi.
 * Akan mati dengan pesan error jika koneksi gagal.
 */
function getDB(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die('<div style="font-family:sans-serif;padding:2rem;color:red;">
            <h2>Koneksi Database Gagal</h2>
            <p>' . htmlspecialchars($conn->connect_error) . '</p>
            <p>Periksa konfigurasi di <code>config/database.php</code></p>
        </div>');
    }

    $conn->set_charset(DB_CHARSET);
    return $conn;
}