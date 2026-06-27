<?php
require 'koneksi.php';
require 'cek_sesi.php';

// Cek apakah ada ID mapel
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: data_mata_pelajaran.php?status=error&message=ID Mata Pelajaran tidak ditemukan!");
    exit;
}

$id_mapel = intval($_GET['id']);

// Cek apakah mata pelajaran ada
$cek_query = "SELECT * FROM mata_pelajaran WHERE id_mapel = ?";
$stmt_cek = mysqli_prepare($koneksi, $cek_query);
mysqli_stmt_bind_param($stmt_cek, "i", $id_mapel);
mysqli_stmt_execute($stmt_cek);
$result_cek = mysqli_stmt_get_result($stmt_cek);

if (mysqli_num_rows($result_cek) == 0) {
    header("Location: data_mata_pelajaran.php?status=error&message=Mata pelajaran tidak ditemukan!");
    exit;
}

// Cek apakah ada nilai yang terkait dengan mata pelajaran ini
$cek_nilai_query = "SELECT COUNT(*) as jumlah FROM nilai WHERE id_mapel = ?";
$stmt_nilai = mysqli_prepare($koneksi, $cek_nilai_query);
mysqli_stmt_bind_param($stmt_nilai, "i", $id_mapel);
mysqli_stmt_execute($stmt_nilai);
$result_nilai = mysqli_stmt_get_result($stmt_nilai);
$nilai_count = mysqli_fetch_assoc($result_nilai)['jumlah'];

if ($nilai_count > 0) {
    header("Location: data_mata_pelajaran.php?status=error&message=Tidak dapat menghapus mata pelajaran karena masih ada nilai yang terkait dengan mata pelajaran ini!");
    exit;
}

// Hapus mata pelajaran
$delete_query = "DELETE FROM mata_pelajaran WHERE id_mapel = ?";
$stmt_delete = mysqli_prepare($koneksi, $delete_query);
mysqli_stmt_bind_param($stmt_delete, "i", $id_mapel);

if (mysqli_stmt_execute($stmt_delete)) {
    header("Location: data_mata_pelajaran.php?status=success&message=Mata pelajaran berhasil dihapus!");
} else {
    header("Location: data_mata_pelajaran.php?status=error&message=Gagal menghapus mata pelajaran: " . mysqli_error($koneksi));
}

mysqli_stmt_close($stmt_delete);
exit;
