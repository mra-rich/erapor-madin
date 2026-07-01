<?php
require 'koneksi.php';
$q = mysqli_query($koneksi, "SELECT * FROM tingkat_kelas");
$tingkat = [];
while($r = mysqli_fetch_assoc($q)){
    $tingkat[] = $r;
}
echo json_encode($tingkat, JSON_PRETTY_PRINT);
?>
