<?php
require 'koneksi.php';
require 'cek_sesi.php';

// Cek apakah ID wali kelas telah diberikan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: data_wali_kelas.php?status=error&message=ID wali kelas tidak valid");
    exit();
}

$id_wali = mysqli_real_escape_string($koneksi, $_GET['id']);

// Cek apakah wali kelas ada
$check_query = "SELECT * FROM pengguna WHERE id_pengguna = '$id_wali' AND peran = 'Wali Kelas'";
$check_result = mysqli_query($koneksi, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    header("Location: data_wali_kelas.php?status=error&message=Wali kelas tidak ditemukan");
    exit();
}

// Cek apakah wali kelas memiliki kelas yang terkait
$check_kelas_query = "SELECT * FROM kelas WHERE id_wali_kelas = '$id_wali'";
$check_kelas_result = mysqli_query($koneksi, $check_kelas_query);

if (mysqli_num_rows($check_kelas_result) > 0) {
    header("Location: data_wali_kelas.php?status=error&message=Wali kelas tidak dapat dihapus karena masih memiliki kelas yang terkait");
    exit();
}

// Hapus wali kelas
$delete_query = "DELETE FROM pengguna WHERE id_pengguna = '$id_wali' AND peran = 'Wali Kelas'";

if (mysqli_query($koneksi, $delete_query)) {
    header("Location: data_wali_kelas.php?status=success&message=Data wali kelas berhasil dihapus");
} else {
    header("Location: data_wali_kelas.php?status=error&message=Gagal menghapus data wali kelas: " . mysqli_error($koneksi));
}

exit();
