<?php
ob_start();
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

// ─── Security Headers ───────────────────────────────────────
// Dikirim sebelum output apa pun. Melindungi dari clickjacking, MIME sniffing,
// dan kebocoran referrer. Aman untuk respons HTML, JSON, maupun download.
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 0');
}

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

    // Nilai — Hapus
    'hapus_nilai.php'           => ['App\\Controllers\\HapusNilaiController', 'delete'],

    // Master tingkat
    'data_tingkat.php'          => ['App\\Controllers\\TingkatController', 'index'],

    // Nilai
    'proses_input_nilai.php'    => ['App\\Controllers\\NilaiController', 'store'],
    'proses_import_nilai.php'   => ['App\\Controllers\\NilaiImportController', 'import'],
    'proses_kenaikan.php'       => ['App\\Controllers\\KenaikanController', 'process'],
];

// ─── Whitelist Legacy (file lama yang sah untuk diakses) ────
// Hanya file yang terdaftar di sini (atau di $routes OOP) yang boleh dieksekusi.
// File dev/test/debug yang belum/tidak terdaftar otomatis ditolak (404),
// sehingga keamanan bersifat struktural — bukan bergantung pada "sudah dihapus".
$legacyWhitelist = [
    // Controllers — API & proses
    'api_get_kelas.php', 'api_load_mapel_default.php', 'api_simpan_mapel_kelas.php',
    'backup_database.php', 'download.php',
    'hapus_kelas.php', 'hapus_mapel.php', 'hapus_santri.php',
    'proses_atur_mapel_kelas.php', 'proses_edit_kelas.php', 'proses_edit_mapel.php',
    'proses_edit_siswa.php', 'proses_evaluasi_wali.php', 'proses_hapus_mapel.php',
    'proses_identitas.php', 'proses_import_guru.php', 'proses_import_kelas.php',
    'proses_import_mapel.php', 'proses_import_siswa.php',
    'proses_input_siswa.php',
    'proses_nilai_massal.php', 'proses_pengampu.php', 'proses_simpan_pengaturan_mapel.php',
    'proses_tambah_kelas.php', 'proses_tambah_mapel.php',

    // Views — halaman
    'index.php', 'dashboard_content.php',
    'atur_mapel_kelas.php', 'data_arsip_santri.php', 'data_guru.php', 'data_kelas.php',
    'data_mata_pelajaran.php', 'data_nilai.php', 'data_santri.php', 'data_tingkat.php',
    'edit_kelas.php', 'edit_mapel.php', 'edit_nilai.php', 'edit_santri.php',
    'evaluasi_wali.php', 'identitas_madrasah.php', 'import_nilai.php',
    'input_nilai_massal.php', 'kenaikan_kelas.php', 'log_aktivitas.php',
    'pengaturan.php', 'penilaian_mapel.php', 'profil.php',
    'tambah_kelas.php', 'tambah_mata_pelajaran.php', 'template_import_excel.php',

    // Views — cetak / export
    'cetak_biodata.php', 'cetak_buku_induk.php', 'cetak_pdf.php', 'cetak_rapot.php',
    'cetak_sampul.php', 'cetak_semua.php', 'cetak_word.php',
    'export_guru.php', 'export_leger.php', 'export_nilai.php', 'export_santri.php',
    'preview_import_guru.php', 'preview_import_kelas.php', 'preview_import_mapel.php',
    'preview_import_siswa.php', 'preview_leger.php', 'preview_rapot.php',
    'download_template_guru.php', 'download_template_kelas.php', 'download_template_mapel.php',

    // Views — import UI/JS
    'import_guru_js.php', 'import_guru_ui.php', 'import_kelas_js.php',
    'import_kelas_ui.php', 'import_mapel_js.php', 'import_mapel_ui.php',

    // Views — endpoint AJAX
    'get_mapel.php', 'get_nilai_siswa.php', 'get_santri_ajax.php',
    'get_siswa.php', 'get_siswa_rapot.php', 'get_terjemahan_mapel.php',
];
$legacyWhitelist = array_flip($legacyWhitelist);

// ─── Resolve Route ──────────────────────────────────────────
$route = $_GET['route'] ?? 'index.php';
if (empty($route) || $route === '/') $route = 'index.php';

// Sanitasi: hanya izinkan karakter alfanumerik, underscore, dash, dot, dan slash
$route = preg_replace('/[^a-zA-Z0-9_\-\.\/]/', '', $route);

// Dukungan Clean URLs: jika rute tidak memiliki ekstensi, tambahkan .php
if (!str_contains(basename($route), '.')) {
    $route .= '.php';
}

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

// 2. Fallback: Legacy Bridge (hanya untuk file yang ada di whitelist)
// Tolak file apa pun yang tidak terdaftar — mencegah eksekusi file dev/test/debug.
$routeBasename = basename($route);
if (!isset($legacyWhitelist[$routeBasename])) {
    http_response_code(404);
    echo '404 Not Found: ' . htmlspecialchars($route);
    exit;
}

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