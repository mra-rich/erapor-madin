<?php
require 'koneksi.php';

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nisn = trim($_POST['nisn']);
    $nama = trim($_POST['nama']);
    $nomor_santri = trim($_POST['nomor_santri']);
    $id_kelas = isset($_POST['id_kelas']) ? intval($_POST['id_kelas']) : 0;
    $tahun_ajaran = trim($_POST['tahun_ajaran']);
    $alamat = trim($_POST['alamat']);
    $nama_wali = trim($_POST['nama_wali']);

    // Validasi input
    if (empty($nisn) || empty($nama) || empty($nomor_santri) || empty($id_kelas) || empty($tahun_ajaran) || empty($alamat) || empty($nama_wali)) {
        header("Location: tambah_santri.php?status=error&message=Semua field harus diisi!");
        exit;
    }

    // Validasi id_kelas
    if ($id_kelas <= 0) {
        header("Location: tambah_santri.php?status=error&message=ID Kelas tidak valid!");
        exit;
    }

    // Cek apakah NISN sudah ada
    $cek_query = "SELECT * FROM siswa WHERE nisn = ?";
    $stmt_cek = mysqli_prepare($koneksi, $cek_query);
    mysqli_stmt_bind_param($stmt_cek, "s", $nisn);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);

    if (mysqli_num_rows($result_cek) > 0) {
        header("Location: tambah_santri.php?status=error&message=NISN sudah terdaftar!");
        exit;
    }

    // Simpan ke database
    $query = "INSERT INTO siswa (nisn, nama, nomor_santri, id_kelas, tahun_ajaran, alamat, nama_wali) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "sssisss", $nisn, $nama, $nomor_santri, $id_kelas, $tahun_ajaran, $alamat, $nama_wali);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: data_santri.php?status=success&message=Data siswa berhasil ditambahkan!");
        exit();
    } else {
        error_log("Gagal menyimpan data: " . mysqli_stmt_error($stmt));
        header("Location: tambah_santri.php?status=error&message=Gagal menyimpan data: " . mysqli_stmt_error($stmt));
        exit();
    }

    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);
}
