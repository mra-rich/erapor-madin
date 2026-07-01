<?php
require 'vendor/autoload.php';
if ($xlsx = \Shuchkin\SimpleXLSX::parse('template_guru.xlsx')) {
    print_r($xlsx->rows()[1]);
} else {
    echo \Shuchkin\SimpleXLSX::parseError();
}
