<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("Aksi diblokir karena token keamanan tidak valid (Potensi serangan CSRF).");
    }

    $id_pengguna = $_SESSION['id_pengguna'];
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi input kosong
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        header("Location: profil.php?status=error&message=Semua field password harus diisi!");
        exit;
    }

    // Validasi kecocokan password baru
    if ($password_baru !== $konfirmasi_password) {
        header("Location: profil.php?status=error&message=Konfirmasi password baru tidak cocok!");
        exit;
    }

    // Ambil data user saat ini
    $query = "SELECT password FROM pengguna WHERE id_pengguna = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_pengguna);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    $is_valid = false;
    
    // Cek kecocokan password lama
    if (password_verify($password_lama, $user['password'])) {
        $is_valid = true;
    } else if ($user['password'] === $password_lama) {
        // Fallback jika masih plaintext
        $is_valid = true;
    }

    if (!$is_valid) {
        header("Location: profil.php?status=error&message=Password lama salah!");
        exit;
    }

    // Hash password baru
    $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);

    // Update ke database
    $update_query = "UPDATE pengguna SET password = ? WHERE id_pengguna = ?";
    $stmt_update = mysqli_prepare($koneksi, $update_query);
    mysqli_stmt_bind_param($stmt_update, "si", $hashed_password, $id_pengguna);
    
    if (mysqli_stmt_execute($stmt_update)) {
        // Catat ke Log Aktivitas
        require_once 'logger.php';
        catat_log($koneksi, $id_pengguna, 'Ganti Password', "Pengguna mengganti password miliknya secara mandiri.");

        header("Location: profil.php?status=success&message=Password berhasil diubah!");
    } else {
        header("Location: profil.php?status=error&message=Terjadi kesalahan pada sistem database!");
    }

    mysqli_stmt_close($stmt);
    mysqli_stmt_close($stmt_update);
    mysqli_close($koneksi);
    exit;
} else {
    header("Location: profil.php");
    exit;
}
?>
