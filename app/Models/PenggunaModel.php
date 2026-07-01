<?php
namespace App\Models;

use App\Core\BaseModel;

/**
 * Model untuk tabel pengguna
 * Mengelola data akun pengguna (login, password, status)
 */
class PenggunaModel extends BaseModel
{
    protected string $table = 'pengguna';
    protected string $primaryKey = 'id_pengguna';

    /**
     * Cari pengguna berdasarkan username
     */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE username = ? LIMIT 1"
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    /**
     * Soft-delete: ubah status menjadi 'Dihapus' (bukan DELETE fisik)
     */
    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET status = 'Dihapus' WHERE {$this->primaryKey} = ?"
        );
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Ubah password pengguna (menerima hash yang sudah di-hash sebelumnya)
     */
    public function changePassword(int $id, string $hashedPassword): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET password = ? WHERE {$this->primaryKey} = ?"
        );
        $stmt->bind_param('si', $hashedPassword, $id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Cek apakah username sudah terpakai
     * Parameter $excludeId digunakan saat update agar tidak bentrok dengan data sendiri
     */
    public function isUsernameExists(string $username, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) AS total FROM {$this->table} WHERE username = ? AND {$this->primaryKey} != ?"
            );
            $stmt->bind_param('si', $username, $excludeId);
        } else {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) AS total FROM {$this->table} WHERE username = ?"
            );
            $stmt->bind_param('s', $username);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int)($row['total'] ?? 0) > 0;
    }
}
