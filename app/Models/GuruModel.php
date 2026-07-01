<?php
namespace App\Models;

use App\Core\BaseModel;

/**
 * Model untuk tabel guru
 * Mengelola data guru beserta relasi ke tabel pengguna
 */
class GuruModel extends BaseModel
{
    protected string $table = 'guru';
    protected string $primaryKey = 'id_guru';

    /**
     * Cari guru berdasarkan id_pengguna (FK)
     */
    public function findByPenggunaId(int $idPengguna): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE id_pengguna = ? LIMIT 1"
        );
        $stmt->bind_param('i', $idPengguna);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    /**
     * Ambil semua guru dengan data pengguna (JOIN)
     * Filter hanya pengguna aktif (status != 'Dihapus')
     * Mendukung pencarian dan pagination
     */
    public function findAllWithPengguna(string $search = '', int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT g.*, p.username, p.peran, p.status
                FROM {$this->table} g
                LEFT JOIN pengguna p ON g.id_pengguna = p.id_pengguna
                WHERE p.status != 'Dihapus'";

        $types = '';
        $params = [];

        if ($search !== '') {
            $sql .= " AND (g.nama_lengkap LIKE ? OR g.nip LIKE ? OR p.username LIKE ?)";
            $types .= 'sss';
            $likeSearch = "%{$search}%";
            $params[] = $likeSearch;
            $params[] = $likeSearch;
            $params[] = $likeSearch;
        }

        $sql .= " ORDER BY g.nama_lengkap ASC";

        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $types .= 'ii';
            $params[] = $limit;
            $params[] = $offset;
        }

        $stmt = $this->db->prepare($sql);
        if ($types !== '' && $params !== []) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();

        return $rows;
    }

    /**
     * Hitung total guru (dengan filter pencarian) untuk pagination
     */
    public function countWithPengguna(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM {$this->table} g
                LEFT JOIN pengguna p ON g.id_pengguna = p.id_pengguna
                WHERE p.status != 'Dihapus'";

        $types = '';
        $params = [];

        if ($search !== '') {
            $sql .= " AND (g.nama_lengkap LIKE ? OR g.nip LIKE ? OR p.username LIKE ?)";
            $types .= 'sss';
            $likeSearch = "%{$search}%";
            $params[] = $likeSearch;
            $params[] = $likeSearch;
            $params[] = $likeSearch;
        }

        $stmt = $this->db->prepare($sql);
        if ($types !== '' && $params !== []) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int)($row['total'] ?? 0);
    }
}
