<?php
require 'koneksi.php';
$tahun_aktif = '2024/2025';
$semester_aktif = 1;
$q = mysqli_query($koneksi, "
    SELECT pm.id_mapel, pm.id_kelas,
    (SELECT COUNT(n.id_nilai) 
     FROM nilai n 
     JOIN transaksi_raport tr ON n.id_transaksi = tr.id_transaksi 
     JOIN riwayat_kelas r ON tr.id_siswa = r.id_siswa 
     WHERE r.id_kelas = pm.id_kelas 
       AND n.id_mapel = pm.id_mapel 
       AND tr.tahun_ajaran = '$tahun_aktif' 
       AND tr.semester = $semester_aktif) as jumlah_nilai
    FROM pengampu_mapel pm LIMIT 5
");
if (!$q) { echo mysqli_error($koneksi); }
while($row = mysqli_fetch_assoc($q)){
    print_r($row);
}
