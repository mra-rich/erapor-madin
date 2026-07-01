<?php
require 'vendor/autoload.php';

$santri_header_raw = [
    'No', 
    'NISN', 
    'No.Induk', 
    'Nama Siswa', 
    'Tempat Lahir', 
    'Tanggal Lahir (YYYY-MM-DD)', 
    'Jenis Kelamin (L/P)', 
    'Status Dalam Keluarga', 
    'Anak Ke', 
    'Alamat Siswa', 
    'No. Telpon', 
    'Kelas',
    'Nama Ayah', 
    'Pekerjaan Ayah', 
    'Nama Ibu', 
    'Pekerjaan Ibu', 
    'Nama Wali', 
    'Pekerjaan Wali'
];

$santri_header = [];
foreach ($santri_header_raw as $th) {
    $santri_header[] = '<style bgcolor="#059669" color="#FFFFFF" border="thin"><center><b>' . $th . '</b></center></style>';
}

$santri_data = [$santri_header];

// Data Dummy
$dummy_data = [
    ['1', '1234567890', '1001', 'Ahmad Rizky', 'Banyumas', '2010-05-15', 'L', 'Anak Kandung', '1', 'Jl. Masjid No 10', '081234567890', '1A', 'Budi Santoso', 'Wiraswasta', 'Siti Aminah', 'Ibu Rumah Tangga', '', ''],
    ['2', '0987654321', '1002', 'Budi Santoso', 'Purwokerto', '2011-02-20', 'L', 'Anak Kandung', '2', 'Jl. Merdeka No 5', '089876543210', '1A', 'Suharto', 'PNS', 'Ratna', 'Guru', '', ''],
    ['3', '1122334455', '1003', 'Citra Kirana', 'Cilacap', '2010-11-10', 'P', 'Anak Kandung', '1', 'Jl. Laut No 2', '085678901234', '1B', 'Budi Hartono', 'TNI', 'Wati', 'Perawat', '', '']
];

foreach ($dummy_data as $row) {
    $bordered_row = [];
    foreach ($row as $cell) {
        $bordered_row[] = '<style border="thin">' . $cell . '</style>';
    }
    $santri_data[] = $bordered_row;
}

$xlsx = \Shuchkin\SimpleXLSXGen::fromArray($santri_data);
$xlsx->saveAs('template_santri.xlsx');
echo "Template Santri recreated successfully.";
?>
