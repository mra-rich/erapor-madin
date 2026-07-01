<?php
require 'koneksi.php';
$res = mysqli_query($koneksi, "SHOW CREATE TABLE mata_pelajaran");
$row = mysqli_fetch_array($res);
echo $row[1];
