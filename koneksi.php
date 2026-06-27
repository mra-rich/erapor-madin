<?php
session_start();

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'e_raport';

$koneksi = new mysqli($host, $user, $password, $database);

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>
