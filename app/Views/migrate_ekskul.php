<?php
require 'koneksi.php';

// Disable foreign key checks momentarily if necessary (though it shouldn't be for column drops)
mysqli_query($koneksi, "ALTER TABLE ekstrakurikuler DROP COLUMN pramuka");
mysqli_query($koneksi, "ALTER TABLE ekstrakurikuler DROP COLUMN pmr");
mysqli_query($koneksi, "ALTER TABLE ekstrakurikuler DROP COLUMN paskibra");

mysqli_query($koneksi, "ALTER TABLE ekstrakurikuler ADD COLUMN baca_quran VARCHAR(20) DEFAULT NULL");
mysqli_query($koneksi, "ALTER TABLE ekstrakurikuler ADD COLUMN baca_kitab VARCHAR(20) DEFAULT NULL");
mysqli_query($koneksi, "ALTER TABLE ekstrakurikuler ADD COLUMN muhafadhoh VARCHAR(20) DEFAULT NULL");
mysqli_query($koneksi, "ALTER TABLE ekstrakurikuler ADD COLUMN kaligrafi VARCHAR(20) DEFAULT NULL");

echo "Database updated.\n";
echo mysqli_error($koneksi);
