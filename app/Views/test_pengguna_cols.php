<?php
require 'koneksi.php';
$res = mysqli_query($koneksi, "SHOW COLUMNS FROM pengguna");
while ($r = mysqli_fetch_assoc($res)) print_r($r['Field']." ");
?>
