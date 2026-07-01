<?php
require 'koneksi.php';

// Fetch the Admin user to keep
$res = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE peran='Admin' LIMIT 1");
$admin = mysqli_fetch_assoc($res);
if(!$admin) {
    echo "No admin found.\n";
} else {
    echo "Found admin: " . $admin['username'] . "\n";
}

mysqli_query($koneksi, 'SET FOREIGN_KEY_CHECKS = 0');

$tables = [
    'absensi',
    'catatan_wali_kelas',
    'ekstrakurikuler',
    'kelas',
    'kepribadian',
    'log_aktivitas',
    'login_attempts',
    'mapel_kelas',
    'mata_pelajaran',
    'nilai',
    'pengampu_mapel',
    'siswa',
    'tingkat_kelas',
    'transaksi_raport'
];

foreach ($tables as $table) {
    mysqli_query($koneksi, "TRUNCATE TABLE $table");
    echo "Truncated $table\n";
}

// Special case for pengguna
mysqli_query($koneksi, "DELETE FROM pengguna WHERE id_pengguna != " . $admin['id_pengguna']);
echo "Cleared pengguna except admin\n";

mysqli_query($koneksi, 'SET FOREIGN_KEY_CHECKS = 1');

echo "Database reset complete.\n";
?>
