<?php
require 'koneksi.php';
$q = mysqli_query($koneksi, "SHOW TABLES");
while($r = mysqli_fetch_row($q)) {
    echo $r[0] . "\n";
    $q2 = mysqli_query($koneksi, "SHOW COLUMNS FROM " . $r[0]);
    while($r2 = mysqli_fetch_assoc($q2)) echo " - " . $r2['Field'] . "\n";
}
?>
