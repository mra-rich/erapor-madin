<?php
require 'koneksi.php';
$q = mysqli_query($koneksi, 'SELECT id_siswa, nama FROM siswa ORDER BY id_siswa ASC LIMIT 5');
print_r(mysqli_fetch_all($q, MYSQLI_ASSOC));
