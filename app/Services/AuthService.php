<?php

declare(strict_types=1);

namespace App\Services;

/**
 * AuthService — Mengelola autentikasi dan otorisasi pengguna.
 * 
 * Semua method bersifat static. Data login disimpan di $_SESSION.
 * Brute-force protection menggunakan tabel login_attempts.
 */
class AuthService
{
    /** Jumlah maksimal percobaan login sebelum dikunci */
    private const MAX_ATTEMPTS = 5;

    /** Durasi penguncian dalam menit */
    private const LOCKOUT_MINUTES = 15;

    /**
     * Cek apakah user sudah login.
     */
    public static function check(): bool
    {
        return isset($_SESSION['id_pengguna']) && !empty($_SESSION['id_pengguna']);
    }

    /**
     * Ambil data user dari session.
     * Mengembalikan null jika belum login.
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id_pengguna' => $_SESSION['id_pengguna'],
            'nama'        => $_SESSION['nama'] ?? '',
            'username'    => $_SESSION['username'] ?? '',
            'peran'       => $_SESSION['peran'] ?? '',
        ];
    }

    /**
     * Cek apakah user memiliki salah satu role yang diberikan.
     * Menerima string tunggal atau array role.
     */
    public static function hasRole(string|array $roles): bool
    {
        if (!self::check()) {
            return false;
        }

        $peran = $_SESSION['peran'] ?? '';

        if (is_string($roles)) {
            return $peran === $roles;
        }

        return in_array($peran, $roles, true);
    }

    /**
     * Set session keys untuk login.
     * Data minimal: id_pengguna, nama, username, peran.
     */
    public static function login(array $userData): void
    {
        // Regenerasi session ID untuk mencegah session fixation
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['id_pengguna'] = $userData['id_pengguna'] ?? null;
        $_SESSION['nama']        = $userData['nama'] ?? '';
        $_SESSION['username']    = $userData['username'] ?? '';
        $_SESSION['peran']       = $userData['peran'] ?? '';
    }

    /**
     * Logout: hapus semua data session dan destroy session.
     */
    public static function logout(): void
    {
        $_SESSION = [];

        // Hapus cookie session jika ada
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Cek apakah username terkunci karena terlalu banyak percobaan login gagal.
     * Terkunci jika >= MAX_ATTEMPTS dalam LOCKOUT_MINUTES menit terakhir.
     */
    public static function checkBruteForce(string $username, \mysqli $db): bool
    {
        $stmt = $db->prepare(
            "SELECT COUNT(*) AS total 
             FROM login_attempts 
             WHERE username = ? 
               AND timestamp > DATE_SUB(NOW(), INTERVAL ? MINUTE)"
        );

        $lockoutMinutes = self::LOCKOUT_MINUTES;
        $stmt->bind_param('si', $username, $lockoutMinutes);
        $stmt->execute();

        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();
        $stmt->close();

        return ($row['total'] ?? 0) >= self::MAX_ATTEMPTS;
    }

    /**
     * Catat percobaan login gagal ke tabel login_attempts.
     */
    public static function recordFailedLogin(string $username, string $ip, \mysqli $db): void
    {
        $stmt = $db->prepare(
            "INSERT INTO login_attempts (username, ip_address, timestamp) 
             VALUES (?, ?, NOW())"
        );

        $stmt->bind_param('ss', $username, $ip);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Hapus semua catatan percobaan login gagal untuk username tertentu.
     * Dipanggil setelah login berhasil.
     */
    public static function clearLoginAttempts(string $username, \mysqli $db): void
    {
        $stmt = $db->prepare(
            "DELETE FROM login_attempts WHERE username = ?"
        );

        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->close();
    }
}
