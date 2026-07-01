<?php
require 'koneksi.php';
$query = "SELECT r.*, k.nama_kelas, k.nama_rombel, tk.nama_tingkat, pg.nama_lengkap as wali_kelas
                 FROM riwayat_kelas r
                 JOIN kelas k ON r.id_kelas = k.id_kelas
                 JOIN tingkat_kelas tk ON k.id_tingkat = tk.id_tingkat
                 LEFT JOIN pengguna pg ON k.id_wali_kelas = pg.id_pengguna
                 LIMIT 1";
$res = mysqli_query($koneksi, $query);
if (!$res) echo mysqli_error($koneksi);
else echo "OK";
?>
