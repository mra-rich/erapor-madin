<?php
require 'koneksi.php';
$res = mysqli_query($koneksi, "DESCRIBE pengampu_mapel");
while($row = mysqli_fetch_assoc($res)){
    print_r($row);
}
?>
