<?php
require 'koneksi.php';
$tahun_aktif = '2023/2024';
$semester_aktif = 1;
$id_kelas = 1;

$query_siswa = "
    SELECT 
        s.id_siswa, s.nama, s.nisn, 
        tr.id_transaksi,
        kp.kelakuan, kp.kerajinan, kp.kerapian, kp.kedisiplinan,
        ex.baca_quran, ex.baca_kitab, ex.muhafadhoh, ex.kaligrafi,
        cw.catatan,
        ab.sakit, ab.izin, ab.tanpa_keterangan
    FROM riwayat_kelas r
    JOIN siswa s ON r.id_siswa = s.id_siswa
    LEFT JOIN transaksi_raport tr ON tr.id_siswa = s.id_siswa AND tr.tahun_ajaran = '$tahun_aktif' AND tr.semester = $semester_aktif
    LEFT JOIN kepribadian kp ON kp.id_transaksi = tr.id_transaksi
    LEFT JOIN ekstrakurikuler ex ON ex.id_transaksi = tr.id_transaksi
    LEFT JOIN catatan_wali_kelas cw ON cw.id_transaksi = tr.id_transaksi
    LEFT JOIN absensi ab ON ab.id_transaksi = tr.id_transaksi
    WHERE r.id_kelas = $id_kelas AND r.tahun_ajaran = '$tahun_aktif'
    ORDER BY s.nama ASC
";
$res = mysqli_query($koneksi, $query_siswa);
print_r(mysqli_fetch_all($res, MYSQLI_ASSOC));
