<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_SUPER_ADMIN);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_mapel = mysqli_real_escape_string($koneksi, $_POST['nama_mapel']);
    $nama_mapel_arab = mysqli_real_escape_string($koneksi, $_POST['nama_mapel_arab']);
    $nama_kitab = mysqli_real_escape_string($koneksi, $_POST['nama_kitab']);
    $kkm = (int)$_POST['kkm'];
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $id_kelas_redirect = isset($_POST['id_kelas_redirect']) ? (int)$_POST['id_kelas_redirect'] : 0;

    // Validasi input
    if (empty($nama_mapel) || empty($nama_mapel_arab)) {
        header("Location: data_mata_pelajaran.php?status=error&message=Semua field harus diisi!");
        exit;
    }

    // Cek apakah nama mata pelajaran sudah ada
    $cek_query = "SELECT * FROM mata_pelajaran WHERE nama_mapel = ?";
    $stmt_cek = mysqli_prepare($koneksi, $cek_query);
    mysqli_stmt_bind_param($stmt_cek, "s", $nama_mapel);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);

    if (mysqli_num_rows($result_cek) > 0) {
        header("Location: data_mata_pelajaran.php?status=error&message=Nama mata pelajaran sudah terdaftar!");
        exit;
    }

    // Simpan ke database
    $query = "INSERT INTO mata_pelajaran (nama_mapel, nama_mapel_arab, nama_kitab, kkm, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "sssis", $nama_mapel, $nama_mapel_arab, $nama_kitab, $kkm, $status);

    if (mysqli_stmt_execute($stmt)) {
        require_once 'logger.php';
        catat_log($koneksi, $_SESSION['id_pengguna'], 'Tambah Mapel', "Menambahkan mata pelajaran master: $nama_mapel");
        header("Location: data_mata_pelajaran.php?kelas=$id_kelas_redirect&status=success&message=Mata pelajaran berhasil ditambahkan!");
    } else {
        header("Location: data_mata_pelajaran.php?kelas=$id_kelas_redirect&status=error&message=Gagal menambahkan mata pelajaran: " . mysqli_error($koneksi));
    }

    mysqli_stmt_close($stmt);
    exit;
} else {
    header("Location: data_mata_pelajaran.php");
    exit;
}
