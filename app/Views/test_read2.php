<?php
require 'vendor/autoload.php';
if ($xlsx = \Shuchkin\SimpleXLSX::parse('test_border.xlsx')) {
    print_r($xlsx->rows());
} else {
    echo \Shuchkin\SimpleXLSX::parseError();
}
