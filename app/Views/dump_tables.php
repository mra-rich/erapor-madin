<?php
require 'koneksi.php';
$q = mysqli_query($koneksi, "SHOW TABLES");
$tables = [];
while($r = mysqli_fetch_array($q)){
    $tables[] = $r[0];
}
echo json_encode($tables, JSON_PRETTY_PRINT);
?>
