<?php
require 'koneksi.php';

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_kelas = isset($_POST['id_kelas']) ? intval($_POST['id_kelas']) : 0;
    $nama_kelas = trim($_POST['nama_kelas']);
    $tingkat = trim($_POST['tingkat']);
    $id_wali_kelas = isset($_POST['id_wali_kelas']) ? intval($_POST['id_wali_kelas']) : 0;

    // Validasi input
    if (empty($id_kelas) || empty($nama_kelas) || empty($tingkat) || empty($id_wali_kelas)) {
        header("Location: edit_kelas.php?id=$id_kelas&status=error&message=Semua field harus diisi!");
        exit;
    }

    // Validasi tingkat
    $tingkat_valid = ['VII', 'VIII', 'IX'];
    if (!in_array($tingkat, $tingkat_valid)) {
        header("Location: edit_kelas.php?id=$id_kelas&status=error&message=Tingkat tidak valid!");
        exit;
    }

    // Validasi id_wali_kelas
    if ($id_wali_kelas <= 0) {
        header("Location: edit_kelas.php?id=$id_kelas&status=error&message=ID Wali Kelas tidak valid!");
        exit;
    }

    // Cek apakah nama kelas sudah ada (kecuali untuk kelas yang sedang diedit)
    $cek_query = "SELECT * FROM kelas WHERE nama_kelas = ? AND id_kelas != ?";
    $stmt_cek = mysqli_prepare($koneksi, $cek_query);
    mysqli_stmt_bind_param($stmt_cek, "si", $nama_kelas, $id_kelas);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);

    if (mysqli_num_rows($result_cek) > 0) {
        header("Location: edit_kelas.php?id=$id_kelas&status=error&message=Nama kelas sudah terdaftar!");
        exit;
    }

    // Update database
    $query = "UPDATE kelas SET nama_kelas = ?, tingkat = ?, id_wali_kelas = ? WHERE id_kelas = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ssii", $nama_kelas, $tingkat, $id_wali_kelas, $id_kelas);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: data_kelas.php?status=success&message=Data kelas berhasil diperbarui!");
    } else {
        header("Location: edit_kelas.php?id=$id_kelas&status=error&message=Gagal memperbarui data kelas: " . mysqli_error($koneksi));
    }

    mysqli_stmt_close($stmt);
    exit;
} else {
    header("Location: data_kelas.php");
    exit;
}
