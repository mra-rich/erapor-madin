<?php
require 'koneksi.php';
$res = mysqli_query($koneksi, "SELECT * FROM pengampu_mapel LIMIT 10");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
