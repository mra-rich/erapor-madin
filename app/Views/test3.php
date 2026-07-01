<?php
require 'koneksi.php';
$q = mysqli_query($koneksi, 'DESCRIBE transaksi_raport');
if($q) {
    while($r = mysqli_fetch_assoc($q)) {
        print_r($r);
    }
}
