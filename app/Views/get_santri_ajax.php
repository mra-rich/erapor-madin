<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_MANAGE_STUDENTS);

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan']);
    exit;
}

$id_siswa = intval($_GET['id']);
$query = "SELECT * FROM siswa WHERE id_siswa = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id_siswa);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode(['status' => 'success', 'data' => $row]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Siswa tidak ditemukan']);
}
?>
