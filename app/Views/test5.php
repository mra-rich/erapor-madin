<?php
require 'koneksi.php';
$q = mysqli_query($koneksi, 'SELECT * FROM transaksi_raport WHERE id_siswa = 1');
print_r(mysqli_fetch_all($q, MYSQLI_ASSOC));

$q2 = mysqli_query($koneksi, 'SELECT * FROM kepribadian WHERE id_transaksi IN (SELECT id_transaksi FROM transaksi_raport WHERE id_siswa = 1)');
print_r(mysqli_fetch_all($q2, MYSQLI_ASSOC));
