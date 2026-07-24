<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\KenaikanService;

class KenaikanController extends BaseController
{
    private const ROLES = ['Admin', 'Kepala Madrasah', 'Wali Kelas'];

    public function process(): void
    {
        $this->requireRole(self::ROLES);
        if (!$this->isPost()) $this->redirect('kenaikan_kelas');
        $this->verifyCsrf();

        $kelasAsal = (int) $this->input('kelas_asal');
        $kelasTujuan = (int) $this->input('kelas_tujuan');
        $tahunAjaran = (string) $this->input('tahun_ajaran');
        $action = (string) $this->input('action_type', 'naik');
        $ids = $_POST['siswa_ids'] ?? [];

        try {
            $count = (new KenaikanService($this->db))->process($kelasAsal, $kelasTujuan, $tahunAjaran, $ids, $action);
            $actionLabel = $action === 'lulus' ? 'Kelulusan' : 'Kenaikan Kelas';
            $message = $action === 'lulus'
                ? "Berhasil meluluskan {$count} siswa menjadi Alumni."
                : "Berhasil memindahkan {$count} siswa ke kelas baru.";
            $this->log($actionLabel, $message);
            $this->redirect('kenaikan_kelas', 'success', $message);
        } catch (\Throwable $e) {
            $this->redirect('kenaikan_kelas', 'error', $e->getMessage());
        }
    }
}
