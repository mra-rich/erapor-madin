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

// Inject base URL supaya Dompdf bisa resolve relative path (uploads/logo...)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
$baseUrl = "$scheme://$host$basePath/";
$html = preg_replace('/<head>/i', '<head><base href="' . $baseUrl . '">' . "\n", $html, 1);

// Hapus script dan CSS dari CDN yang bermasalah (font, icon) karena server read-only
$html = preg_replace('/<script>window\.print\(\);<\/script>/', '', $html);
$html = preg_replace('/@import\s+url\(.*?remixicon.*?\);/i', '', $html);
$html = preg_replace('/<link[^>]*href=["\'].*?remixicon.*?["\'][^>]*>/i', '', $html);
$html = preg_replace('/<link[^>]*href=["\']https:\/\/cdn\.jsdelivr\.net[^>]*>/i', '', $html);
$html = preg_replace('/@font-face\s*\{[^}]*remixicon[^}]*\}/si', '', $html);
$html = preg_replace('/@font-face\s*\{[^}]*cdn\.jsdelivr[^}]*\}/si', '', $html);

// Inject CSS fixes untuk Dompdf (sampul, identitas, rapor, dll)
$dompdfCss = '
    @page { margin: 1.5cm 1.5cm !important; }
    body { background: white !important; margin: 0 !important; padding: 0 !important; font-size: 12pt !important; }
    .page {
        width: auto !important;
        min-height: auto !important;
        height: auto !important;
        margin: 0 !important;
        padding: 1.5cm !important;
        box-shadow: none !important;
        transform: none !important;
        background: white !important;
        box-sizing: border-box !important;
        page-break-after: auto !important;
        position: static !important;
    }
    .page:not(:last-child) { page-break-after: always !important; }
    .no-print { display: none !important; }
    .preview-wrapper { padding: 0 !important; overflow: visible !important; }
    
    /* Fix centering sampul */
    .sampul-container { display: block !important; text-align: center !important; padding-top: 20px !important; height: auto !important; }
    .sampul-identitas-siswa, .identitas-siswa { display: inline-block !important; text-align: left !important; }
    .identitas-madrasah { text-align: center !important; }
    .logo-placeholder { display: block !important; margin: 40px auto !important; line-height: 150px !important; text-align: center !important; }
    
    /* Fix biodata */
    .table-biodata { width: 100% !important; }
    .table-biodata td { padding: 6px 4px !important; vertical-align: top !important; font-size: 12pt !important; }
    .isian { font-weight: bold !important; }
    .photo-box { float: left !important; margin-bottom: 20px !important; margin-top: 20px !important; }
    .signature-box { float: right !important; margin-top: 20px !important; }
    .ttd-section { margin-top: 30px !important; }
    
    /* Fix rapor */
    .rapor-table { width: 100% !important; border-collapse: collapse !important; }
    .rapor-table td, .rapor-table th { padding: 5px 4px !important; border: 1px solid black !important; text-align: center !important; font-size: 11pt !important; }
    .rapor-table th { background-color: #f2f2f2 !important; }
    .rapor-header-info { border: 1px solid black !important; padding: 10px !important; margin-bottom: 10px !important; }
    .rapor-info-table { width: 100% !important; border-collapse: collapse !important; }
    .rapor-info-table td { padding: 3px !important; border: none !important; font-size: 11pt !important; }
    .rapor-info-label { font-weight: bold !important; white-space: nowrap !important; width: 120px !important; }
    
    /* Fix footer rapor: ganti grid jadi table biar kompatibel Dompdf */
    .rapor-footer { width: 100% !important; margin-top: 15px !important; display: table !important; }
    .rapor-footer-table { width: 100% !important; border-collapse: collapse !important; }
    .rapor-footer-table td { width: 32% !important; border: none !important; vertical-align: top !important; padding: 0 5px !important; }
    .rapor-footer-table table { width: 100% !important; border-collapse: collapse !important; }
    .rapor-footer-table th, .rapor-footer-table td.inner { border: 1px solid black !important; padding: 4px !important; text-align: center !important; font-size: 11pt !important; }
    
    /* Fix arabic font */
    .arabic { font-family: "DejaVu Sans", Arial, sans-serif !important; }
    
    /* Fix leger */
    th[style*="writing-mode"] { writing-mode: horizontal-tb !important; transform: none !important; height: auto !important; }
    
    /* Fix print media */
    @media print {
        body { background: white; margin: 0; padding: 0; }
        .page { margin: 0; box-shadow: none; padding: 1.5cm; }
        .no-print { display: none; }
    }
';

// Sisipkan CSS — inject setelah <style> atau sebelum </head>
if (strpos($html, '</style>') !== false) {
    $html = str_replace('</style>', $dompdfCss . '</style>', $html);
} else {
    $html = str_replace('</head>', '<style>' . $dompdfCss . '</style></head>', $html);
}

// Konversi gambar "uploads/..." menjadi base64 data URI.
// Ini menghindari dompdf mengambil logo via HTTP (yang sering gagal di Vercel/serverless).
$uploadsDirs = [$base . '/public/uploads', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . '/uploads'];
$html = preg_replace_callback(
    '/(<img\b[^>]*?\bsrc=["\'])(uploads\/[^"\']+)(["\'])/i',
    function ($m) use ($uploadsDirs) {
        $rel = $m[2];
        foreach ($uploadsDirs as $dir) {
            $file = $dir . '/' . substr($rel, strlen('uploads/'));
            if (is_file($file)) {
                $mime = mime_content_type($file) ?: 'image/png';
                $data = base64_encode(file_get_contents($file));
                return $m[1] . 'data:' . $mime . ';base64,' . $data . $m[3];
            }
        }
        return $m[0]; // biarkan apa adanya jika file tidak ditemukan
    },
    $html
);

// Options PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('isFontSubsettingEnabled', true);
	$options->set('fontDir', sys_get_temp_dir());
	$options->set('fontCache', sys_get_temp_dir());
	$options->set('tempDir', sys_get_temp_dir());
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output inline (browser PDF viewer, bukan download)
$dompdf->stream("Dokumen.pdf", ["Attachment" => 0]);