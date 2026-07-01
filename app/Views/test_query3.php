<?php
require 'koneksi.php';
$res = mysqli_query($koneksi, "SHOW COLUMNS FROM nilai");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . "\n";
}
