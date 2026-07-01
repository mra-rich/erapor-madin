<?php
require 'koneksi.php';
$q = mysqli_query($koneksi, "DESCRIBE pengguna");
while ($r = mysqli_fetch_assoc($q)) {
    echo $r['Field'] . "\n";
}
echo "--- kelas ---\n";
$q = mysqli_query($koneksi, "DESCRIBE kelas");
while ($r = mysqli_fetch_assoc($q)) {
    echo $r['Field'] . "\n";
}
?>
