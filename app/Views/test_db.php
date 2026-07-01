<?php
require 'koneksi.php';
$query = "SELECT RANK() OVER (ORDER BY id_kelas) as rnk FROM kelas LIMIT 1";
$result = mysqli_query($koneksi, $query);
if ($result) {
    echo "Window functions supported.";
} else {
    echo "Error: " . mysqli_error($koneksi);
}
?>
