<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);

$id_mapel = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_kelas = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;

if ($id_mapel <= 0 || $id_kelas <= 0) {
    header("Location: data_mata_pelajaran.php?kelas=$id_kelas&status=error&message=ID tidak valid!");
    exit;
}

// Hanya hapus relasi mapel di kelas ini saja — kelas lain tidak terpengaruh sama sekali
$stmt = mysqli_prepare($koneksi, "DELETE FROM pengampu_mapel WHERE id_mapel = ? AND id_kelas = ?");
mysqli_stmt_bind_param($stmt, "ii", $id_mapel, $id_kelas);
$berhasil = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($berhasil) {
    require_once 'logger.php';
    catat_log($koneksi, $_SESSION['id_pengguna'], 'Hapus Mapel dari Kelas', "Menghapus mapel (ID: $id_mapel) dari kelas (ID: $id_kelas)");
    header("Location: data_mata_pelajaran.php?kelas=$id_kelas&status=success&message=Mata Pelajaran berhasil dihapus dari kelas ini!");
} else {
    header("Location: data_mata_pelajaran.php?kelas=$id_kelas&status=error&message=Gagal menghapus! " . mysqli_error($koneksi));
}
exit;
?>
