<?php
require 'koneksi.php';
$q = mysqli_query($koneksi, "SHOW COLUMNS FROM pengaturan");
while($r = mysqli_fetch_assoc($q)) echo $r['Field'] . " ";
echo "\n";
$q2 = mysqli_query($koneksi, "SHOW COLUMNS FROM transaksi_raport");
while($r = mysqli_fetch_assoc($q2)) echo $r['Field'] . " ";
?>
