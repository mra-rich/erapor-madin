<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'e_raport';
$port = getenv('DB_PORT') ?: 3306;
$ssl = getenv('DB_SSL') === 'true' ? MYSQLI_CLIENT_SSL : 0;

// Matikan error reporting bawaan agar error database tidak bocor ke layar pengguna
mysqli_report(MYSQLI_REPORT_OFF);

$koneksi = mysqli_init();
if ($ssl) {
    // Agar bisa koneksi ke Aiven (membutuhkan SSL)
    $koneksi->ssl_set(NULL, NULL, NULL, NULL, NULL);
}
$koneksi->real_connect($host, $user, $password, $database, $port, NULL, $ssl);

if ($koneksi->connect_error) {
    // Tampilkan pesan ramah jika server mati
    die("<div style='font-family:sans-serif; text-align:center; padding:50px;'><h1>Sistem Sedang Sibuk</h1><p>Mohon maaf, layanan kami sedang dalam pemeliharaan rutin. Silakan kembali dalam beberapa menit.</p></div>");
}

// Inisialisasi Lightweight ORM / Query Builder (legacy)
require_once __DIR__ . '/../app/Models/QueryBuilder.php';
$db = new QueryBuilder($koneksi);
?>
