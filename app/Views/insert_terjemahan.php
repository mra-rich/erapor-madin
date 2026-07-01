<?php
require 'koneksi.php';

// Data terjemahan mata pelajaran
$terjemahan_data = [
    ["Al-Quran/Nahdhiyah", "القرآن النهضية"],
    ["Aqidatul Awam", "عقيدة العوام"],
    ["Imritih", "العمريطي"],
    ["Sorof", "الصرف"],
    ["Tajwid", "التجويد"],
    ["Taqrin", "التقارير"],
    ["Washoya", "الوصايا"]
];

// Insert data
foreach ($terjemahan_data as $data) {
    $nama_mapel = $data[0];
    $terjemahan_arab = $data[1];

    $query = "INSERT IGNORE INTO terjemahan_mapel (nama_mapel, terjemahan_arab) VALUES (?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);

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
