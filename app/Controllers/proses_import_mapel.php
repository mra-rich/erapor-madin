<?php
require 'koneksi.php';
require 'cek_sesi.php';

restrict_roles(['Admin']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'CSRF Token tidak valid']);
    exit;
}

$batch_data = json_decode($_POST['batch_data'], true);

if (!$batch_data || !is_array($batch_data)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    foreach ($batch_data as $row) {
        $id_kelas = (int)$row['id_kelas'];
        $id_mapel = (int)$row['id_mapel'];
        $nama_kitab = $row['nama_kitab'];
        $id_guru = isset($row['id_guru']) && $row['id_guru'] !== null ? (int)$row['id_guru'] : null;
        $status = $row['status'];
        
        // Cek apakah sudah ada
        $cek_query = "SELECT id FROM pengampu_mapel WHERE id_kelas = ? AND id_mapel = ?";
        $stmt_cek = mysqli_prepare($koneksi, $cek_query);
        mysqli_stmt_bind_param($stmt_cek, "ii", $id_kelas, $id_mapel);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);
        $exists = mysqli_stmt_num_rows($stmt_cek) > 0;
        mysqli_stmt_close($stmt_cek);
        
        if ($exists) {
            // UPDATE
            if ($id_guru === null) {
                $query = "UPDATE pengampu_mapel SET status = ?, id_guru = NULL, nama_kitab = ? WHERE id_kelas = ? AND id_mapel = ?";
                $stmt = mysqli_prepare($koneksi, $query);
                mysqli_stmt_bind_param($stmt, "ssii", $status, $nama_kitab, $id_kelas, $id_mapel);
            } else {
                $query = "UPDATE pengampu_mapel SET status = ?, id_guru = ?, nama_kitab = ? WHERE id_kelas = ? AND id_mapel = ?";
                $stmt = mysqli_prepare($koneksi, $query);
                mysqli_stmt_bind_param($stmt, "sisii", $status, $id_guru, $nama_kitab, $id_kelas, $id_mapel);
            }
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            // INSERT
            if ($id_guru === null) {
                $query = "INSERT INTO pengampu_mapel (id_kelas, id_mapel, id_guru, nama_kitab, status) VALUES (?, ?, NULL, ?, ?)";
                $stmt = mysqli_prepare($koneksi, $query);
                mysqli_stmt_bind_param($stmt, "iiss", $id_kelas, $id_mapel, $nama_kitab, $status);
            } else {
                $query = "INSERT INTO pengampu_mapel (id_kelas, id_mapel, id_guru, nama_kitab, status) VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($koneksi, $query);
                mysqli_stmt_bind_param($stmt, "iiiss", $id_kelas, $id_mapel, $id_guru, $nama_kitab, $status);
            }
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_commit($koneksi);
    echo json_encode(['success' => true, 'message' => 'Batch berhasil diproses']);
    
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['success' => false, 'message' => 'Gagal memproses data: ' . $e->getMessage()]);
}
