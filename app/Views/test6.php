<?php
require 'koneksi.php';
$q = mysqli_query($koneksi, 'DESCRIBE pengaturan');
while($r = mysqli_fetch_assoc($q)){print_r($r);}
