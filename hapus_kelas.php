<?php
require 'koneksi.php';

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Cek apakah ada ID kelas
if (!isset($_GET['id'])) {
    header("Location: data_kelas.php?status=error&message=ID Kelas tidak ditemukan!");
    exit;
}

$id_kelas = $_GET['id'];

// Cek apakah kelas ada
$cek_query = "SELECT * FROM kelas WHERE id_kelas = ?";
$stmt_cek = mysqli_prepare($koneksi, $cek_query);
mysqli_stmt_bind_param($stmt_cek, "i", $id_kelas);
mysqli_stmt_execute($stmt_cek);
$result_cek = mysqli_stmt_get_result($stmt_cek);

if (mysqli_num_rows($result_cek) == 0) {
    header("Location: data_kelas.php?status=error&message=Kelas tidak ditemukan!");
    exit;
}

// Cek apakah ada siswa yang terkait dengan kelas ini
$cek_siswa_query = "SELECT COUNT(*) as jumlah FROM siswa WHERE id_kelas = ?";
$stmt_siswa = mysqli_prepare($koneksi, $cek_siswa_query);
mysqli_stmt_bind_param($stmt_siswa, "i", $id_kelas);
mysqli_stmt_execute($stmt_siswa);
$result_siswa = mysqli_stmt_get_result($stmt_siswa);
$siswa_count = mysqli_fetch_assoc($result_siswa)['jumlah'];

if ($siswa_count > 0) {
    header("Location: data_kelas.php?status=error&message=Tidak dapat menghapus kelas karena masih ada siswa yang terkait dengan kelas ini!");
    exit;
}

// Cek apakah ada mata pelajaran yang terkait dengan kelas ini
$cek_mapel_query = "SELECT COUNT(*) as jumlah FROM mata_pelajaran WHERE id_kelas = ?";
$stmt_mapel = mysqli_prepare($koneksi, $cek_mapel_query);
mysqli_stmt_bind_param($stmt_mapel, "i", $id_kelas);
mysqli_stmt_execute($stmt_mapel);
$result_mapel = mysqli_stmt_get_result($stmt_mapel);
$mapel_count = mysqli_fetch_assoc($result_mapel)['jumlah'];

if ($mapel_count > 0) {
    header("Location: data_kelas.php?status=error&message=Tidak dapat menghapus kelas karena masih ada mata pelajaran yang terkait dengan kelas ini!");
    exit;
}

// Hapus kelas
$delete_query = "DELETE FROM kelas WHERE id_kelas = ?";
$stmt_delete = mysqli_prepare($koneksi, $delete_query);
mysqli_stmt_bind_param($stmt_delete, "i", $id_kelas);

if (mysqli_stmt_execute($stmt_delete)) {
    header("Location: data_kelas.php?status=success&message=Kelas berhasil dihapus!");
} else {
    header("Location: data_kelas.php?status=error&message=Gagal menghapus kelas: " . mysqli_error($koneksi));
}

mysqli_stmt_close($stmt_delete);
exit;
