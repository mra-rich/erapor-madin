<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\NilaiService;

/** Controller nilai rapor. */
class NilaiController extends BaseController
{
    private const RBAC_MANAGE_GRADES = ['Admin', 'Wali Kelas', 'Guru'];

    public function store(): void
    {
        $this->requireRole(self::RBAC_MANAGE_GRADES);

        if (!$this->isPost()) {
            $this->redirect('data_nilai');
        }

        $this->verifyCsrf();

        try {
            $service = new NilaiService($this->db);
            $idTransaksi = $service->create($_POST, (int) $_SESSION['id_pengguna']);
            $this->log('Input Nilai', "Menyimpan transaksi nilai ID: {$idTransaksi}");
            $this->redirect('data_nilai', 'sukses', 'Data nilai berhasil disimpan.');
        } catch (\Throwable $e) {
            $this->redirect('data_nilai', 'gagal', 'Gagal menyimpan nilai: ' . $e->getMessage());
        }
    }
}
