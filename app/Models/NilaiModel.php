<?php
namespace App\Models;

use App\Core\BaseModel;

/**
 * Model untuk tabel transaksi_raport (nilai rapor)
 * Mengelola data nilai siswa per mata pelajaran dan periode
 */
class NilaiModel extends BaseModel
{
    protected string $table = 'transaksi_raport';
    protected string $primaryKey = 'id_transaksi';

    /**
     * Ambil semua nilai siswa pada periode tertentu
     * JOIN dengan mata_pelajaran, diurutkan berdasarkan id_mapel ASC (urutan mutlak)
     */
    public function findBySiswaAndPeriode(int $idSiswa, string $tahunAjaran, int $semester): array
    {
        $sql = "SELECT tr.*, m.nama_mapel
                FROM {$this->table} tr
                JOIN mata_pelajaran m ON tr.id_mapel = m.id_mapel
                WHERE tr.id_siswa = ?
                  AND tr.tahun_ajaran = ?
                  AND tr.semester = ?
                ORDER BY m.id_mapel ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('isi', $idSiswa, $tahunAjaran, $semester);
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
     * Hitung jumlah siswa unik yang sudah memiliki nilai di kelas dan tahun ajaran tertentu
     * Digunakan untuk mengecek progres input nilai per kelas
     */
    public function countByKelas(int $idKelas, string $tahunAjaran): int
    {
        $sql = "SELECT COUNT(DISTINCT tr.id_siswa) AS total
                FROM {$this->table} tr
                JOIN siswa s ON tr.id_siswa = s.id_siswa
                WHERE s.id_kelas = ?
                  AND s.status = 'Aktif'
                  AND tr.tahun_ajaran = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('is', $idKelas, $tahunAjaran);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int)($row['total'] ?? 0);
    }
}
