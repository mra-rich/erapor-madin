<?php
require 'koneksi.php';

$new_mapels = [
    1 => 'Ilmu Tafsir',
    2 => 'Ilmu Hadits',
    3 => 'Hadits',
    4 => 'Tauhid',
    5 => 'Akhlaq',
    6 => 'Fiqhi',
    7 => 'Ushul Fiqhi',
    8 => 'Qowaidul Fiqhi',
    9 => 'Faroidl',
    10 => 'Balaghoh',
    11 => 'Nahwu',
    12 => 'Shorof',
    13 => 'I\'lal',
    14 => 'Bahasa Arab',
    15 => 'Pego',
    16 => 'Tajwid',
    17 => 'Tarekh',
    18 => 'Fasholatan',
    19 => 'Al-Qur\'an',
    20 => 'Tes Lisan'
];

mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS=0");

// Ambil data yang lama
$res = mysqli_query($koneksi, "SELECT * FROM mata_pelajaran");
$old_mapels = [];
while ($row = mysqli_fetch_assoc($res)) {
    $old_mapels[$row['nama_mapel']] = $row;
}

// Untuk menghindari duplicate ID saat update, kita pindahkan semua ID lama ke range sementara (1000+)
foreach ($old_mapels as $nama => $row) {
    $temp_id = $row['id_mapel'] + 1000;
    $old_id = $row['id_mapel'];
    mysqli_query($koneksi, "UPDATE mata_pelajaran SET id_mapel = $temp_id WHERE id_mapel = $old_id");
    mysqli_query($koneksi, "UPDATE pengampu_mapel SET id_mapel = $temp_id WHERE id_mapel = $old_id");
    // Update nilai_mapel (asumsi ada tabel nilai, tapi jika tidak ada, abaikan errornya)
    mysqli_query($koneksi, "UPDATE nilai_akhir SET id_mapel = $temp_id WHERE id_mapel = $old_id");
    mysqli_query($koneksi, "UPDATE detail_nilai_formatif SET id_mapel = $temp_id WHERE id_mapel = $old_id");
    mysqli_query($koneksi, "UPDATE nilai_sumatif SET id_mapel = $temp_id WHERE id_mapel = $old_id");
    $old_mapels[$nama]['temp_id'] = $temp_id;
}

// Update ke ID yang baru
foreach ($new_mapels as $new_id => $nama) {
    if (isset($old_mapels[$nama])) {
        // Update dari temp_id ke new_id
        $temp_id = $old_mapels[$nama]['temp_id'];
        mysqli_query($koneksi, "UPDATE mata_pelajaran SET id_mapel = $new_id WHERE id_mapel = $temp_id");
        mysqli_query($koneksi, "UPDATE pengampu_mapel SET id_mapel = $new_id WHERE id_mapel = $temp_id");
        mysqli_query($koneksi, "UPDATE nilai_akhir SET id_mapel = $new_id WHERE id_mapel = $temp_id");
        mysqli_query($koneksi, "UPDATE detail_nilai_formatif SET id_mapel = $new_id WHERE id_mapel = $temp_id");
        mysqli_query($koneksi, "UPDATE nilai_sumatif SET id_mapel = $new_id WHERE id_mapel = $temp_id");
        echo "Updated $nama to ID $new_id\n";
    } else {
        // Jika belum ada, insert baru
        $nama_esc = mysqli_real_escape_string($koneksi, $nama);
        mysqli_query($koneksi, "INSERT INTO mata_pelajaran (id_mapel, nama_mapel, nama_mapel_arab, kkm, status) VALUES ($new_id, '$nama_esc', '', 65, 'Aktif')");
        echo "Inserted $nama with ID $new_id\n";
    }
}

// Delete mapel sisa yang temp (yang tidak ada di list baru)
mysqli_query($koneksi, "DELETE FROM mata_pelajaran WHERE id_mapel > 1000");

mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS=1");
echo "DONE!";
