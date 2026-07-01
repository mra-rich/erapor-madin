<?php
$baseDir = __DIR__;

// 1. Create directories
$dirs = [
    'public',
    'app',
    'app/Controllers',
    'app/Views',
    'app/Models',
    'config'
];
foreach($dirs as $dir) {
    if (!is_dir($baseDir . '/' . $dir)) {
        mkdir($baseDir . '/' . $dir, 0755, true);
    }
}

// 2. Move public assets
$publicAssets = ['assets', 'css', 'uploads'];
foreach ($publicAssets as $asset) {
    if (is_dir($baseDir . '/' . $asset)) {
        rename($baseDir . '/' . $asset, $baseDir . '/public/' . $asset);
    }
}

// 3. Move config and models
if (file_exists($baseDir . '/koneksi.php')) {
    $koneksiContent = file_get_contents($baseDir . '/koneksi.php');
    file_put_contents($baseDir . '/config/koneksi.php', $koneksiContent);
    unlink($baseDir . '/koneksi.php');
}

if (file_exists($baseDir . '/QueryBuilder.php')) {
    rename($baseDir . '/QueryBuilder.php', $baseDir . '/app/Models/QueryBuilder.php');
}

// 4. Move Views and Controllers
$files = glob($baseDir . '/*.php');
foreach ($files as $file) {
    if (is_dir($file)) continue;
    $filename = basename($file);
    if (in_array($filename, ['migration.php'])) continue; // skip this script

    $isController = false;
    if (
        str_starts_with($filename, 'proses_') ||
        str_starts_with($filename, 'api_') ||
        str_starts_with($filename, 'hapus_') ||
        in_array($filename, ['cek_sesi.php', 'logout.php', 'backup_database.php', 'download.php'])
    ) {
        $isController = true;
    }
    
    if ($isController) {
        rename($file, $baseDir . '/app/Controllers/' . $filename);
    } else {
        rename($file, $baseDir . '/app/Views/' . $filename);
    }
}

// Move include folder
if (is_dir($baseDir . '/include')) {
    rename($baseDir . '/include', $baseDir . '/app/Views/include');
}

// 5. Write public/index.php Router
$indexContent = <<<'PHP'
<?php
$route = $_GET['route'] ?? 'index.php';
if (empty($route)) $route = 'index.php';

$base = realpath(__DIR__ . '/..');
$views = $base . '/app/Views';
$controllers = $base . '/app/Controllers';
$config = $base . '/config';
$models = $base . '/app/Models'; 

// Set include path so requires like "require 'koneksi.php'" still work!
set_include_path(get_include_path() . PATH_SEPARATOR . $views . PATH_SEPARATOR . $controllers . PATH_SEPARATOR . $config . PATH_SEPARATOR . $models);

$context = null;
if (file_exists($controllers . '/' . $route)) {
    $context = $controllers . '/' . $route;
    chdir($controllers);
} elseif (file_exists($views . '/' . $route)) {
    $context = $views . '/' . $route;
    chdir($views);
} else {
    http_response_code(404);
    echo "404 Not Found: " . htmlspecialchars($route);
    exit;
}

require $context;
PHP;

file_put_contents($baseDir . '/public/index.php', $indexContent);

// 6. Write .htaccess
$htaccessContent = <<<'HTACCESS'
RewriteEngine On

# Redirect assets
RewriteRule ^assets/(.*)$ public/assets/$1 [L]
RewriteRule ^css/(.*)$ public/css/$1 [L]
RewriteRule ^uploads/(.*)$ public/uploads/$1 [L]

# Route PHP files
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*\.php)$ public/index.php?route=$1 [QSA,L]
RewriteRule ^$ public/index.php?route=index.php [QSA,L]
HTACCESS;
file_put_contents($baseDir . '/.htaccess', $htaccessContent);

echo "Migration completed successfully!\n";
