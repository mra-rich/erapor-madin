<?php
require 'koneksi.php';
mysqli_query($koneksi, "INSERT INTO tingkat_kelas (nama_tingkat) VALUES ('Ibtida\'iyah'), ('Tsanawiyah'), ('Aliyah')");
mysqli_query($koneksi, "INSERT INTO identitas_madrasah (nama_madrasah, nsm, npsn, alamat) VALUES ('Madrasah Aliyah Negeri 1', '131135730001', '20531518', 'Jl. Pendidikan No. 1')");
mysqli_query($koneksi, "INSERT INTO pengaturan (nama_pengaturan, nilai) VALUES ('tahun_ajaran_aktif', '2023/2024'), ('semester_aktif', 'Ganjil')");
echo 'Base data restored.';
?>
