<?php
require 'koneksi.php';

mysqli_query($koneksi, "ALTER TABLE kepribadian ADD COLUMN kedisiplinan VARCHAR(20) DEFAULT NULL");

echo "Database updated.\n";
echo mysqli_error($koneksi);
