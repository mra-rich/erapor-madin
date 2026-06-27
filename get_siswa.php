<?php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kelas'])) {
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $query = "SELECT * FROM siswa WHERE id_kelas = '$kelas' ORDER BY nama ASC"; // Ganti kelas -> id_kelas
    $result = mysqli_query($koneksi, $query);

    $output = "<option value='' disabled selected>Pilih Santri</option>";
    while ($siswa = mysqli_fetch_assoc($result)) {
        $output .= "<option value='{$siswa['id_siswa']}' 
                    data-nisn='{$siswa['nisn']}' 
                    data-tahun_pelajaran='{$siswa['tahun_ajaran']}'>
                    {$siswa['nama']}</option>";
    }
    echo $output;
}
