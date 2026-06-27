<?php
require 'koneksi.php';

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_siswa = intval($_POST['id_siswa']);
    $nisn = trim($_POST['nisn']);
    $nama = trim($_POST['nama']);
    $nomor_santri = trim($_POST['nomor_santri']);
    $id_kelas = isset($_POST['id_kelas']) ? intval($_POST['id_kelas']) : 0;
    $tahun_ajaran = trim($_POST['tahun_ajaran']);
    $alamat = trim($_POST['alamat']);
    $nama_wali = trim($_POST['nama_wali']);

    // Validasi input
    if (empty($nisn) || empty($nama) || empty($nomor_santri) || empty($id_kelas) || empty($tahun_ajaran) || empty($alamat) || empty($nama_wali)) {
        header("Location: edit_santri.php?id=" . $id_siswa . "&status=error&message=Semua field harus diisi!");
        exit;
    }

    // Validasi id_kelas
    if ($id_kelas <= 0) {
        header("Location: edit_santri.php?id=" . $id_siswa . "&status=error&message=ID Kelas tidak valid!");
        exit;
    }

    // Cek apakah NISN sudah ada (kecuali untuk siswa yang sedang diedit)
    $cek_query = "SELECT * FROM siswa WHERE nisn = ? AND id_siswa != ?";
    $stmt_cek = mysqli_prepare($koneksi, $cek_query);
    mysqli_stmt_bind_param($stmt_cek, "si", $nisn, $id_siswa);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);

    if (mysqli_num_rows($result_cek) > 0) {
        header("Location: edit_santri.php?id=" . $id_siswa . "&status=error&message=NISN sudah terdaftar!");
        exit;
    }

    // Update data ke database
    $query = "UPDATE siswa SET nisn = ?, nama = ?, nomor_santri = ?, id_kelas = ?, tahun_ajaran = ?, alamat = ?, nama_wali = ? WHERE id_siswa = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "sssisssi", $nisn, $nama, $nomor_santri, $id_kelas, $tahun_ajaran, $alamat, $nama_wali, $id_siswa);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: data_santri.php?status=success&message=Data siswa berhasil diperbarui!");
        exit();
    } else {
        error_log("Gagal memperbarui data: " . mysqli_stmt_error($stmt));
        header("Location: edit_santri.php?id=" . $id_siswa . "&status=error&message=Gagal memperbarui data: " . mysqli_stmt_error($stmt));
        exit();
    }

    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);
}
