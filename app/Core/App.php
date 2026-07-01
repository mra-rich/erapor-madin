<?php
namespace App\Core;

/**
 * Application Container (Singleton)
 * Menyediakan akses terpusat ke database connection dan konfigurasi.
 * Menjembatani variabel global legacy ($koneksi) ke pola OOP.
 */
class App
{
    private static ?App $instance = null;
    private \mysqli $db;
    private array $config = [];

    private function __construct(\mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Boot aplikasi dengan koneksi database yang sudah ada (dari legacy koneksi.php)
     */
    public static function boot(\mysqli $db): self
    {
        if (self::$instance === null) {
            self::$instance = new self($db);
        }
        return self::$instance;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new \RuntimeException('App belum di-boot. Panggil App::boot($koneksi) terlebih dahulu.');
        }
        return self::$instance;
    }

    public function db(): \mysqli
    {
        return $this->db;
    }

    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Base URL aplikasi (untuk asset path resolution)
     */
    public function baseUrl(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        // Naik dari public/index.php ke root erapor/
        $basePath = dirname(dirname($scriptName));
        return rtrim($basePath, '/') . '/';
    }
}
