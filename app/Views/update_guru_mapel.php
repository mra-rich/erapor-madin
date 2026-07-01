<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        echo json_encode(['status' => 'error', 'message' => 'CSRF Token tidak valid!']);
        exit;
    }

    $id_mapel = (int)$_POST['id_mapel'];
    $id_guru = (int)$_POST['id_guru'];

    $guru_val = ($id_guru > 0) ? $id_guru : 'NULL';

    $query = "UPDATE mata_pelajaran SET id_guru = $guru_val WHERE id_mapel = $id_mapel";
    if (mysqli_query($koneksi, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Guru pengajar berhasil disimpan.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data: ' . mysqli_error($koneksi)]);
    }
}
?>
