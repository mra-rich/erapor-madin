<?php
if (isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $filepath = __DIR__ . '/' . $file;
    
    // Pastikan file yang diminta valid dan berekstensi xlsx (keamanan sederhana)
    if (file_exists($filepath) && pathinfo($filepath, PATHINFO_EXTENSION) === 'xlsx') {
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        http_response_code(404);
        echo "File tidak ditemukan.";
    }
}
?>
