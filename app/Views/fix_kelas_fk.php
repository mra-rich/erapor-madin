<?php
require 'koneksi.php';

// Coba drop foreign key dulu jika ada masalah
// $res = mysqli_query($koneksi, "ALTER TABLE kelas DROP FOREIGN KEY fk_wali_kelas");
// echo "Drop FK: " . mysqli_error($koneksi) . "\n";

$res = mysqli_query($koneksi, "ALTER TABLE kelas MODIFY COLUMN id_wali_kelas INT(11) NULL DEFAULT NULL");
echo "Alter column: " . mysqli_error($koneksi) . "\n";

?>
