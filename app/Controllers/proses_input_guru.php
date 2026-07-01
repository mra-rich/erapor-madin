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
    $nip = trim($_POST['nip']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $jenis_kelamin = trim($_POST['jenis_kelamin']);
    $tempat_lahir = trim($_POST['tempat_lahir']);
    $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : NULL;
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    
    $peran = isset($_POST['peran']) ? trim($_POST['peran']) : 'Guru';
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $id_pengguna = isset($_POST['id_pengguna']) ? trim($_POST['id_pengguna']) : '';
    $id_guru = isset($_POST['id_guru']) ? trim($_POST['id_guru']) : '';

    // Validasi basic
    if (empty($nama_lengkap) || empty($username)) {
        header("Location: data_guru.php?status=error&message=Nama dan Username wajib diisi!");
        exit;
    }
    
    if (empty($id_pengguna) && empty($password)) {
        header("Location: data_guru.php?status=error&message=Password wajib diisi untuk pengguna baru!");
        exit;
    }

    mysqli_begin_transaction($koneksi);

    try {
        // Cek username apakah sudah ada (kecuali milik sendiri)
        $query_cek = "SELECT id_pengguna FROM pengguna WHERE username = ?";
        if (!empty($id_pengguna)) {
            $query_cek .= " AND id_pengguna != ?";
        }
        $stmt_cek = mysqli_prepare($koneksi, $query_cek);
        
        if (!empty($id_pengguna)) {
            mysqli_stmt_bind_param($stmt_cek, "si", $username, $id_pengguna);
        } else {
            mysqli_stmt_bind_param($stmt_cek, "s", $username);
        }
        
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);
        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            mysqli_stmt_close($stmt_cek);
            throw new Exception("Username sudah digunakan. Silakan pilih username lain.");
        }
        mysqli_stmt_close($stmt_cek);

        if (empty($id_pengguna)) {
            // INSERT BARU
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert ke pengguna
            $query_pengguna = "INSERT INTO pengguna (nama, username, password, peran, status) VALUES (?, ?, ?, ?, 'Aktif')";
            $stmt_pengguna = mysqli_prepare($koneksi, $query_pengguna);
            mysqli_stmt_bind_param($stmt_pengguna, "ssss", $nama_lengkap, $username, $hashed_password, $peran);
            
            if (!mysqli_stmt_execute($stmt_pengguna)) {
                throw new Exception("Gagal menyimpan data login.");
            }
            
            $new_id_pengguna = mysqli_insert_id($koneksi);
            mysqli_stmt_close($stmt_pengguna);

            // Insert ke guru
            $query_guru = "INSERT INTO guru (id_pengguna, nip, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, no_hp, alamat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_guru = mysqli_prepare($koneksi, $query_guru);
            mysqli_stmt_bind_param($stmt_guru, "isssssss", $new_id_pengguna, $nip, $nama_lengkap, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $no_hp, $alamat);

            if (!mysqli_stmt_execute($stmt_guru)) {
                throw new Exception("Gagal menyimpan biodata guru.");
            }
            mysqli_stmt_close($stmt_guru);
            
            $log_action = 'Tambah Guru';
            $log_msg = "Menambahkan guru baru: $nama_lengkap (Username: $username)";
        } else {
            // UPDATE DATA
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query_pengguna = "UPDATE pengguna SET nama=?, username=?, password=?, peran=? WHERE id_pengguna=?";
                $stmt_pengguna = mysqli_prepare($koneksi, $query_pengguna);
                mysqli_stmt_bind_param($stmt_pengguna, "ssssi", $nama_lengkap, $username, $hashed_password, $peran, $id_pengguna);
            } else {
                $query_pengguna = "UPDATE pengguna SET nama=?, username=?, peran=? WHERE id_pengguna=?";
                $stmt_pengguna = mysqli_prepare($koneksi, $query_pengguna);
                mysqli_stmt_bind_param($stmt_pengguna, "sssi", $nama_lengkap, $username, $peran, $id_pengguna);
            }
            
            if (!mysqli_stmt_execute($stmt_pengguna)) {
                throw new Exception("Gagal mengupdate data login.");
            }
            mysqli_stmt_close($stmt_pengguna);

            // Update ke guru
            if (!empty($id_guru)) {
                $query_guru = "UPDATE guru SET nip=?, nama_lengkap=?, jenis_kelamin=?, tempat_lahir=?, tanggal_lahir=?, no_hp=?, alamat=? WHERE id_guru=?";
                $stmt_guru = mysqli_prepare($koneksi, $query_guru);
                mysqli_stmt_bind_param($stmt_guru, "sssssssi", $nip, $nama_lengkap, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $no_hp, $alamat, $id_guru);
            } else {
                // If id_guru is empty but id_pengguna exists, insert into guru
                $query_guru = "INSERT INTO guru (id_pengguna, nip, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, no_hp, alamat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_guru = mysqli_prepare($koneksi, $query_guru);
                mysqli_stmt_bind_param($stmt_guru, "isssssss", $id_pengguna, $nip, $nama_lengkap, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $no_hp, $alamat);
            }

            if (!mysqli_stmt_execute($stmt_guru)) {
                throw new Exception("Gagal mengupdate biodata guru.");
            }
            mysqli_stmt_close($stmt_guru);

            $log_action = 'Edit Guru';
            $log_msg = "Mengedit data guru: $nama_lengkap (Username: $username)";
        }

        mysqli_commit($koneksi);

        // Catat log
        require_once 'logger.php';
        catat_log($koneksi, $_SESSION['id_pengguna'], $log_action, $log_msg);

        header("Location: data_guru.php?status=success&message=" . urlencode("Data guru berhasil " . (empty($id_pengguna) ? "ditambahkan!" : "diperbarui!")));
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: data_guru.php?status=error&message=" . urlencode($e->getMessage()));
        exit;
    }
}
?>
