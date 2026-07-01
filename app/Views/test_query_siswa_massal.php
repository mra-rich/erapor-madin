<?php
require 'koneksi.php';

$id_mapel = 1;
$id_kelas = 10;
$tahun_aktif = '2023/2024';
$semester_aktif = 1;

$query_siswa = "
    SELECT s.id_siswa, s.nama, s.nisn,
           (SELECT n.nilai_angka 
            FROM nilai n 
            JOIN transaksi_raport tr ON n.id_transaksi = tr.id_transaksi 
            WHERE tr.id_siswa = s.id_siswa 
              AND tr.tahun_ajaran = '$tahun_aktif' 
              AND tr.semester = $semester_aktif 
              AND n.id_mapel = $id_mapel LIMIT 1) as nilai_angka
    FROM siswa s
    WHERE s.id_kelas = $id_kelas
    ORDER BY s.nama ASC
";
$res = mysqli_query($koneksi, $query_siswa);
if(!$res) {
    echo "Error: " . mysqli_error($koneksi);
} else {
    echo "Success, rows: " . mysqli_num_rows($res) . "\n";
    while($r = mysqli_fetch_assoc($res)) {
        echo $r['nama'] . "\n";
    }
}
?>
