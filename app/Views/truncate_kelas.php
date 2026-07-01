<?php
require 'koneksi.php';
mysqli_query($koneksi, 'SET FOREIGN_KEY_CHECKS = 0');
mysqli_query($koneksi, 'TRUNCATE TABLE kelas');
mysqli_query($koneksi, 'SET FOREIGN_KEY_CHECKS = 1');
echo "Kelas truncated\n";
?>
