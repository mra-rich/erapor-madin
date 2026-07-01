<?php
require 'koneksi.php';
$q = mysqli_query($koneksi, "SELECT * FROM kelas");
$kelas = [];
while($r = mysqli_fetch_assoc($q)){
    $kelas[] = $r;
}
echo json_encode($kelas, JSON_PRETTY_PRINT);
?>
