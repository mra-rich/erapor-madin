<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(['Admin']);

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_kelas = isset($_POST['id_kelas']) ? (int)$_POST['id_kelas'] : 0;
    $id_mapel = isset($_POST['id_mapel']) ? (int)$_POST['id_mapel'] : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'Aktif';
    $nama_kitab = isset($_POST['nama_kitab']) ? trim($_POST['nama_kitab']) : '';
    $nama_kitab_arab = isset($_POST['nama_kitab_arab']) ? trim($_POST['nama_kitab_arab']) : '';
    $id_guru = (isset($_POST['id_guru']) && $_POST['id_guru'] !== '') ? (int)$_POST['id_guru'] : NULL;

    if ($id_kelas <= 0 || $id_mapel <= 0) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
        exit;
    }

    // Cek apakah sudah ada pengampu_mapel untuk mapel dan kelas ini
    $cek = mysqli_query($koneksi, "SELECT id FROM pengampu_mapel WHERE id_kelas = $id_kelas AND id_mapel = $id_mapel");
    
    if (mysqli_num_rows($cek) > 0) {
        // Update data
        if ($status == 'Non-Aktif') {
            $query = "UPDATE pengampu_mapel SET status = ?, id_guru = NULL, nama_kitab = ?, nama_kitab_arab = ? WHERE id_kelas = ? AND id_mapel = ?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "sssii", $status, $nama_kitab, $nama_kitab_arab, $id_kelas, $id_mapel);
        } else {
            $query = "UPDATE pengampu_mapel SET status = ?, id_guru = ?, nama_kitab = ?, nama_kitab_arab = ? WHERE id_kelas = ? AND id_mapel = ?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "sisssi", $status, $id_guru, $nama_kitab, $nama_kitab_arab, $id_kelas, $id_mapel);
        }
    } else {
        // Insert data baru
        if ($status == 'Aktif') {
            $query = "INSERT INTO pengampu_mapel (id_kelas, id_mapel, id_guru, nama_kitab, nama_kitab_arab, status) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "iiisss", $id_kelas, $id_mapel, $id_guru, $nama_kitab, $nama_kitab_arab, $status);
        } else {
            // Jika belum ada dan diset non-aktif, kita insert dengan status non-aktif dan guru null
            $query = "INSERT INTO pengampu_mapel (id_kelas, id_mapel, id_guru, nama_kitab, nama_kitab_arab, status) VALUES (?, ?, NULL, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "iisss", $id_kelas, $id_mapel, $nama_kitab, $nama_kitab_arab, $status);
        }
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Tersimpan otomatis']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . mysqli_error($koneksi)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
