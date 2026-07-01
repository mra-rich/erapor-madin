<?php
require 'koneksi.php';
$username = "' OR 1=1#";
$password = "bebas";
$query = "SELECT * FROM pengguna WHERE username = '$username' AND password = '$password'";
echo "Query: $query\n";
$result = mysqli_query($koneksi, $query);
if (!$result) {
    echo "MySQL Error: " . mysqli_error($koneksi) . "\n";
} else {
    echo "Rows: " . mysqli_num_rows($result) . "\n";
}
