<?php
require 'koneksi.php';
$res = mysqli_query($koneksi, "SELECT pm.id_kelas, k.nama_kelas, k.nama_rombel, t.nama_tingkat FROM pengampu_mapel pm JOIN kelas k ON pm.id_kelas = k.id_kelas LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat WHERE pm.id_guru = 5 OR pm.id_guru = 2");
while($row = mysqli_fetch_assoc($res)) {
    echo "Assigned to Kelas ID: " . $row['id_kelas'] . " (" . $row['nama_tingkat'] . " - " . $row['nama_kelas'] . " " . $row['nama_rombel'] . ")\n";
}
?>
