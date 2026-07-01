<?php
require 'koneksi.php';
require 'csrf.php';
// session_start(); // Sudah dipanggil di csrf.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF Token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Validasi Brute Force
    if (check_brute_force($username, $koneksi)) {
        $_SESSION['error'] = 'Akun terkunci sementara karena terlalu banyak percobaan gagal. Coba lagi dalam 15 menit.';
        header("Location: index.php");
        exit;
    }

    // Validasi input kosong
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username dan password wajib diisi!';
        header('Location: index.php');
        exit;
    }

    // Gunakan prepared statement untuk keamanan
    $stmt = $koneksi->prepare("SELECT * FROM pengguna WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Periksa apakah pengguna ditemukan
    if ($user) { 
        $is_valid = false;
        
        // 1. Verifikasi murni menggunakan Hash (Keamanan Ditingkatkan)
        if (password_verify($password, $user['password'])) {
            $is_valid = true;
        }

        if ($is_valid) {
            // Simpan data pengguna ke dalam sesi dengan aman
            $_SESSION['id_pengguna'] = $user['id_pengguna'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['peran'] = $user['peran'];
            
            // Clear brute-force logs
            clear_login_attempts($username, $koneksi);

            // Catat ke Log Aktivitas
            require_once 'logger.php';
            catat_log($koneksi, $user['id_pengguna'], 'Login Berhasil', "Pengguna {$user['username']} login ke sistem.");

            // Redirect berdasarkan peran
            // Redirect ke halaman utama
            header('Location: dashboard.php');
            exit;
        }
    }
    
    // Jika gagal
    $ip_address = $_SERVER['REMOTE_ADDR'];
    record_failed_login($username, $ip_address, $koneksi);
    $_SESSION['error'] = 'Username atau password salah!';
    header('Location: index.php');
    exit;
}
?>
