<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_VIEW_REPORTS);
require_once dirname(__DIR__, 2) . "/vendor/autoload.php";

use Shuchkin\SimpleXLSXGen;

if (!isset($_GET['kelas'])) {
    die("Kelas tidak dipilih.");
}

$id_kelas = (int)$_GET['kelas'];

// Dapatkan nama kelas
$query_kelas = mysqli_query($koneksi, "SELECT k.nama_kelas, k.nama_rombel, t.nama_tingkat FROM kelas k LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat WHERE k.id_kelas = '$id_kelas'");
$row_k = mysqli_fetch_assoc($query_kelas);
$nama_kelas = ($row_k['nama_kelas'] ?? '') . ' ' . ($row_k['nama_rombel'] ?? '');

// Query mata pelajaran aktif untuk kelas ini
$query_mapel = "SELECT DISTINCT m.id_mapel, m.nama_mapel FROM mata_pelajaran m JOIN pengampu_mapel pm ON m.id_mapel = pm.id_mapel WHERE pm.id_kelas = '$id_kelas' AND pm.status = 'Aktif' ORDER BY m.id_mapel ASC";
$result_mapel = mysqli_query($koneksi, $query_mapel);

$mapel_headers = [];
$mapel_ids = [];
while ($row = mysqli_fetch_assoc($result_mapel)) {
    $mapel_headers[] = "NILAI_" . $row['id_mapel'] . "_" . strtoupper($row['nama_mapel']);
    $mapel_ids[] = $row['id_mapel'];
}

// Header dasar
$headers = [
    'ID_SISWA',
    'NOMOR_SANTRI',
    'NAMA_SANTRI',
    'IZIN',
    'SAKIT',
    'ALPA',
    'KELAKUAN',
    'KERAJINAN',
    'KERAPIAN',
    'CATATAN',
    'PRAMUKA',
    'PMR',
    'PASKIBRA'
];

// Gabungkan dengan header mapel
$headers = array_merge($headers, $mapel_headers);

// Inisialisasi data array untuk Excel
$data_excel = [];

// Format header dengan warna hijau (emerald-600) dan teks putih tebal
$formatted_headers = [];
foreach ($headers as $th) {
    $formatted_headers[] = '<style bgcolor="#059669" color="#FFFFFF" border="thin"><center><b>' . $th . '</b></center></style>';
}
$data_excel[] = $formatted_headers;

// Query siswa di kelas ini
$query_siswa = "SELECT id_siswa, nomor_santri, nama FROM siswa WHERE id_kelas = '$id_kelas' ORDER BY nama ASC";
$result_siswa = mysqli_query($koneksi, $query_siswa);

while ($siswa = mysqli_fetch_assoc($result_siswa)) {
    $row = [
        $siswa['id_siswa'],
        $siswa['nomor_santri'],
        $siswa['nama'],
        '0', // Izin
        '0', // Sakit
        '0', // Alpa
        'A', // Kelakuan
        'A', // Kerajinan
        'A', // Kerapian
        '',  // Catatan
        '',  // Pramuka
        '',  // PMR
        ''   // Paskibra
    ];
    
    // Kosongkan nilai untuk mapel (agar diisi guru)
    foreach ($mapel_ids as $id) {
        $row[] = '0'; // Default nilai
    }
    
    $bordered_row = [];
    foreach ($row as $cell) {
        $bordered_row[] = '<style border="thin">' . $cell . '</style>';
    }
    
    $data_excel[] = $bordered_row;
}

$xlsx = SimpleXLSXGen::fromArray($data_excel);
if (ob_get_length()) ob_end_clean();
$xlsx->downloadAs("Template_Import_Nilai_{$nama_kelas}.xlsx");
exit;
?>
