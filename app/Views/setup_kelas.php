<?php
require 'koneksi.php';

$targets = [
    1 => ['1', '2', '3'],
    2 => ['1', '2', '3'],
    3 => ['1', '2', '3']
];

foreach ($targets as $id_tingkat => $kelas_list) {
    foreach ($kelas_list as $nama_kelas) {
        $q_check = mysqli_query($koneksi, "SELECT id_kelas FROM kelas WHERE id_tingkat = '$id_tingkat' AND nama_kelas = '$nama_kelas'");
        if (mysqli_num_rows($q_check) == 0) {
            $q_guru = mysqli_query($koneksi, "SELECT id_guru FROM guru LIMIT 1");
            $wali = 0;
            if ($q_guru && $row = mysqli_fetch_assoc($q_guru)) {
                $wali = $row['id_guru'];
            }
            
            $sql = "INSERT INTO kelas (nama_kelas, id_tingkat, nama_rombel, id_wali_kelas, status) VALUES ('$nama_kelas', '$id_tingkat', 'A', '$wali', 'Aktif')";
            mysqli_query($koneksi, $sql);
            echo "Inserted $nama_kelas for tingkat $id_tingkat\n";
        } else {
            echo "Exists $nama_kelas for tingkat $id_tingkat\n";
        }
    }
}
echo "Done.";
?>
