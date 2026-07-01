<?php
require 'koneksi.php';
$q = mysqli_query($koneksi, 'DESCRIBE absensi');
if($q) {
    while($r = mysqli_fetch_assoc($q)) {
        print_r($r);
    }
} else {
    echo "No absensi table";
}
