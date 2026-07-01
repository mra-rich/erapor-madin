<?php
require 'koneksi.php';
$res = mysqli_query($koneksi, "SHOW COLUMNS FROM mapel_kelas");
while($row = mysqli_fetch_array($res)) {
    echo $row[0] . "\n";
}
