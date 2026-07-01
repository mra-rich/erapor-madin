<?php
require 'koneksi.php';
$query = "ALTER TABLE siswa MODIFY COLUMN status enum('Aktif','Lulus','Pindah','Dihapus','Boyong','Alumni') DEFAULT 'Aktif'";
if (mysqli_query($koneksi, $query)) {
    echo "Sukses alter table";
} else {
    echo "Gagal: " . mysqli_error($koneksi);
}
?>
