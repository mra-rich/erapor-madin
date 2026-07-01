<?php
namespace App\Models;

/**
 * Model Notifikasi (non-CRUD)
 * Tidak extends BaseModel karena hanya menjalankan query peringatan khusus.
 * Query diambil dari legacy navbar.php dan direfaktor ke prepared statements.
 */
class NotifikasiModel
{
    private \mysqli $db;

    public function __construct(\mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Ambil daftar kelas yang siswa-nya belum lengkap dinilai
     *
     * Jika $idWaliKelas diberikan, filter hanya kelas milik wali tersebut.
     * Return array dengan keys: nama_kelas, total_siswa, total_dinilai
     */
    public function getAlertKelasKurang(?int $idWaliKelas = null): array
    {
        $sql = "SELECT k.nama_kelas,
                    (SELECT COUNT(*)
                     FROM siswa s
                     WHERE s.id_kelas = k.id_kelas
                       AND s.status = 'Aktif') AS total_siswa,
                    (SELECT COUNT(DISTINCT t.id_siswa)
                     FROM transaksi_raport t
                     JOIN siswa s ON t.id_siswa = s.id_siswa
                     WHERE s.id_kelas = k.id_kelas
                       AND s.status = 'Aktif') AS total_dinilai
                FROM kelas k";

        $types = '';
        $params = [];

        if ($idWaliKelas !== null) {
            $sql .= " WHERE k.id_wali_kelas = ?";
            $types .= 'i';
            $params[] = $idWaliKelas;
        }

        $sql .= " HAVING total_siswa > 0 AND total_dinilai < total_siswa";

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
}
