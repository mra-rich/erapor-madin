<?php
$_POST['id_kelas'] = 1; // Assuming class 1
$_POST['id_siswa'] = [1]; // Assuming student 1
$_POST['kelakuan'] = [1 => 'C'];
$_POST['ajax'] = '1';
require 'proses_evaluasi_wali.php';
