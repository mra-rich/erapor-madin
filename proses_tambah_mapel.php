<?php
require 'koneksi.php';

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_mapel = trim($_POST['nama_mapel']);
    $nama_mapel_arab = trim($_POST['nama_mapel_arab']);
    $kategori = trim($_POST['kategori']);
    $id_kelas = isset($_POST['id_kelas']) ? intval($_POST['id_kelas']) : 0;

    // Validasi input
    if (empty($nama_mapel) || empty($nama_mapel_arab) || empty($kategori) || empty($id_kelas)) {
        header("Location: tambah_mata_pelajaran.php?status=error&message=Semua field harus diisi!");
        exit;
    }

    // Validasi kategori
    $kategori_valid = ['TES TERTULIS', 'HAFALAN', 'TES BACA'];
    if (!in_array($kategori, $kategori_valid)) {
        header("Location: tambah_mata_pelajaran.php?status=error&message=Kategori tidak valid!");
        exit;
    }

    // Validasi id_kelas
    if ($id_kelas <= 0) {
        header("Location: tambah_mata_pelajaran.php?status=error&message=ID Kelas tidak valid!");
        exit;
    }

    // Cek apakah nama mata pelajaran sudah ada untuk kelas ini
    $cek_query = "SELECT * FROM mata_pelajaran WHERE nama_mapel = ? AND id_kelas = ?";
    $stmt_cek = mysqli_prepare($koneksi, $cek_query);
    mysqli_stmt_bind_param($stmt_cek, "si", $nama_mapel, $id_kelas);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);

    if (mysqli_num_rows($result_cek) > 0) {
        header("Location: tambah_mata_pelajaran.php?status=error&message=Nama mata pelajaran sudah terdaftar untuk kelas ini!");
        exit;
    }

    // Simpan ke database
    $query = "INSERT INTO mata_pelajaran (nama_mapel, nama_mapel_arab, kategori, id_kelas) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "sssi", $nama_mapel, $nama_mapel_arab, $kategori, $id_kelas);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: data_mata_pelajaran.php?status=success&message=Mata pelajaran berhasil ditambahkan!");
    } else {
        header("Location: tambah_mata_pelajaran.php?status=error&message=Gagal menambahkan mata pelajaran: " . mysqli_error($koneksi));
    }

    mysqli_stmt_close($stmt);
    exit;
} else {
    header("Location: tambah_mata_pelajaran.php");
    exit;
}
