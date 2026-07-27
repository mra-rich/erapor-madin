<?php
// Test PDF - ganti type sesuai parameter
$type = $_GET['type'] ?? 'sampul';
$views = [
    'sampul' => 'cetak_sampul.php',
    'identitas' => 'cetak_biodata.php',
    'rapor' => 'preview_rapot.php',
    'semua' => 'cetak_semua.php',
    'leger' => 'preview_leger.php',
];
$viewFile = $views[$type] ?? 'cetak_sampul.php';

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/koneksi.php';

set_include_path(get_include_path()
    . PATH_SEPARATOR . __DIR__ . '/app/Views'
    . PATH_SEPARATOR . __DIR__ . '/app/Controllers'
    . PATH_SEPARATOR . __DIR__ . '/config'
    . PATH_SEPARATOR . __DIR__ . '/app/Models');

function restrict_roles($r) {}
$_SESSION = ['id_pengguna' => 1, 'peran' => 'Admin'];
$_GET['id'] = 1;
$_GET['smt'] = 1;
$_GET['kelas'] = 1;

use Dompdf\Dompdf;
use Dompdf\Options;

ob_start();
chdir(__DIR__ . '/app/Views');
require __DIR__ . '/app/Views/' . $viewFile;
$html = ob_get_clean();

$html = preg_replace('/<script>.*?<\/script>/s', '', $html);
$html = str_replace('</style>', '
    .page { box-shadow: none !important; transform: none !important; background: white !important; min-height: auto !important; margin: 0 !important; padding: 0.5cm !important; }
    body { background: white !important; }
    .no-print { display: none !important; }
    .preview-wrapper { padding: 0 !important; }
    .sampul-container { display: block !important; text-align: center !important; height: auto !important; }
    .sampul-identitas-siswa { display: inline-block !important; text-align: left !important; }
    .logo-placeholder { width: 120px !important; height: 120px !important; margin: 15px auto !important; border: 1px solid #ccc !important; }
    .photo-box { float: left !important; margin-bottom: 20px !important; }
    .signature-box { float: right !important; }
    .clearfix::after { content: "" !important; clear: both !important; display: table !important; }
    .arabic { font-family: "DejaVu Sans", Arial, sans-serif !important; }
</style>', $html);

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Arial');
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

header('Content-Type: application/pdf');
echo $dompdf->output();