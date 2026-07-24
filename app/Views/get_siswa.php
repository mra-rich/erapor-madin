<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_MANAGE_GRADES);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kelas'])) {
    $kelas = (int)$_POST['kelas'];
    $query = "SELECT * FROM siswa WHERE id_kelas = ? ORDER BY nama ASC"; // Ganti kelas -> id_kelas
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $kelas);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $output = "<option value='' disabled selected>Pilih Santri</option>";
    while ($siswa = mysqli_fetch_assoc($result)) {
        $id_siswa = (int)$siswa['id_siswa'];
        $nisn = htmlspecialchars($siswa['nisn'] ?? '', ENT_QUOTES, 'UTF-8');
        $tahun = htmlspecialchars($siswa['tahun_ajaran'] ?? '', ENT_QUOTES, 'UTF-8');
        $nama = htmlspecialchars($siswa['nama'] ?? '', ENT_QUOTES, 'UTF-8');
        $output .= "<option value='{$id_siswa}'
                    data-nisn='{$nisn}'
                    data-tahun_pelajaran='{$tahun}'>
                    {$nama}</option>";
    }
    echo $output;
}
