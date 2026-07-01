<?php
require 'koneksi.php';
$result = mysqli_query($koneksi, "SHOW COLUMNS FROM mata_pelajaran");
$cols = [];
while ($row = mysqli_fetch_assoc($result)) {
    $cols[] = $row['Field'];
}
echo json_encode($cols);
