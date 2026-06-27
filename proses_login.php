<?php
session_start();
require 'koneksi.php'; // Pastikan koneksi database tersedia

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

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

    // Periksa apakah pengguna ditemukan dan password cocok
    if ($user && $user['password'] === $password) { 
        // Simpan semua data pengguna ke dalam sesi
        $_SESSION['id_pengguna'] = $user['id_pengguna'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['password'] = $user['password']; // Hindari menyimpan password di sesi jika memungkinkan
        $_SESSION['peran'] = $user['peran'];

        // Redirect berdasarkan peran
        if ($user['peran'] === 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit;
    } else {
        $_SESSION['error'] = 'Username atau password salah!';
        header('Location: index.php');
        exit;
    }
}
?>
