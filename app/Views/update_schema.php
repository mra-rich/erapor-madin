<?php
require 'koneksi.php';
mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS=0");
mysqli_query($koneksi, "TRUNCATE TABLE nilai");
mysqli_query($koneksi, "TRUNCATE TABLE pengampu_mapel");
mysqli_query($koneksi, "TRUNCATE TABLE mapel_kelas");
mysqli_query($koneksi, "TRUNCATE TABLE mata_pelajaran");
mysqli_query($koneksi, "ALTER TABLE mata_pelajaran DROP FOREIGN KEY mata_pelajaran_ibfk_1");
mysqli_query($koneksi, "ALTER TABLE mata_pelajaran DROP COLUMN id_tingkat");
mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS=1");

$mapel_default = [
    "Ilmu Tafsir" => "علم التفسير",
    "Ilmu Hadits" => "علم الحديث",
    "Hadits" => "الحديث",
    "Tauhid" => "التوحيد",
    "Akhlaq" => "الأخلاق", 
    "Fiqih" => "الفقه",
    "Ushul Fiqhi" => "أصول الفقه",
    "Qowaidul Fiqhi" => "قواعد الفقه",
    "Faroidl" => "الفرائض",
    "Balaghoh" => "البلاغة", 
    "Nahwu" => "النحو",
    "Shorof" => "الصرف",
    "I'lal" => "الإعلال",
    "Bahasa Arab" => "اللغة العربية",
    "Pego" => "فيجو", 
    "Tajwid" => "التجويد",
    "Tarekh" => "التاريخ",
    "Fasholatan" => "فصلتان",
    "Al-Qur'an" => "القرآن",
    "Tes Lisan" => "اختبار شفوي"
];

foreach ($mapel_default as $mapel => $arab) {
    $safe_mapel = mysqli_real_escape_string($koneksi, $mapel);
    $safe_arab = mysqli_real_escape_string($koneksi, $arab);
    
    // Check if column id_guru exists
    $check_col = mysqli_query($koneksi, "SHOW COLUMNS FROM mata_pelajaran LIKE 'id_guru'");
    if(mysqli_num_rows($check_col) == 0){
        mysqli_query($koneksi, "ALTER TABLE mata_pelajaran ADD COLUMN id_guru INT(11) NULL");
    }

    $insert = "INSERT INTO mata_pelajaran (nama_mapel, nama_mapel_arab, status, kkm) VALUES ('$safe_mapel', '$safe_arab', 'Aktif', 65)";
    if(!mysqli_query($koneksi, $insert)) {
        echo "Gagal: " . mysqli_error($koneksi) . "\n";
    }
}
echo "Sukses update schema dan insert mapel.";
?>
