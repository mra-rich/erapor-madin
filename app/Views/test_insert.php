<?php
require 'koneksi.php';
mysqli_query($koneksi, "INSERT INTO kelas (id_tingkat, nama_kelas, nama_rombel) VALUES (1, '1', 'A')");
echo "Error: " . mysqli_error($koneksi) . "\n";
?>
