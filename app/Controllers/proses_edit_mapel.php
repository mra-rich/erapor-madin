<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_SUPER_ADMIN);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $id_mapel = (int)$_POST['id_mapel'];
    $nama_mapel = mysqli_real_escape_string($koneksi, $_POST['nama_mapel']);
    $nama_mapel_arab = mysqli_real_escape_string($koneksi, $_POST['nama_mapel_arab']);
    $nama_kitab = mysqli_real_escape_string($koneksi, $_POST['nama_kitab']);
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'Aktif';
    $kkm = (int)$_POST['kkm'];
    $id_kelas_redirect = isset($_POST['id_kelas_redirect']) ? (int)$_POST['id_kelas_redirect'] : 0;

    // Validasi input
    if (empty($id_mapel) || empty($nama_mapel) || empty($nama_mapel_arab)) {
        header("Location: data_mata_pelajaran.php?kelas=$id_kelas_redirect&status=error&message=Semua field harus diisi!");
        exit;
    }

    // Cek apakah nama mata pelajaran sudah ada (kecuali untuk mata pelajaran yang sedang diedit)
    $cek_query = "SELECT * FROM mata_pelajaran WHERE nama_mapel = ? AND id_mapel != ?";
    $stmt_cek = mysqli_prepare($koneksi, $cek_query);
    mysqli_stmt_bind_param($stmt_cek, "si", $nama_mapel, $id_mapel);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);

    if (mysqli_num_rows($result_cek) > 0) {
        header("Location: data_mata_pelajaran.php?kelas=$id_kelas_redirect&status=error&message=Nama mata pelajaran sudah terdaftar!");
        exit;
    }

    // Update database
    $query = "UPDATE mata_pelajaran SET nama_mapel = ?, nama_mapel_arab = ?, nama_kitab = ?, status = ?, kkm = ? WHERE id_mapel = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ssssii", $nama_mapel, $nama_mapel_arab, $nama_kitab, $status, $kkm, $id_mapel);

    if (mysqli_stmt_execute($stmt)) {
        require_once 'logger.php';
        catat_log($koneksi, $_SESSION['id_pengguna'], 'Edit Mapel', "Memperbarui mata pelajaran master ID: $id_mapel");
        header("Location: data_mata_pelajaran.php?kelas=$id_kelas_redirect&status=success&message=Data mata pelajaran berhasil diperbarui!");
    } else {
        header("Location: data_mata_pelajaran.php?kelas=$id_kelas_redirect&status=error&message=Gagal memperbarui data mata pelajaran: " . mysqli_error($koneksi));
    }

    mysqli_stmt_close($stmt);
    exit;
} else {
    header("Location: data_mata_pelajaran.php");
    exit;
}
