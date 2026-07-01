<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);
require_once 'csrf.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        header("Location: data_guru.php?status=error&message=Token CSRF tidak valid!");
        exit;
    }
    $id_pengguna = isset($_POST['id_pengguna']) ? (int)$_POST['id_pengguna'] : 0;
    $id_guru = isset($_POST['id_guru']) ? (int)$_POST['id_guru'] : 0;
    $peran = isset($_POST['peran']) ? trim($_POST['peran']) : 'Guru';
    
    $nip = trim($_POST['nip']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $jenis_kelamin = trim($_POST['jenis_kelamin']);
    $tempat_lahir = trim($_POST['tempat_lahir']);
    $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : NULL;
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($id_pengguna <= 0 || empty($nama_lengkap) || empty($username)) {
        header("Location: data_guru.php?status=error&message=Data tidak lengkap!");
        exit;
    }

    mysqli_begin_transaction($koneksi);

    try {
        // Cek username apakah dipakai orang lain
        $query_cek = "SELECT id_pengguna FROM pengguna WHERE username = ? AND id_pengguna != ?";
        $stmt_cek = mysqli_prepare($koneksi, $query_cek);
        mysqli_stmt_bind_param($stmt_cek, "si", $username, $id_pengguna);
        mysqli_stmt_execute($stmt_cek);
        $cek_username = mysqli_stmt_get_result($stmt_cek);
        if (mysqli_num_rows($cek_username) > 0) {
            throw new Exception("Username sudah digunakan oleh pengguna lain.");
        }

        // Update pengguna
        if (!empty($password)) {
            // Update dengan password baru
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query_pengguna = "UPDATE pengguna SET nama = ?, username = ?, password = ?, peran = ? WHERE id_pengguna = ?";
            $stmt_pengguna = mysqli_prepare($koneksi, $query_pengguna);
            mysqli_stmt_bind_param($stmt_pengguna, "ssssi", $nama_lengkap, $username, $hashed_password, $peran, $id_pengguna);
        } else {
            // Update tanpa ubah password
            $query_pengguna = "UPDATE pengguna SET nama = ?, username = ?, peran = ? WHERE id_pengguna = ?";
            $stmt_pengguna = mysqli_prepare($koneksi, $query_pengguna);
            mysqli_stmt_bind_param($stmt_pengguna, "sssi", $nama_lengkap, $username, $peran, $id_pengguna);
        }
        
        if (!mysqli_stmt_execute($stmt_pengguna)) {
            throw new Exception("Gagal memperbarui data login.");
        }
        mysqli_stmt_close($stmt_pengguna);

        // Update atau Insert ke tabel guru
        if ($id_guru > 0) {
            $query_guru = "UPDATE guru SET nip = ?, nama_lengkap = ?, jenis_kelamin = ?, tempat_lahir = ?, tanggal_lahir = ?, no_hp = ?, alamat = ? WHERE id_guru = ?";
            $stmt_guru = mysqli_prepare($koneksi, $query_guru);
            mysqli_stmt_bind_param($stmt_guru, "sssssssi", $nip, $nama_lengkap, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $no_hp, $alamat, $id_guru);
            if (!mysqli_stmt_execute($stmt_guru)) {
                throw new Exception("Gagal memperbarui biodata.");
            }
            mysqli_stmt_close($stmt_guru);
        } else {
            // Jika sebelumnya tidak ada data di tabel guru, maka insert
            $query_guru = "INSERT INTO guru (id_pengguna, nip, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, no_hp, alamat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_guru = mysqli_prepare($koneksi, $query_guru);
            mysqli_stmt_bind_param($stmt_guru, "isssssss", $id_pengguna, $nip, $nama_lengkap, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $no_hp, $alamat);
            if (!mysqli_stmt_execute($stmt_guru)) {
                throw new Exception("Gagal menambahkan biodata.");
            }
            mysqli_stmt_close($stmt_guru);
        }

        mysqli_commit($koneksi);

        // Catat log
        require_once 'logger.php';
        catat_log($koneksi, $_SESSION['id_pengguna'], 'Edit Pengguna', "Memperbarui data pengguna ID: $id_pengguna ($nama_lengkap, Peran: $peran)");

        header("Location: data_guru.php?status=success&message=Data berhasil diperbarui!");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: data_guru.php?status=error&message=" . urlencode($e->getMessage()));
        exit;
    }
}
?>
