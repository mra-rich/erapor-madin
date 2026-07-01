<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: index.php");
    exit;
}

require_once 'koneksi.php';
// Muat pengaturan sistem secara dinamis agar jika admin mengubah, otomatis berubah
if (!isset($_SESSION['tahun_ajaran']) || !isset($_SESSION['semester'])) {
    $q_pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
    if ($q_pengaturan && mysqli_num_rows($q_pengaturan) > 0) {
        $p = mysqli_fetch_assoc($q_pengaturan);
        $_SESSION['tahun_ajaran'] = $p['tahun_ajaran'];
        $_SESSION['semester'] = $p['semester'];
    } else {
        $_SESSION['tahun_ajaran'] = '2024/2025';
        $_SESSION['semester'] = 1;
    }
}

// Konstanta Grup Hak Akses (Centralized RBAC)
const RBAC_SUPER_ADMIN = ['Admin'];
const RBAC_MANAGE_MASTER_DATA = ['Admin', 'Kepala Madrasah'];
const RBAC_MANAGE_STUDENTS = ['Admin', 'Wali Kelas'];
const RBAC_MANAGE_GRADES = ['Admin', 'Wali Kelas', 'Guru'];
const RBAC_VIEW_REPORTS = ['Admin', 'Kepala Madrasah', 'Wali Kelas'];
const RBAC_VIEW_ALL = ['Admin', 'Kepala Madrasah', 'Wali Kelas', 'Guru'];

// Fungsi untuk mengecek peran yang diizinkan (RBAC)
function restrict_roles($allowed_roles = []) {
    if (!isset($_SESSION['peran'])) {
        header("Location: index.php");
        exit;
    }
    
    // Jika allowed_roles tidak kosong, pastikan peran pengguna ada di dalam array
    if (!empty($allowed_roles)) {
        if (!in_array($_SESSION['peran'], $allowed_roles)) {
            // Jika akses ditolak, arahkan ke dashboard
            header("Location: dashboard.php?status=error&message=Akses Ditolak: Anda tidak memiliki izin untuk mengakses halaman tersebut.");
            exit;
        }
    }
}
?>
