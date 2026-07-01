<?php
require 'vendor/autoload.php';

$guru_header = ['NO', 'NIP', 'NAMA_LENGKAP', 'JENIS_KELAMIN', 'TEMPAT_LAHIR', 'TANGGAL_LAHIR', 'ALAMAT', 'NO_HP', 'PENDIDIKAN_TERAKHIR', 'JABATAN', 'STATUS_GURU'];
$guru_data = [$guru_header];

$xlsx = \Shuchkin\SimpleXLSXGen::fromArray($guru_data);
$xlsx->saveAs('template_guru.xlsx');

echo "Template created.";
?>
