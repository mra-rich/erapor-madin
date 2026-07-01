<?php
require 'vendor/autoload.php';
$data = [['<style bgcolor="#059669" color="#FFFFFF"><center><b>NO</b></center></style>']];
$xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->saveAs('test_style.xlsx');
echo "Done";
