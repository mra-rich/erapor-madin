<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\CsrfService;

/**
 * HapusNilaiController
 * Menghapus data nilai dari 5 tabel terkait secara transaksional.
 * Menggantikan logika DELETE yang sebelumnya tertanam di Views/data_nilai.php.
 */
class HapusNilaiController extends BaseController
{
    /** Tabel yang harus dihapus ketika menghapus satu transaksi rapor */
    private const TABEL_TERKAIT = [
        'nilai',
        'absensi',
        'kepribadian',
        'catatan_wali_kelas',
        'ekstrakurikuler',
        'transaksi_raport',
    ];

    /**
     * GET: Hapus data nilai berdasarkan id_transaksi
     */
    public function delete(): void
    {
        $this->requireRole(['Admin', 'Wali Kelas', 'Guru']);
        $this->verifyCsrf();

        $idTransaksi = (int) ($this->input('hapus') ?? 0);

        if ($idTransaksi <= 0) {
            $this->redirect('data_nilai', 'error', 'ID Transaksi tidak valid.');
        }

        // Cek apakah transaksi ada
        $stmt = $this->db->prepare(
            "SELECT id_transaksi FROM transaksi_raport WHERE id_transaksi = ?"
        );
        $stmt->bind_param('i', $idTransaksi);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$exists) {
            $this->redirect('data_nilai', 'error', 'Data transaksi tidak ditemukan.');
        }

        // Hapus dari semua tabel terkait dalam satu transaksi
        $this->db->begin_transaction();

        try {
            foreach (self::TABEL_TERKAIT as $tabel) {
                $stmt = $this->db->prepare(
                    "DELETE FROM {$tabel} WHERE id_transaksi = ?"
                );
                $stmt->bind_param('i', $idTransaksi);
                $stmt->execute();
                $stmt->close();
            }

            // Catat log aktivitas (standar AGENTS.md)
            $this->log(
                'Hapus Data Nilai',
                "Menghapus data nilai (ID Transaksi: {$idTransaksi})"
            );

            $this->db->commit();
            $this->redirect('data_nilai', 'sukses', 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->redirect('data_nilai', 'gagal', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
