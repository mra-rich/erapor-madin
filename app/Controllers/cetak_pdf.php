<?php
require_once dirname(__DIR__, 2) . "/vendor/autoload.php";
$base = dirname(__DIR__, 2);
require_once $base . "/config/koneksi.php";
require_once $base . "/app/Controllers/cek_sesi.php";
restrict_roles(RBAC_VIEW_REPORTS);

use Dompdf\Dompdf;
use Dompdf\Options;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$semester = isset($_GET['smt']) ? (int)$_GET['smt'] : 1;
$kelas = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'rapor';

if (!$id && !$kelas) {
    die("ID Siswa atau Kelas tidak ditemukan.");
}

// Pilih view berdasarkan type
$viewFile = '';
switch ($type) {
    case 'sampul':
        $viewFile = '/cetak_sampul.php';
        break;
    case 'identitas':
        $viewFile = '/cetak_biodata.php';
        break;
    case 'semua':
        $viewFile = '/cetak_semua.php';
        break;
    case 'leger':
        $viewFile = '/preview_leger.php';
        break;
    case 'rapor':
    default:
        $viewFile = '/preview_rapot.php';
        break;
}

// Capture output dari view
$_GET['id'] = $id;
$_GET['smt'] = $semester;
if ($kelas) $_GET['kelas'] = $kelas;

ob_start();
$viewsPath = $base . "/app/Views";
chdir($viewsPath);
include $viewsPath . $viewFile;
$html = ob_get_clean();

// Hapus script window.print() dari HTML karena akan mengganggu PDF
$html = preg_replace('/<script>window\.print\(\);<\/script>/', '', $html);

// Options PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output inline (browser PDF viewer, bukan download)
$dompdf->stream("Dokumen.pdf", ["Attachment" => 0]);