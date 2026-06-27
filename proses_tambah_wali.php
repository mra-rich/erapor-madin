<?php
require 'koneksi.php';
require 'cek_sesi.php';

// Cek apakah form telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi input
    $errors = [];

    // Cek apakah username sudah ada
    $check_query = "SELECT * FROM pengguna WHERE username = '$username'";
    $check_result = mysqli_query($koneksi, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "Username sudah digunakan. Silakan gunakan username lain.";
    }

    // Cek apakah password dan konfirmasi password sama
    if ($password !== $konfirmasi_password) {
        $errors[] = "Password dan konfirmasi password tidak cocok.";
    }

    // Jika tidak ada error, simpan data
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Query untuk menyimpan data
        $query = "INSERT INTO pengguna (nama, username, password, peran) VALUES ('$nama', '$username', '$hashed_password', 'Wali Kelas')";

        if (mysqli_query($koneksi, $query)) {
            // Redirect ke halaman data wali kelas dengan pesan sukses
            header("Location: data_wali_kelas.php?status=success&message=Data wali kelas berhasil ditambahkan");
            exit();
        } else {
            $errors[] = "Terjadi kesalahan: " . mysqli_error($koneksi);
        }
    }

    // Jika ada error, kembali ke form dengan pesan error
    if (!empty($errors)) {
        // Simpan error dalam session
        session_start();
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = [
            'nama' => $nama,
            'username' => $username
        ];

        // Redirect kembali ke form
        header("Location: tambah_wali.php");
        exit();
    }
} else {
    // Jika bukan method POST, redirect ke halaman data wali kelas
    header("Location: data_wali_kelas.php");
    exit();
}
