<?php
/**
 * Router development untuk PHP built-in server.
 * File statis dilayani langsung; route aplikasi diteruskan ke public/index.php.
 */
$publicRoot = __DIR__ . DIRECTORY_SEPARATOR . 'public';
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = realpath($publicRoot . DIRECTORY_SEPARATOR . ltrim($uriPath, '/'));

if ($file !== false && str_starts_with($file, realpath($publicRoot)) && is_file($file)) {
    return false;
}

// Samakan perilaku development server dengan rewrite Apache/Vercel.
$route = trim($uriPath, '/');
$_GET['route'] = $route === '' ? 'index.php' : $route;

require $publicRoot . DIRECTORY_SEPARATOR . 'index.php';
