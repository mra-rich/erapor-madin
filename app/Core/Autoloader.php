<?php
/**
 * PSR-4 Autoloader Sederhana (Tanpa Composer)
 * Mapping: App\Core\ClassName → app/Core/ClassName.php
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../'; // app/

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
