<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_VIEW_ALL);

$kelasFilter = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$semesterFilter = isset($_GET['semester']) ? $_GET['semester'] : '';

// Kondisi query
$whereClause = "WHERE 1=1";
if ($kelasFilter) {
    $whereClause .= " AND s.id_kelas = '" . mysqli_real_escape_string($koneksi, $kelasFilter) . "'";
} else if ($_SESSION['peran'] === 'Wali Kelas') {
    $id_pengguna = $_SESSION['id_pengguna'];
    $whereClause .= " AND k.id_wali_kelas = '$id_pengguna'";
}

if ($semesterFilter) {
    $whereClause .= " AND t.semester = '" . mysqli_real_escape_string($koneksi, $semesterFilter) . "'";
}

// Nama file Excel
$filename = "Export_Data_Nilai_" . date('Y-m-d') . ".xls";

// Set header untuk download Excel (disamarkan dengan format HTML table)
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Ambil daftar mata pelajaran (kolom dinamis)
$queryMapel = "SELECT id_mapel, nama_mapel FROM mata_pelajaran ORDER BY id_mapel ASC";
if ($kelasFilter) {
    $queryMapel = "SELECT id_mapel, nama_mapel FROM mata_pelajaran WHERE id_kelas = '" . mysqli_real_escape_string($koneksi, $kelasFilter) . "' ORDER BY id_mapel ASC";
}
$resultMapel = mysqli_query($koneksi, $queryMapel);
$mapelList = [];
while ($row = mysqli_fetch_assoc($resultMapel)) {
    $mapelList[] = $row;
}
$q_ta = mysqli_query($koneksi, "SELECT tahun_ajaran FROM pengaturan LIMIT 1");
$ta_aktif = mysqli_fetch_assoc($q_ta)['tahun_ajaran'];

$reportData = [];

// Data Santri, Nilai (GROUP_CONCAT), dan Agregasi (SUM)
$query = "SELECT 
            s.id_siswa, s.nama, s.nomor_santri, k.nama_kelas,
            t.id_transaksi, t.tahun_ajaran, t.semester,
            a.sakit, a.izin, a.tanpa_keterangan,
            kep.kelakuan, kep.kerajinan, kep.kerapian,
            c.catatan,
            COALESCE(SUM(n.nilai_angka), 0) as total_nilai,
            GROUP_CONCAT(CONCAT(n.id_mapel, ':', n.nilai_angka) SEPARATOR ',') as daftar_nilai
          FROM riwayat_kelas r
          JOIN siswa s ON r.id_siswa = s.id_siswa
          JOIN kelas k ON r.id_kelas = k.id_kelas
          LEFT JOIN transaksi_raport t ON s.id_siswa = t.id_siswa AND t.tahun_ajaran = r.tahun_ajaran
          LEFT JOIN absensi a ON t.id_transaksi = a.id_transaksi
          LEFT JOIN kepribadian kep ON t.id_transaksi = kep.id_transaksi
          LEFT JOIN catatan_wali_kelas c ON t.id_transaksi = c.id_transaksi
          LEFT JOIN nilai n ON t.id_transaksi = n.id_transaksi
          $whereClause AND r.tahun_ajaran = '$ta_aktif'
          GROUP BY s.id_siswa, t.id_transaksi
          ORDER BY k.nama_kelas, s.nama";

$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Ambil nilai mapel dari string GROUP_CONCAT
        $nilaiMapelData = [];
        if (!empty($row['daftar_nilai'])) {
            $pairs = explode(',', $row['daftar_nilai']);
            foreach ($pairs as $pair) {
                $parts = explode(':', $pair);
                if (count($parts) === 2) {
                    $nilaiMapelData[$parts[0]] = $parts[1];
                }
            }
        }

        // Kalkulasi ulang jumlah mapel aktual yang dinilai (> 0)
        // (Sesuai dengan original logic export_nilai agar rata-rata akurat)
        $totalNilai = $row['total_nilai'];
        $jumlahMapel = 0;
        foreach ($mapelList as $mapel) {
            $nilai = isset($nilaiMapelData[$mapel['id_mapel']]) ? $nilaiMapelData[$mapel['id_mapel']] : 0;
            if ($nilai > 0) $jumlahMapel++;
        }
        $rataRata = $jumlahMapel > 0 ? round($totalNilai / $jumlahMapel, 2) : 0;

        // Susun data untuk view
        $row['nilai'] = $nilaiMapelData;
        $row['total_nilai'] = $totalNilai;
        $row['rata_rata'] = $rataRata;
        $row['sakit'] = $row['sakit'] ?? 0;
        $row['izin'] = $row['izin'] ?? 0;
        $row['tanpa_keterangan'] = $row['tanpa_keterangan'] ?? 0;
        $row['kelakuan'] = $row['kelakuan'] ?? '-';
        $row['kerajinan'] = $row['kerajinan'] ?? '-';
        $row['kerapian'] = $row['kerapian'] ?? '-';
        $row['catatan'] = $row['catatan'] ?? '-';

        $reportData[] = $row;
    }
}

// Render View
include 'views/export_nilai_view.php';
exit;
