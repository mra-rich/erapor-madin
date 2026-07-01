<?php

declare(strict_types=1);

namespace App\Services;

/**
 * CsrfService — Mengelola CSRF token untuk proteksi form.
 * 
 * Semua method bersifat static karena CSRF token
 * disimpan di $_SESSION dan tidak memerlukan state internal.
 */
class CsrfService
{
    /**
     * Generate token baru jika belum ada di session.
     * Jika sudah ada, kembalikan token yang tersimpan.
     */
    public static function generate(): string
    {
        if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Verifikasi token yang dikirim user dengan token di session.
     * Menggunakan hash_equals untuk mencegah timing attack.
     */
    public static function verify(string $token): bool
    {
        if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Paksa generate token baru (misal setelah login berhasil).
     * Menggantikan token lama untuk mencegah session fixation.
     */
    public static function regenerate(): string
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        return $_SESSION['csrf_token'];
    }
}
