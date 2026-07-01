<?php

declare(strict_types=1);

namespace App\Services;

/**
 * LoggerService — Mencatat log aktivitas pengguna ke database.
 * 
 * Menggunakan tabel log_aktivitas dengan kolom:
 * id (PK auto_increment), id_pengguna, aksi, detail, timestamp.
 * PENTING: Jangan gunakan kolom id_log!
 */
class LoggerService
{
    /**
     * Catat aktivitas pengguna ke tabel log_aktivitas.
     *
     * @param \mysqli  $db         Koneksi database
     * @param int|null $idPengguna ID pengguna yang melakukan aksi (null jika guest/system)
     * @param string   $aksi       Deskripsi singkat aksi (misal: 'login', 'tambah_siswa')
     * @param string   $detail     Detail tambahan (opsional)
     */
    public static function log(\mysqli $db, ?int $idPengguna, string $aksi, string $detail = ''): void
    {
        $stmt = $db->prepare(
            "INSERT INTO log_aktivitas (id_pengguna, aksi, detail) 
             VALUES (?, ?, ?)"
        );

        $stmt->bind_param('iss', $idPengguna, $aksi, $detail);
        $stmt->execute();
        $stmt->close();
    }
}
