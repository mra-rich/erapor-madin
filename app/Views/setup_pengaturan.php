<?php
require 'koneksi.php';

// Buat tabel pengaturan jika belum ada
$sql = "CREATE TABLE IF NOT EXISTS `pengaturan` (
  `id_pengaturan` int(11) NOT NULL AUTO_INCREMENT,
  `tahun_ajaran` varchar(20) NOT NULL,
  `semester` int(11) NOT NULL,
  PRIMARY KEY (`id_pengaturan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($koneksi, $sql)) {
    echo "Tabel pengaturan berhasil dibuat atau sudah ada.\n";
} else {
    echo "Error membuat tabel: " . mysqli_error($koneksi) . "\n";
}

// Cek apakah data sudah ada
$cek = mysqli_query($koneksi, "SELECT * FROM pengaturan");
if (mysqli_num_rows($cek) == 0) {
    // Insert default data
    $insert = "INSERT INTO pengaturan (tahun_ajaran, semester) VALUES ('2024/2025', 1)";
    if (mysqli_query($koneksi, $insert)) {
        echo "Data default pengaturan berhasil ditambahkan.\n";
    }
} else {
    echo "Data pengaturan sudah ada.\n";
}
?>
