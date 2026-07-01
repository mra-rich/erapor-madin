<?php
namespace App\Core;

use App\Services\CsrfService;
use App\Services\LoggerService;

/**
 * Abstract Base Controller
 * Menyediakan helper umum: redirect, RBAC, CSRF, render view, JSON response.
 * Semua controller OOP extends class ini.
 */
abstract class BaseController
{
    protected \mysqli $db;

    public function __construct()
    {
        $this->db = App::getInstance()->db();
    }

    // ─── RBAC ────────────────────────────────────────────────

    protected function requireAuth(): void
    {
        if (!isset($_SESSION['id_pengguna'])) {
            $this->redirect('index.php');
        }
    }

    /**
     * @param string[] $roles Daftar role yang diizinkan
     */
    protected function requireRole(array $roles): void
    {
        $this->requireAuth();
        if (!in_array($_SESSION['peran'] ?? '', $roles, true)) {
            $this->redirect('dashboard.php', 'error', 'Akses Ditolak: Anda tidak memiliki izin.');
        }
    }

    // ─── CSRF ────────────────────────────────────────────────

    protected function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        if (!CsrfService::verify($token)) {
            $this->redirect('index.php', 'error', 'Token keamanan tidak valid.');
        }
    }

    // ─── Redirect ────────────────────────────────────────────

    protected function redirect(string $page, string $status = '', string $message = ''): void
    {
        $params = [];
        if ($status)  $params['status']  = $status;
        if ($message) $params['message'] = $message;

        $url = $page;
        if ($params) {
            $url .= '?' . http_build_query($params);
        }

        // Support HTMX partial request
        if (isset($_SERVER['HTTP_HX_REQUEST'])) {
            echo "<script>window.location.href='" . addslashes($url) . "';</script>";
            exit;
        }

        header("Location: $url");
        exit;
    }

    // ─── Input ───────────────────────────────────────────────

    protected function input(string $key, mixed $default = ''): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    // ─── Render View ─────────────────────────────────────────

    /**
     * Render file view dengan data yang diberikan.
     * Variable dalam $data akan di-extract sehingga tersedia di dalam view.
     * Variabel $koneksi juga disediakan untuk backward-compatibility dengan view legacy.
     */
    protected function render(string $viewFile, array $data = []): void
    {
        // Backward-compat: view legacy masih pakai $koneksi global
        $koneksi = $this->db;
        $db = $koneksi; // alias

        extract($data, EXTR_SKIP);

        $fullPath = dirname(__DIR__) . '/Views/' . $viewFile;
        if (!file_exists($fullPath)) {
            throw new \RuntimeException("View tidak ditemukan: $viewFile");
        }

        require $fullPath;
    }

    // ─── JSON Response ───────────────────────────────────────

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ─── Logger ──────────────────────────────────────────────

    protected function log(string $aksi, string $detail = ''): void
    {
        LoggerService::log(
            $this->db,
            $_SESSION['id_pengguna'] ?? null,
            $aksi,
            $detail
        );
    }
}
