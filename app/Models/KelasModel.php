<?php
namespace App\Models;

use App\Core\BaseModel;

/**
 * Model untuk tabel kelas
 * Mengelola data kelas beserta relasi tingkat dan wali kelas
 */
class KelasModel extends BaseModel
{
    protected string $table = 'kelas';
    protected string $primaryKey = 'id_kelas';

    /**
     * Ambil semua kelas beserta nama tingkat dan data wali kelas
     * Diurutkan berdasarkan tingkat kemudian nama kelas
     */
    public function findAllWithTingkat(): array
    {
        $sql = "SELECT k.*, t.nama_tingkat, p.nama AS nama_wali
                FROM {$this->table} k
                LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat
                LEFT JOIN pengguna p ON k.id_wali_kelas = p.id_pengguna
                ORDER BY t.nama_tingkat ASC, k.nama_kelas ASC";

        $result = $this->db->query($sql);

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Ambil semua kelas berdasarkan tingkat tertentu
     */
    public function findByTingkat(int $idTingkat): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE id_tingkat = ? ORDER BY nama_kelas ASC"
        );
        $stmt->bind_param('i', $idTingkat);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();

        return $rows;
    }
}
