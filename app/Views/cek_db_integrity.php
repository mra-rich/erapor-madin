<?php
require 'koneksi.php';
$id_kelas_test = 2; // Test class 2 B Tsanawiyah (or whatever it is)

// Find what id_kelas is 2 B Tsanawiyah
$q_kelas = mysqli_query($koneksi, "SELECT * FROM kelas WHERE nama_kelas='2' AND nama_rombel='B'");
while($row = mysqli_fetch_assoc($q_kelas)){
    echo "Found Kelas: ";
    print_r($row);
    
    // Check siswa in this class
    $idk = $row['id_kelas'];
    $q_siswa = mysqli_query($koneksi, "SELECT COUNT(*) as c FROM siswa WHERE id_kelas=$idk");
    $c = mysqli_fetch_assoc($q_siswa);
    echo "Siswa in this class: " . $c['c'] . "\n";
    
    // Check pengampu_mapel for this class
    $q_pm = mysqli_query($koneksi, "SELECT * FROM pengampu_mapel WHERE id_kelas=$idk");
    echo "Pengampu_mapel entries for this class: " . mysqli_num_rows($q_pm) . "\n";
}
?>
