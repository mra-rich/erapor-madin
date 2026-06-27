<?php
require 'koneksi.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['nama_mapel']) || empty($_POST['nama_mapel'])) {
        throw new Exception('Nama mata pelajaran tidak boleh kosong');
    }

    $nama_mapel = trim($_POST['nama_mapel']);

    // Escape string untuk mencegah SQL injection
    $nama_mapel = mysqli_real_escape_string($koneksi, $nama_mapel);

    // Query untuk mencari terjemahan dari tabel terjemahan_mapel
    $query = "SELECT terjemahan_arab FROM terjemahan_mapel WHERE nama_mapel = ?";
    $stmt = mysqli_prepare($koneksi, $query);

    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "s", $nama_mapel);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(['terjemahan' => $row['terjemahan_arab'] ?? '']);
    } else {
        // Jika tidak ditemukan di tabel terjemahan_mapel, coba cari di tabel mata_pelajaran
        $query_mapel = "SELECT nama_mapel_arab FROM mata_pelajaran WHERE nama_mapel = ?";
        $stmt_mapel = mysqli_prepare($koneksi, $query_mapel);

        if (!$stmt_mapel) {
            throw new Exception('Error preparing statement: ' . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param($stmt_mapel, "s", $nama_mapel);
        mysqli_stmt_execute($stmt_mapel);
        $result_mapel = mysqli_stmt_get_result($stmt_mapel);

        if ($row_mapel = mysqli_fetch_assoc($result_mapel)) {
            echo json_encode(['terjemahan' => $row_mapel['nama_mapel_arab'] ?? '']);
        } else {
            echo json_encode(['terjemahan' => '']);
        }

        mysqli_stmt_close($stmt_mapel);
    }

    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'terjemahan' => ''
    ]);
}
