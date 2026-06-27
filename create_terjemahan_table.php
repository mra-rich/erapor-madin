<?php
require 'koneksi.php';

// Buat tabel terjemahan_mapel
$query_create_table = "CREATE TABLE IF NOT EXISTS terjemahan_mapel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_mapel VARCHAR(100) NOT NULL,
    terjemahan_arab VARCHAR(100) NOT NULL,
    UNIQUE KEY unique_nama_mapel (nama_mapel)
)";

if (mysqli_query($koneksi, $query_create_table)) {
    echo "Tabel terjemahan_mapel berhasil dibuat<br>";
} else {
    echo "Error creating table: " . mysqli_error($koneksi) . "<br>";
}

// Data awal untuk terjemahan
$terjemahan_data = [
    ["Al-Qur'an/Nahdhiyah", "القران النهضية"],
    ["Aqidatul Awam", "عقيدة العوام"],
    ["Imritih", "العمريطي"],
    ["Sorof", "الصرف"],
    ["Tajwid", "التجويد"],
    ["Taqrin", "التقارير"],
    ["Washoya", "الوصايا"]
];

// Insert data awal
foreach ($terjemahan_data as $data) {
    $nama_mapel = $data[0];
    $terjemahan_arab = $data[1];

    $query_insert = "INSERT IGNORE INTO terjemahan_mapel (nama_mapel, terjemahan_arab) 
                     VALUES (?, ?)";

    $stmt = mysqli_prepare($koneksi, $query_insert);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $nama_mapel, $terjemahan_arab);

        if (mysqli_stmt_execute($stmt)) {
            echo "Data berhasil ditambahkan: $nama_mapel<br>";
        } else {
            echo "Error inserting data: " . mysqli_stmt_error($stmt) . "<br>";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($koneksi) . "<br>";
    }
}

echo "<br>Proses selesai!";
