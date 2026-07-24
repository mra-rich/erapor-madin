<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\NilaiImportService;

class NilaiImportController extends BaseController
{
    private const ROLES = ['Admin', 'Kepala Madrasah', 'Wali Kelas'];

    public function import(): void
    {
        require_once dirname(__DIR__, 2) . "/vendor/autoload.php";
        $this->requireRole(self::ROLES);
        if (!$this->isPost()) $this->redirect('import_nilai');
        $this->verifyCsrf();

        if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
            $this->redirect('import_nilai', 'gagal', 'Terjadi kesalahan saat mengunggah file.');
        }

        try {
            $count = (new NilaiImportService($this->db))->import(
                $_FILES['file_excel']['tmp_name'],
                (int) $this->input('semester'),
                (string) ($_SESSION['tahun_ajaran'] ?? ''),
                (int) $_SESSION['id_pengguna']
            );
            $message = "Berhasil mengimpor {$count} data siswa.";
            $this->log('Import Nilai', $message);
            $_SESSION['import_status'] = 'sukses';
            $_SESSION['import_msg'] = $message;
            $this->redirect('import_nilai');
        } catch (\Throwable $e) {
            $_SESSION['import_status'] = 'gagal';
            $_SESSION['import_msg'] = $e->getMessage();
            $this->redirect('import_nilai');
        }
    }
}
