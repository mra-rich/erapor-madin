<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['id_kelas'] = 1;
$_POST['id_siswa'] = [1];
$_POST['kelakuan'] = [1 => 'D'];
$_POST['ajax'] = '1';

require 'koneksi.php';
$_SESSION['id_pengguna'] = 1;
$_SESSION['peran'] = 'Admin';

require 'proses_evaluasi_wali.php';
