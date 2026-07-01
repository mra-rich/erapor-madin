<?php
require 'koneksi.php';
$res = mysqli_query($koneksi, "SHOW COLUMNS FROM siswa");
while ($r = mysqli_fetch_assoc($res)) print_r($r);
?>
