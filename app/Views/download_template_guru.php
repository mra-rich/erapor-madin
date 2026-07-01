<?php
require 'vendor/autoload.php';

$guru_header_raw = ['NO', 'NIP / NIK', 'NAMA_LENGKAP', 'JENIS_KELAMIN (L/P)', 'TEMPAT_LAHIR', 'TANGGAL_LAHIR (YYYY-MM-DD)', 'ALAMAT', 'NO_HP', 'PERAN (Guru/Wali Kelas/Admin)', 'USERNAME (Opsional)'];

$guru_header = [];
foreach ($guru_header_raw as $th) {
    $guru_header[] = '<style bgcolor="#059669" color="#FFFFFF" border="thin"><center><b>' . $th . '</b></center></style>';
}

$guru_data = [$guru_header];

// Tambahkan beberapa baris contoh agar lebih rapi (optional)
$sample_data1 = ['1', '198001012005011003', 'Ahmad Budi Santoso', 'L', 'Jakarta', '1980-01-01', 'Jl. Merdeka No.1', '081234567890', 'Guru', 'ahmadbudi'];
$sample_data2 = ['2', '198502022010022004', 'Siti Aminah', 'P', 'Bandung', '1985-02-02', 'Jl. Sudirman No.5', '089876543210', 'Wali Kelas', 'sitiaminah'];

// Beri border untuk data cell
foreach ([$sample_data1, $sample_data2] as $row) {
    $bordered_row = [];
    foreach ($row as $cell) {
        $bordered_row[] = '<style border="thin">' . $cell . '</style>';
    }
    $guru_data[] = $bordered_row;
}

$xlsx = \Shuchkin\SimpleXLSXGen::fromArray($guru_data);
$xlsx->downloadAs('template_guru.xlsx');
exit;
?>
