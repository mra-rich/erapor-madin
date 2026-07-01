<?php
require 'koneksi.php';
$q = mysqli_query($koneksi, "
    SELECT 
        s.id_siswa, s.nama,
        tr.id_transaksi,
        tr.tahun_ajaran,
        tr.semester
    FROM siswa s 
    LEFT JOIN transaksi_raport tr ON tr.id_siswa = s.id_siswa 
        AND tr.tahun_ajaran = '2023/2024' 
        AND tr.semester = 1
    WHERE s.id_siswa = 1
");
print_r(mysqli_fetch_all($q, MYSQLI_ASSOC));
