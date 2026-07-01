<?php
require 'vendor/autoload.php';

$kelas_header_raw = ['TINGKATAN (Aliyah/Tsanawiyah/Ibtida\'iyah)', 'KELAS (Angka: 1/2/3)', 'ROMBEL (A/B/C atau -)', 'WALI_KELAS (NIP/NIK atau Nama)'];

$kelas_header = [];
foreach ($kelas_header_raw as $th) {
    $kelas_header[] = '<style bgcolor="#059669" color="#FFFFFF" border="thin"><center><b>' . $th . '</b></center></style>';
}

$kelas_data = [$kelas_header];

// Tambahkan beberapa baris contoh agar lebih rapi (optional)
$sample_data1 = ['Aliyah', '3', '-', 'Ahmad Budi Santoso'];
$sample_data2 = ['Tsanawiyah', '1', 'A', '198502022010022004'];

// Beri border untuk data cell
foreach ([$sample_data1, $sample_data2] as $row) {
    $bordered_row = [];
    foreach ($row as $cell) {
        $bordered_row[] = '<style border="thin">' . $cell . '</style>';
    }
    $kelas_data[] = $bordered_row;
}

$xlsx = \Shuchkin\SimpleXLSXGen::fromArray($kelas_data);
$xlsx->downloadAs('template_kelas.xlsx');
exit;
?>
