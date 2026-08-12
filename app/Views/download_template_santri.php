<?php
/**
 * Download template import data santri.
 * File Excel statis berada di root proyek (template_santri.xlsx),
 * dilayani lewat endpoint ini agar berfungsi di semua environment
 * (public/ sebagai web root, termasuk Vercel).
 */
$file = dirname(__DIR__, 2) . '/template_santri.xlsx';

if (!file_exists($file)) {
    http_response_code(404);
    die('File template tidak ditemukan.');
}

if (ob_get_length()) ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="template_santri.xlsx"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: no-cache');
readfile($file);
exit;
