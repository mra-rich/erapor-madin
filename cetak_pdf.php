<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;

$dompdf = new Dompdf();
ob_start();
?>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Belajar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
    </style>
</head>

<body>
    <?php
    ob_start();
    include 'cetak_rapot.php';
    $full_content = ob_get_clean();
    preg_match('/<div class="container" id="laporan-container">(.*?)<\/div>/s', $full_content, $matches);
    echo $matches[0]; // Hanya cetak div.container
    ?>
</body>

</html>
<?php
$html = ob_get_clean();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Laporan_Hasil_Belajar.pdf", ["Attachment" => 1]);
?>