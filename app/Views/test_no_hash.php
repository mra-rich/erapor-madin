<?php
require 'vendor/autoload.php';
$data = [['<center><b color="FFFFFF" bgcolor="059669">NO</b></center>']];
$xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->saveAs('test_no_hash.xlsx');
echo "Done";
