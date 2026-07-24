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

        $waliDashboard = $this->emptyWaliDashboard();
        if (($_SESSION['peran'] ?? '') === 'Wali Kelas') {
            $waliDashboard = $this->buildWaliDashboard((int) ($_SESSION['id_pengguna'] ?? 0), $tahunAktif, (int) $semesterAktif);
        }

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

    private function emptyWaliDashboard(): array
    {
        return ['has_class' => false, 'class' => null, 'class_name' => '', 'student_total' => 0,
            'subject_total' => 0, 'subject_complete' => 0, 'evaluation_complete' => 0,
            'report_complete' => 0, 'subjects' => [], 'attention_students' => []];
    }

    private function buildWaliDashboard(int $idWali, string $tahunAktif, int $semesterAktif): array
    {
        $dashboard = $this->emptyWaliDashboard();
        $stmt = $this->db->prepare('SELECT k.*, t.nama_tingkat FROM kelas k LEFT JOIN tingkat_kelas t ON t.id_tingkat = k.id_tingkat WHERE k.id_wali_kelas = ? ORDER BY k.id_kelas ASC LIMIT 1');
        $stmt->bind_param('i', $idWali); $stmt->execute(); $class = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$class) return $dashboard;

        $idKelas = (int) $class['id_kelas'];
        $className = trim($class['nama_kelas'] . (($class['nama_rombel'] ?? '') !== '' && $class['nama_rombel'] !== '-' ? ' ' . $class['nama_rombel'] : '') . (($class['nama_tingkat'] ?? '') !== '' ? ' ' . $class['nama_tingkat'] : ''));
        $dashboard['has_class'] = true; $dashboard['class'] = $class; $dashboard['class_name'] = $className;

        $studentSql = "SELECT COUNT(DISTINCT s.id_siswa) total FROM riwayat_kelas r JOIN siswa s ON s.id_siswa = r.id_siswa WHERE r.id_kelas = ? AND r.tahun_ajaran = ? AND s.status = 'Aktif'";
        $stmt = $this->db->prepare($studentSql); $stmt->bind_param('is', $idKelas, $tahunAktif); $stmt->execute(); $dashboard['student_total'] = (int) $stmt->get_result()->fetch_assoc()['total']; $stmt->close();

        $subjectSql = "SELECT DISTINCT m.id_mapel, m.nama_mapel, p.nama nama_guru FROM pengampu_mapel pm JOIN mata_pelajaran m ON m.id_mapel = pm.id_mapel AND m.status = 'Aktif' LEFT JOIN pengguna p ON p.id_pengguna = pm.id_pengguna WHERE pm.id_kelas = ? AND pm.status = 'Aktif' ORDER BY m.nama_mapel";
        $stmt = $this->db->prepare($subjectSql); $stmt->bind_param('i', $idKelas); $stmt->execute(); $subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        $gradeSql = "SELECT COUNT(DISTINCT n.id_transaksi) total FROM nilai n JOIN transaksi_raport tr ON tr.id_transaksi = n.id_transaksi JOIN riwayat_kelas r ON r.id_siswa = tr.id_siswa JOIN siswa s ON s.id_siswa = tr.id_siswa WHERE n.id_mapel = ? AND r.id_kelas = ? AND r.tahun_ajaran = ? AND tr.tahun_ajaran = ? AND tr.semester = ? AND s.status = 'Aktif'";
        foreach ($subjects as &$subject) {
            $idMapel = (int) $subject['id_mapel']; $stmt = $this->db->prepare($gradeSql); $stmt->bind_param('iissi', $idMapel, $idKelas, $tahunAktif, $tahunAktif, $semesterAktif); $stmt->execute();
            $graded = (int) $stmt->get_result()->fetch_assoc()['total']; $stmt->close(); $total = $dashboard['student_total'];
            $subject['graded_students'] = $graded; $subject['total_students'] = $total; $subject['percentage'] = $total ? (int) round($graded * 100 / $total) : 0;
            $subject['status'] = $total > 0 && $graded >= $total ? 'Lengkap' : ($graded > 0 ? 'Sebagian' : 'Belum');
            if ($subject['status'] === 'Lengkap') $dashboard['subject_complete']++;
        }
        unset($subject); $dashboard['subjects'] = $subjects; $dashboard['subject_total'] = count($subjects);

        $completeSql = "SELECT COUNT(DISTINCT tr.id_siswa) total FROM transaksi_raport tr JOIN riwayat_kelas r ON r.id_siswa = tr.id_siswa JOIN siswa s ON s.id_siswa = tr.id_siswa JOIN kepribadian kp ON kp.id_transaksi = tr.id_transaksi JOIN ekstrakurikuler e ON e.id_transaksi = tr.id_transaksi JOIN catatan_wali_kelas c ON c.id_transaksi = tr.id_transaksi JOIN absensi a ON a.id_transaksi = tr.id_transaksi WHERE r.id_kelas = ? AND r.tahun_ajaran = ? AND tr.tahun_ajaran = ? AND tr.semester = ? AND s.status = 'Aktif'";
        $stmt = $this->db->prepare($completeSql); $stmt->bind_param('issi', $idKelas, $tahunAktif, $tahunAktif, $semesterAktif); $stmt->execute(); $dashboard['evaluation_complete'] = (int) $stmt->get_result()->fetch_assoc()['total']; $stmt->close();
        $reportSql = "SELECT COUNT(DISTINCT tr.id_siswa) total FROM transaksi_raport tr JOIN riwayat_kelas r ON r.id_siswa = tr.id_siswa JOIN siswa s ON s.id_siswa = tr.id_siswa WHERE r.id_kelas = ? AND r.tahun_ajaran = ? AND tr.tahun_ajaran = ? AND tr.semester = ? AND s.status = 'Aktif'";
        $stmt = $this->db->prepare($reportSql); $stmt->bind_param('issi', $idKelas, $tahunAktif, $tahunAktif, $semesterAktif); $stmt->execute(); $dashboard['report_complete'] = (int) $stmt->get_result()->fetch_assoc()['total']; $stmt->close();

        $attentionSql = "SELECT s.id_siswa, s.nama, COALESCE(a.sakit, 0) + COALESCE(a.izin, 0) + COALESCE(a.tanpa_keterangan, 0) absence_total, MAX(CASE WHEN n.nilai_angka < 70 THEN 1 ELSE 0 END) low_grade, MAX(CASE WHEN kp.id_kepribadian IS NULL OR e.id_ekstrakurikuler IS NULL OR c.id_catatan IS NULL OR a.id_absensi IS NULL THEN 1 ELSE 0 END) incomplete FROM riwayat_kelas r JOIN siswa s ON s.id_siswa = r.id_siswa LEFT JOIN transaksi_raport tr ON tr.id_siswa = s.id_siswa AND tr.tahun_ajaran = ? AND tr.semester = ? LEFT JOIN absensi a ON a.id_transaksi = tr.id_transaksi LEFT JOIN kepribadian kp ON kp.id_transaksi = tr.id_transaksi LEFT JOIN ekstrakurikuler e ON e.id_transaksi = tr.id_transaksi LEFT JOIN catatan_wali_kelas c ON c.id_transaksi = tr.id_transaksi LEFT JOIN nilai n ON n.id_transaksi = tr.id_transaksi WHERE r.id_kelas = ? AND r.tahun_ajaran = ? AND s.status = 'Aktif' GROUP BY s.id_siswa, s.nama, a.sakit, a.izin, a.tanpa_keterangan HAVING absence_total >= 5 OR low_grade = 1 OR incomplete = 1 LIMIT 8";
        $stmt = $this->db->prepare($attentionSql); $stmt->bind_param('siis', $tahunAktif, $semesterAktif, $idKelas, $tahunAktif); $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $student) { $reasons = []; if ((int) $student['absence_total'] >= 5) $reasons[] = 'Absensi ≥ 5'; if ((int) $student['low_grade']) $reasons[] = 'Nilai < 70'; if ((int) $student['incomplete']) $reasons[] = 'Evaluasi belum lengkap'; $dashboard['attention_students'][] = ['id_siswa' => (int) $student['id_siswa'], 'nama' => $student['nama'], 'reasons' => $reasons]; }
        $stmt->close(); return $dashboard;
    }

    private function countTable(string $table, string $where = ''): int
    {
        $sql = "SELECT COUNT(*) as total FROM $table";
        if ($where) $sql .= " WHERE $where";
        $result = $this->db->query($sql);
        return (int) ($result->fetch_assoc()['total'] ?? 0);
    }
}
