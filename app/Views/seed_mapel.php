<?php
require 'koneksi.php';

$mapel_default = [
    "Ilmu Tafsir", "Ilmu Hadits", "Hadits", "Tauhid", "Akhlaq", 
    "Fiqih", "Ushul Fiqhi", "Qowaidul Fiqhi", "Faroidl", "Balaghoh", 
    "Nahwu", "Shorof", "I'lal", "Bahasa Arab", "Pego", 
    "Tajwid", "Tarekh", "Fasholatan", "Al-Qur'an", "Tes Lisan"
];

// Dapatkan semua tingkat yang ada
$query_tingkat = "SELECT id_tingkat FROM tingkat_kelas";
$result_tingkat = mysqli_query($koneksi, $query_tingkat);

$inserted = 0;

while ($row = mysqli_fetch_assoc($result_tingkat)) {
    $id_tingkat = $row['id_tingkat'];
    
    foreach ($mapel_default as $mapel) {
        $safe_mapel = mysqli_real_escape_string($koneksi, $mapel);
        // Cek apakah mapel sudah ada di tingkat ini
        $cek = mysqli_query($koneksi, "SELECT id_mapel FROM mata_pelajaran WHERE nama_mapel = '$safe_mapel' AND id_tingkat = $id_tingkat");
        if (mysqli_num_rows($cek) == 0) {
            // Masukkan mapel
            $insert = "INSERT INTO mata_pelajaran (nama_mapel, id_tingkat, status) VALUES ('$safe_mapel', $id_tingkat, 'Aktif')";
            mysqli_query($koneksi, $insert);
            $inserted++;
        }
    }
}

echo "Sukses: $inserted mata pelajaran default berhasil ditambahkan ke semua tingkat kelas.\n";
?>
