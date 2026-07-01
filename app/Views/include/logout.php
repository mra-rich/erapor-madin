<?php
/**
 * Legacy Logout (backward-compatible)
 * Redirect ke AuthController::logout() jika diakses langsung.
 * Jika autoloader tersedia, gunakan AuthService. Fallback ke cara lama.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Coba gunakan OOP AuthService
$autoloaderPath = __DIR__ . '/../../Core/Autoloader.php';
if (file_exists($autoloaderPath)) {
    require_once $autoloaderPath;
    \App\Services\AuthService::logout();
} else {
    // Fallback legacy
    session_unset();
    session_destroy();
}

header("Location: ../index.php");
exit;
?>
