<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\NotifikasiModel;

/**
 * DashboardController
 * Menangani rendering dashboard dengan data statistik.
 * Menggantikan logika query di dashboard.php (view)
 */
class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        // Ambil statistik
        $siswa = $this->countTable('siswa', "status = 'Aktif'");
        $kelas = $this->countTable('kelas');
        $tahunAktif = $_SESSION['tahun_ajaran'] ?? '2024/2025';
        $semesterAktif = $_SESSION['semester'] ?? 1;

        // Prepared statement untuk data dari session (anti SQL injection)
        $stmtRapot = $this->db->prepare("SELECT COUNT(*) as total FROM transaksi_raport WHERE tahun_ajaran = ? AND semester = ?");
        $stmtRapot->bind_param('si', $tahunAktif, $semesterAktif);
        $stmtRapot->execute();
        $rapotTahun = (int) $stmtRapot->get_result()->fetch_assoc()['total'];
        $stmtRapot->close();

        $rapotSemua = $this->countTable('transaksi_raport');
        $waliKelas = $this->countTable('pengguna', "peran = 'Wali Kelas' AND status = 'Aktif'");

        // Ambil alert notifikasi (dipindahkan dari navbar.php)
        $notifModel = new NotifikasiModel($this->db);
        $idWali = null;
        if (($_SESSION['peran'] ?? '') === 'Wali Kelas') {
            $idWali = (int) $_SESSION['id_pengguna'];
        }
        $alerts = $notifModel->getAlertKelasKurang($idWali);
        $total_alerts = count($alerts);

        // Render view legacy — variabel di atas tersedia di view via extract atau langsung
        $koneksi = $this->db;
        require_once 'csrf.php';
        include 'include/header.php';
        include 'include/navbar.php';
        include 'include/sidebar.php';

        // Inline render dashboard content (agar variabel tersedia)
        require dirname(__DIR__) . '/Views/dashboard_content.php';

        include 'include/footer.php';
    }

    private function countTable(string $table, string $where = ''): int
    {
        $sql = "SELECT COUNT(*) as total FROM $table";
        if ($where) $sql .= " WHERE $where";
        $result = $this->db->query($sql);
        return (int) ($result->fetch_assoc()['total'] ?? 0);
    }
}
