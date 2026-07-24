<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once dirname(__DIR__, 2) . "/vendor/autoload.php";

restrict_roles(['Admin']);

use Shuchkin\SimpleXLSXGen;

// 1. Ambil semua kelas
$query_kelas = "SELECT k.id_kelas, t.nama_tingkat, k.nama_kelas, k.nama_rombel 
                FROM kelas k 
                JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat 
                ORDER BY FIELD(t.nama_tingkat, 'Ibtida\'iyah', 'Tsanawiyah', 'Aliyah') ASC, CAST(k.nama_kelas AS UNSIGNED) ASC, k.nama_rombel ASC";
$res_kelas = mysqli_query($koneksi, $query_kelas);
$kelas_list = [];
while ($row = mysqli_fetch_assoc($res_kelas)) {
    $kelas_list[] = $row;
}

// 2. Ambil semua master mapel
$query_mapel = "SELECT id_mapel, nama_mapel FROM mata_pelajaran WHERE status = 'Aktif' ORDER BY id_mapel ASC";
$res_mapel = mysqli_query($koneksi, $query_mapel);
$mapel_list = [];
while ($row = mysqli_fetch_assoc($res_mapel)) {
    $mapel_list[] = $row;
}

// 3. Ambil data yang sudah ada di pengampu_mapel
$query_existing = "SELECT pm.id_kelas, pm.id_mapel, pm.nama_kitab, pm.status, p.nama as nama_guru 
                   FROM pengampu_mapel pm 
                   LEFT JOIN pengguna p ON pm.id_guru = p.id_pengguna";
$res_existing = mysqli_query($koneksi, $query_existing);
$existing = [];
while ($row = mysqli_fetch_assoc($res_existing)) {
    $existing[$row['id_kelas'] . '_' . $row['id_mapel']] = $row;
}

// Buat sheet 1: Data Pengaturan Mapel
$header = [
    '<b>ID Kelas</b> (JANGAN DIUBAH)', 
    '<b>Nama Kelas</b>', 
    '<b>ID Mapel</b> (JANGAN DIUBAH)', 
    '<b>Nama Mapel</b>', 
    '<b>Nama Kitab</b>', 
    '<b>Nama Guru</b>',
    '<b>Status</b> (Aktif/Non-Aktif)'
];
$data = [$header];

foreach ($kelas_list as $k) {
    $nama_kelas_full = $k['nama_tingkat'] . ' - ' . $k['nama_kelas'] . ' ' . $k['nama_rombel'];
    foreach ($mapel_list as $m) {
        $key = $k['id_kelas'] . '_' . $m['id_mapel'];
        
        $kitab = '';
        $guru = '';
        $status = '';
        
        if (isset($existing[$key])) {
            $kitab = $existing[$key]['nama_kitab'];
            $guru = $existing[$key]['nama_guru'] ?? '';
            $status = $existing[$key]['status'];
        }
        
        $data[] = [
            $k['id_kelas'],
            $nama_kelas_full,
            $m['id_mapel'],
            $m['nama_mapel'],
            $kitab,
            $guru,
            $status
        ];
    }
}

// Buat sheet 2: Referensi Guru
$header_guru = [
    '<b>Nama Guru</b> (Copy & Paste ke Sheet 1)',
    '<b>Username</b>'
];
$data_guru = [$header_guru];

$query_guru = "SELECT nama, username FROM pengguna WHERE peran IN ('Guru', 'Wali Kelas') AND status = 'Aktif' ORDER BY nama ASC";
$res_guru = mysqli_query($koneksi, $query_guru);
while ($row = mysqli_fetch_assoc($res_guru)) {
    $data_guru[] = [$row['nama'], $row['username']];
}

$xlsx = SimpleXLSXGen::fromArray($data, 'Pengaturan Mapel');
$xlsx->addSheet($data_guru, 'Referensi Guru');

if (ob_get_length()) ob_end_clean();
$xlsx->downloadAs('Template_Pengaturan_Mapel.xlsx');
exit;
