<?php
/**
 * Enhanced Front Controller / Router
 * 
 * Flow:
 * 1. Load autoloader → boot App container
 * 2. Cek route map → jika ada, panggil Controller OOP
 * 3. Jika tidak ada di route map → fallback ke Legacy Bridge (file lama)
 * 
 * Ini memungkinkan migrasi bertahap: modul yang sudah di-refaktor
 * jalan via OOP Controller, sisanya tetap jalan seperti biasa.
 */

// ─── Bootstrap ──────────────────────────────────────────────
$base = realpath(__DIR__ . '/..');
$views = $base . '/app/Views';
$controllers = $base . '/app/Controllers';
$config = $base . '/config';
$models = $base . '/app/Models';

// Set include path agar require 'koneksi.php' dll tetap resolve
set_include_path(
    get_include_path()
    . PATH_SEPARATOR . $views
    . PATH_SEPARATOR . $controllers
    . PATH_SEPARATOR . $config
    . PATH_SEPARATOR . $models
);

// Load koneksi database (legacy — menghasilkan $koneksi dan $db global)
require_once $config . '/koneksi.php';

// Load autoloader PSR-4
require_once $base . '/app/Core/Autoloader.php';

// Boot App container dengan koneksi legacy
\App\Core\App::boot($koneksi);

// ─── Route Map (OOP Controllers) ────────────────────────────
// Format: 'nama_file_legacy.php' => ['ControllerClass', 'method']
// Modul yang sudah di-refaktor didaftarkan di sini.
// Modul yang belum di-refaktor TIDAK perlu didaftarkan — otomatis fallback ke legacy.

$routes = [
    // Auth
    'proses_login.php'          => ['App\\Controllers\\AuthController', 'login'],
    'proses_ganti_password.php' => ['App\\Controllers\\AuthController', 'changePassword'],
    'logout.php'                => ['App\\Controllers\\AuthController', 'logout'],

    // Dashboard
    'dashboard.php'             => ['App\\Controllers\\DashboardController', 'index'],

    // Guru CRUD
    'data_guru.php'             => ['App\\Controllers\\GuruController', 'index'],
    'proses_input_guru.php'     => ['App\\Controllers\\GuruController', 'store'],
    'proses_edit_guru.php'      => ['App\\Controllers\\GuruController', 'store'],
    'hapus_guru.php'            => ['App\\Controllers\\GuruController', 'delete'],
];

// ─── Resolve Route ──────────────────────────────────────────
$route = $_GET['route'] ?? 'index.php';
if (empty($route)) $route = 'index.php';

// Sanitasi: hanya izinkan karakter alfanumerik, underscore, dash, dot, dan slash
$route = preg_replace('/[^a-zA-Z0-9_\-\.\/]/', '', $route);

// Cegah directory traversal
if (str_contains($route, '..')) {
    http_response_code(403);
    echo '403 Forbidden';
    exit;
}

// ─── Dispatch ───────────────────────────────────────────────

// 1. Cek apakah ada di route map OOP
if (isset($routes[$route])) {
    [$controllerClass, $method] = $routes[$route];

    // chdir ke Views agar include 'include/header.php' dll resolve
    chdir($views);

    $controller = new $controllerClass();
    $controller->$method();
    exit;
}

// 2. Fallback: Legacy Bridge (untuk file yang belum di-migrasi)
$context = null;
if (file_exists($controllers . '/' . $route)) {
    $context = $controllers . '/' . $route;
    chdir($controllers);
} elseif (file_exists($views . '/' . $route)) {
    $context = $views . '/' . $route;
    chdir($views);
} else {
    http_response_code(404);
    echo '404 Not Found: ' . htmlspecialchars($route);
    exit;
}

require $context;