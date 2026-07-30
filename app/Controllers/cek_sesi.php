<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: index.php");
    exit;
}

// Session idle timeout: 30 menit tidak aktif → logout otomatis
$session_timeout = 1800; // 30 menit dalam detik
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION = [];
    session_destroy();
    header("Location: index.php?status=timeout");
    exit;
}
$_SESSION['last_activity'] = time();

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

/**
 * Helper: query prepared statement untuk SELECT.
 * @param string $sql Query dengan placeholder ?
 * @param array $params Parameter values
 * @return mysqli_result|false
 */
function db_query($sql, $params = []) {
    global $koneksi;
    $stmt = $koneksi->prepare($sql);
    if (!$stmt) {
        error_log("DB prepare error: " . $koneksi->error . " | SQL: " . $sql);
        return false;
    }
    if ($params) {
        $types = '';
        $values = [];
        foreach ($params as $p) {
            $types .= is_int($p) ? 'i' : (is_float($p) ? 'd' : 's');
            $values[] = $p;
        }
        $stmt->bind_param($types, ...$values);
    }
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Helper: query prepared statement untuk INSERT/UPDATE/DELETE.
 * @param string $sql Query dengan placeholder ?
 * @param array $params Parameter values
 * @return bool true jika berhasil
 */
function db_execute($sql, $params = []) {
    global $koneksi;
    $stmt = $koneksi->prepare($sql);
    if (!$stmt) {
        error_log("DB prepare error: " . $koneksi->error . " | SQL: " . $sql);
        return false;
    }
    if ($params) {
        $types = '';
        $values = [];
        foreach ($params as $p) {
            $types .= is_int($p) ? 'i' : (is_float($p) ? 'd' : 's');
            $values[] = $p;
        }
        $stmt->bind_param($types, ...$values);
    }
    return $stmt->execute();
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
