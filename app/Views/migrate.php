<?php
require 'koneksi.php';

// Step 1: Wipe tingkat_kelas and populate with 3 primary tingkats
mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS=0");
mysqli_query($koneksi, "TRUNCATE TABLE tingkat_kelas");
mysqli_query($koneksi, "INSERT INTO tingkat_kelas (id_tingkat, nama_tingkat) VALUES (1, 'Ibtida\\'iyah'), (2, 'Tsanawiyah'), (3, 'Aliyah')");

// Step 2: Extract all existing kelas records
$result = mysqli_query($koneksi, "SELECT * FROM kelas");
while ($k = mysqli_fetch_assoc($result)) {
    $id_kelas = $k['id_kelas'];
    $old_nama = $k['nama_kelas'];
    
    $new_tingkat_id = 1;
    $new_nama_kelas = "1";
    $new_nama_rombel = "A";
    
    if (strpos($old_nama, "Ibtida'iyah") !== false) {
        $new_tingkat_id = 1;
        if (preg_match('/(\d+)/', $old_nama, $m)) $new_nama_kelas = $m[1];
        if (preg_match('/\d+\s+([A-Z])\s+/', $old_nama, $m)) $new_nama_rombel = $m[1];
    } elseif (strpos($old_nama, "Tsanawiyah") !== false || strpos($old_nama, "VII") !== false || strpos($old_nama, "IX") !== false) {
        $new_tingkat_id = 2;
        if (strpos($old_nama, "VII-") !== false) $new_nama_kelas = "1";
        elseif (strpos($old_nama, "VIII") !== false) $new_nama_kelas = "2";
        elseif (strpos($old_nama, "IX") !== false) $new_nama_kelas = "3";
        
        if (strpos($old_nama, "-") !== false) {
            $parts = explode("-", $old_nama);
            $new_nama_rombel = trim($parts[1]);
        }
    } elseif (strpos($old_nama, "Aliyah") !== false || strpos($old_nama, "X-") !== false || strpos($old_nama, "XI") !== false) {
        $new_tingkat_id = 3;
        if (strpos($old_nama, "X-") !== false) $new_nama_kelas = "1";
        elseif (strpos($old_nama, "XI") !== false) $new_nama_kelas = "2";
        elseif (strpos($old_nama, "XII") !== false) $new_nama_kelas = "3";
        
        if (strpos($old_nama, "-") !== false) {
            $parts = explode("-", $old_nama);
            $new_nama_rombel = trim($parts[1]);
        }
    }
    
    mysqli_query($koneksi, "UPDATE kelas SET id_tingkat=$new_tingkat_id, nama_kelas='$new_nama_kelas', nama_rombel='$new_nama_rombel' WHERE id_kelas=$id_kelas");
}

mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS=1");

echo "Database migrated successfully!\n";
?>
