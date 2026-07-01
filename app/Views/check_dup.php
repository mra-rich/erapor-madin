<?php
require 'koneksi.php';
$res = mysqli_query($koneksi, "SELECT s.id_siswa, s.nama, s.status, t.id_transaksi, t.semester, t.tahun_ajaran FROM siswa s LEFT JOIN transaksi_raport t ON s.id_siswa = t.id_siswa");
$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $data[] = $row;
}
echo json_encode($data, JSON_PRETTY_PRINT);
