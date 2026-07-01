<?php
require 'koneksi.php';
$res = mysqli_query($koneksi, "SELECT id_kelas, COUNT(*) as jml FROM siswa GROUP BY id_kelas");
while($row = mysqli_fetch_assoc($res)) {
    echo "Kelas " . $row['id_kelas'] . ": " . $row['jml'] . " siswa\n";
}
?>
