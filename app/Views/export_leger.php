<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_VIEW_REPORTS);

if (!isset($_REQUEST['kelas']) || !isset($_REQUEST['semester'])) {
    die("Data tidak lengkap.");
}

$selectedKelas = $_REQUEST['kelas'];
$selectedSemester = $_REQUEST['semester'];
$tahun_ajaran = $_SESSION['tahun_ajaran'];

// Ambil nama kelas
$query_kelas = mysqli_query($koneksi, "SELECT nama_kelas FROM kelas WHERE id_kelas = '$selectedKelas'");
$nama_kelas = mysqli_fetch_assoc($query_kelas)['nama_kelas'] ?? 'Kelas';

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Leger_Nilai_{$nama_kelas}_Semester_{$selectedSemester}_{$tahun_ajaran}.xls");

$siswaData = [];
$mapelList = [];

// Ambil daftar mapel
$queryMapel = "SELECT id_mapel, nama_mapel FROM mata_pelajaran ORDER BY id_mapel ASC";
$resultMapel = mysqli_query($koneksi, $queryMapel);
while ($mapel = mysqli_fetch_assoc($resultMapel)) {
    $mapelList[] = $mapel;
}

// Ambil data siswa, nilai (via GROUP_CONCAT), agregasi (SUM, AVG), dan ranking (RANK)
$query = "SELECT 
            s.id_siswa, s.nama, s.nomor_santri,
            t.id_transaksi,
            a.izin, a.sakit, a.tanpa_keterangan,
            k.kelakuan, k.kerajinan, k.kerapian,
            COALESCE(SUM(n.nilai_angka), 0) as total_nilai,
            COALESCE(AVG(n.nilai_angka), 0) as rata_rata,
            GROUP_CONCAT(CONCAT(n.id_mapel, ':', n.nilai_angka) SEPARATOR ',') as daftar_nilai,
            RANK() OVER (ORDER BY COALESCE(SUM(n.nilai_angka), 0) DESC) as ranking
          FROM riwayat_kelas r
          JOIN siswa s ON r.id_siswa = s.id_siswa
          LEFT JOIN transaksi_raport t ON s.id_siswa = t.id_siswa AND t.semester = '$selectedSemester' AND t.tahun_ajaran = r.tahun_ajaran
          LEFT JOIN absensi a ON t.id_transaksi = a.id_transaksi
          LEFT JOIN kepribadian k ON t.id_transaksi = k.id_transaksi
          LEFT JOIN nilai n ON t.id_transaksi = n.id_transaksi
          WHERE r.id_kelas = '$selectedKelas' AND r.tahun_ajaran = '$tahun_ajaran' AND s.status = 'Aktif'
          GROUP BY s.id_siswa
          ORDER BY s.nama ASC";

$result = mysqli_query($koneksi, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $nilaiMapel = [];
    
    if (!empty($row['daftar_nilai'])) {
        $pairs = explode(',', $row['daftar_nilai']);
        foreach ($pairs as $pair) {
            $parts = explode(':', $pair);
            if (count($parts) === 2) {
                $nilaiMapel[$parts[0]] = $parts[1];
            }
        }
    }
    
    $row['nilai'] = $nilaiMapel;
    
    // Perbaikan format rata-rata jika mata pelajaran lebih dari yang ada nilainya
    // Pada export_leger lama, pembaginya adalah jumlah dari seluruh mapel yg diajarkan
    $jumlah_seluruh_mapel = count($mapelList);
    if ($jumlah_seluruh_mapel > 0) {
        $row['rata_rata'] = $row['total_nilai'] / $jumlah_seluruh_mapel;
    } else {
        $row['rata_rata'] = 0;
    }

    $siswaData[] = $row;
}

// Include the view file for HTML rendering
include 'views/export_leger_view.php';
?>
