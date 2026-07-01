<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tingkatan = trim($_POST['tingkatan'] ?? '');
    $angka_kelas = trim($_POST['angka_kelas'] ?? '');
    $nama_rombel = trim($_POST['rombel'] ?? '');
    $id_wali_kelas = !empty($_POST['id_wali_kelas']) ? intval($_POST['id_wali_kelas']) : NULL;

    // Validasi input
    if (empty($tingkatan) || empty($angka_kelas) || empty($nama_rombel) || empty($id_wali_kelas)) {
        header("Location: data_kelas.php?status=error&message=Tingkatan, Kelas, Rombel, dan Wali Kelas harus diisi!");
        exit;
    }

    // Map string tingkatan ke id_tingkat secara dinamis dari tabel tingkat_kelas
    $id_tingkat = 0;
    $query_tingkat = "SELECT id_tingkat FROM tingkat_kelas WHERE nama_tingkat = ?";
    $stmt_tingkat = mysqli_prepare($koneksi, $query_tingkat);
    mysqli_stmt_bind_param($stmt_tingkat, "s", $tingkatan);
    mysqli_stmt_execute($stmt_tingkat);
    $result_tingkat = mysqli_stmt_get_result($stmt_tingkat);
    if ($row_tingkat = mysqli_fetch_assoc($result_tingkat)) {
        $id_tingkat = $row_tingkat['id_tingkat'];
    }
    mysqli_stmt_close($stmt_tingkat);

    if ($id_tingkat === 0) {
        header("Location: data_kelas.php?status=error&message=Tingkat Madrasah tidak dikenali di master data!");
        exit;
    }

    $nama_kelas = $angka_kelas; // Hanya menyimpan angka (1, 2, 3) di nama_kelas

    // 2. Cek apakah nama kelas (rombel) sudah ada di tingkat yang sama
    $cek_query = "SELECT * FROM kelas WHERE id_tingkat = ? AND nama_kelas = ? AND nama_rombel = ?";
    $stmt_cek = mysqli_prepare($koneksi, $cek_query);
    mysqli_stmt_bind_param($stmt_cek, "iss", $id_tingkat, $nama_kelas, $nama_rombel);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);

    if (mysqli_num_rows($result_cek) > 0) {
        header("Location: data_kelas.php?status=error&message=Nama kelas sudah terdaftar!");
        exit;
    }

    // 3. Simpan ke database kelas
    $query = "INSERT INTO kelas (nama_kelas, id_tingkat, nama_rombel, id_wali_kelas) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    
    // Bind dengan hati-hati karena id_wali_kelas bisa NULL
    mysqli_stmt_bind_param($stmt, "sisi", $nama_kelas, $id_tingkat, $nama_rombel, $id_wali_kelas);

    if (mysqli_stmt_execute($stmt)) {
        require_once 'logger.php';
        catat_log($koneksi, $_SESSION['id_pengguna'], 'Tambah Kelas', "Menambahkan kelas baru: Kelas $nama_kelas $nama_rombel $tingkatan");
        header("Location: data_kelas.php?status=success&message=Kelas berhasil ditambahkan!");
    } else {
        header("Location: data_kelas.php?status=error&message=Gagal menambahkan kelas: " . mysqli_error($koneksi));
    }

    mysqli_stmt_close($stmt);
    exit;
} else {
    header("Location: data_kelas.php");
    exit;
}
