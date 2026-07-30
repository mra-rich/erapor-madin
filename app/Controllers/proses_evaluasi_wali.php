<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_GRADES);

$id_pengguna = (int)($_SESSION['id_pengguna'] ?? 0);
$peran = $_SESSION['peran'] ?? '';

if (!in_array($peran, ['Wali Kelas', 'Admin'])) {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        if (($_POST['ajax'] ?? '') === '1') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'CSRF token validation failed.']);
        } else {
            die("CSRF token validation failed.");
        }
        exit;
    }
    $id_kelas = (int)($_POST['id_kelas'] ?? 0);
    $id_siswa_arr = $_POST['id_siswa'] ?? [];
    
    // Arrays data
    $kelakuan = $_POST['kelakuan'] ?? [];
    $kerajinan = $_POST['kerajinan'] ?? [];
    $kerapian = $_POST['kerapian'] ?? [];
    $kedisiplinan = $_POST['kedisiplinan'] ?? [];
    
    $baca_quran = $_POST['baca_quran'] ?? [];
    $baca_kitab = $_POST['baca_kitab'] ?? [];
    $muhafadhoh = $_POST['muhafadhoh'] ?? [];
    $kaligrafi = $_POST['kaligrafi'] ?? [];
    
    $sakit_arr = $_POST['sakit'] ?? [];
    $izin_arr = $_POST['izin'] ?? [];
    $alpha_arr = $_POST['alpha'] ?? [];
    
    $catatan = $_POST['catatan'] ?? [];

    $q_pengaturan = db_query("SELECT * FROM pengaturan LIMIT 1");
    $data_pengaturan = $q_pengaturan ? mysqli_fetch_assoc($q_pengaturan) : [];
    $tahun_aktif = $data_pengaturan['tahun_ajaran'] ?? '';
    $semester_aktif = (int)($data_pengaturan['semester'] ?? 1);

    mysqli_begin_transaction($koneksi);
    try {
        foreach ($id_siswa_arr as $id_siswa) {
            $id_siswa = (int)$id_siswa;
            // 1. Dapatkan atau Buat transaksi_raport
            $res_tr = db_query(
                "SELECT id_transaksi FROM transaksi_raport WHERE id_siswa = ? AND tahun_ajaran = ? AND semester = ?",
                [$id_siswa, $tahun_aktif, $semester_aktif]
            );
            
            if ($res_tr && mysqli_num_rows($res_tr) > 0) {
                $tr = mysqli_fetch_assoc($res_tr);
                $id_transaksi = $tr['id_transaksi'];
            } else {
                // Buat transaksi raport baru
                db_execute(
                    "INSERT INTO transaksi_raport (id_siswa, id_pengguna, tahun_ajaran, semester) VALUES (?, ?, ?, ?)",
                    [$id_siswa, $id_pengguna, $tahun_aktif, $semester_aktif]
                );
                $id_transaksi = mysqli_insert_id($koneksi);
            }

            // Data untuk diinsert
            $v_kelakuan = $kelakuan[$id_siswa] ?? '';
            $v_kerajinan = $kerajinan[$id_siswa] ?? '';
            $v_kerapian = $kerapian[$id_siswa] ?? '';
            $v_kedisiplinan = $kedisiplinan[$id_siswa] ?? '';
            
            $v_baca_quran = $baca_quran[$id_siswa] ?? '';
            $v_baca_kitab = $baca_kitab[$id_siswa] ?? '';
            $v_muhafadhoh = $muhafadhoh[$id_siswa] ?? '';
            $v_kaligrafi = $kaligrafi[$id_siswa] ?? '';
            
            $v_sakit = (int)($sakit_arr[$id_siswa] ?? 0);
            $v_izin = (int)($izin_arr[$id_siswa] ?? 0);
            $v_alpha = (int)($alpha_arr[$id_siswa] ?? 0);
            
            $v_catatan = $catatan[$id_siswa] ?? '';

            // 2. Kepribadian (Upsert)
            $res_kp = db_query("SELECT id_kepribadian FROM kepribadian WHERE id_transaksi = ?", [$id_transaksi]);
            if ($res_kp && mysqli_num_rows($res_kp) > 0) {
                db_execute("UPDATE kepribadian SET kelakuan = ?, kerajinan = ?, kerapian = ?, kedisiplinan = ? WHERE id_transaksi = ?",
                    [$v_kelakuan, $v_kerajinan, $v_kerapian, $v_kedisiplinan, $id_transaksi]);
            } else {
                db_execute("INSERT INTO kepribadian (id_transaksi, kelakuan, kerajinan, kerapian, kedisiplinan) VALUES (?, ?, ?, ?, ?)",
                    [$id_transaksi, $v_kelakuan, $v_kerajinan, $v_kerapian, $v_kedisiplinan]);
            }

            // 3. Ekstrakurikuler (Upsert)
            $res_ex = db_query("SELECT id_ekstrakurikuler FROM ekstrakurikuler WHERE id_transaksi = ?", [$id_transaksi]);
            if ($res_ex && mysqli_num_rows($res_ex) > 0) {
                db_execute("UPDATE ekstrakurikuler SET baca_quran = ?, baca_kitab = ?, muhafadhoh = ?, kaligrafi = ? WHERE id_transaksi = ?",
                    [$v_baca_quran, $v_baca_kitab, $v_muhafadhoh, $v_kaligrafi, $id_transaksi]);
            } else {
                db_execute("INSERT INTO ekstrakurikuler (id_transaksi, baca_quran, baca_kitab, muhafadhoh, kaligrafi) VALUES (?, ?, ?, ?, ?)",
                    [$id_transaksi, $v_baca_quran, $v_baca_kitab, $v_muhafadhoh, $v_kaligrafi]);
            }

            // 4. Catatan Wali Kelas (Upsert)
            $res_cw = db_query("SELECT id_catatan FROM catatan_wali_kelas WHERE id_transaksi = ?", [$id_transaksi]);
            if ($res_cw && mysqli_num_rows($res_cw) > 0) {
                db_execute("UPDATE catatan_wali_kelas SET catatan = ? WHERE id_transaksi = ?",
                    [$v_catatan, $id_transaksi]);
            } else {
                db_execute("INSERT INTO catatan_wali_kelas (id_transaksi, catatan) VALUES (?, ?)",
                    [$id_transaksi, $v_catatan]);
            }

            // 5. Absensi (Upsert)
            $res_ab = db_query("SELECT id_absensi FROM absensi WHERE id_transaksi = ?", [$id_transaksi]);
            if ($res_ab && mysqli_num_rows($res_ab) > 0) {
                db_execute("UPDATE absensi SET sakit = ?, izin = ?, tanpa_keterangan = ? WHERE id_transaksi = ?",
                    [$v_sakit, $v_izin, $v_alpha, $id_transaksi]);
            } else {
                db_execute("INSERT INTO absensi (id_transaksi, sakit, izin, tanpa_keterangan) VALUES (?, ?, ?, ?)",
                    [$id_transaksi, $v_sakit, $v_izin, $v_alpha]);
            }
        }
        
        mysqli_commit($koneksi);
        
        // Catat aktivitas
        db_execute(
            "INSERT INTO log_aktivitas (id_pengguna, aktivitas, tabel_terkait, waktu) VALUES (?, ?, ?, NOW())",
            [$id_pengguna, 'Menyimpan evaluasi kelas binaan (ID Kelas: ' . $id_kelas . ')', 'kepribadian, ekstrakurikuler, catatan_wali_kelas']
        );

        if (isset($_GET['ajax']) || isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            exit;
        }

        echo "<script>
            alert('Evaluasi kelas berhasil disimpan!');
            window.location.href = 'evaluasi_wali.php';
        </script>";

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        
        if (isset($_GET['ajax']) || isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
        
        echo "<script>
            alert('Terjadi kesalahan: " . $e->getMessage() . "');
            window.history.back();
        </script>";
    }
} else {
    header("Location: evaluasi_wali.php");
    exit;
}
?>
