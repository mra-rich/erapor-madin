<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_MANAGE_GRADES);

$id_pengguna = $_SESSION['id_pengguna'] ?? 0;
$peran = $_SESSION['peran'] ?? '';

if (!in_array($peran, ['Wali Kelas', 'Admin'])) {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kelas = $_POST['id_kelas'] ?? 0;
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

    $q_pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
    $data_pengaturan = mysqli_fetch_assoc($q_pengaturan);
    $tahun_aktif = $data_pengaturan['tahun_ajaran'];
    $semester_aktif = $data_pengaturan['semester'];

    mysqli_begin_transaction($koneksi);
    try {
        foreach ($id_siswa_arr as $id_siswa) {
            // 1. Dapatkan atau Buat transaksi_raport
            $q_cek_tr = mysqli_query($koneksi, "SELECT id_transaksi FROM transaksi_raport WHERE id_siswa = $id_siswa AND tahun_ajaran = '$tahun_aktif' AND semester = $semester_aktif");
            
            if (mysqli_num_rows($q_cek_tr) > 0) {
                $tr = mysqli_fetch_assoc($q_cek_tr);
                $id_transaksi = $tr['id_transaksi'];
            } else {
                // Buat transaksi raport baru
                mysqli_query($koneksi, "INSERT INTO transaksi_raport (id_siswa, id_pengguna, tahun_ajaran, semester) VALUES ($id_siswa, $id_pengguna, '$tahun_aktif', $semester_aktif)");
                $id_transaksi = mysqli_insert_id($koneksi);
            }

            // Data untuk diinsert
            $v_kelakuan = mysqli_real_escape_string($koneksi, $kelakuan[$id_siswa] ?? '');
            $v_kerajinan = mysqli_real_escape_string($koneksi, $kerajinan[$id_siswa] ?? '');
            $v_kerapian = mysqli_real_escape_string($koneksi, $kerapian[$id_siswa] ?? '');
            $v_kedisiplinan = mysqli_real_escape_string($koneksi, $kedisiplinan[$id_siswa] ?? '');
            
            $v_baca_quran = mysqli_real_escape_string($koneksi, $baca_quran[$id_siswa] ?? '');
            $v_baca_kitab = mysqli_real_escape_string($koneksi, $baca_kitab[$id_siswa] ?? '');
            $v_muhafadhoh = mysqli_real_escape_string($koneksi, $muhafadhoh[$id_siswa] ?? '');
            $v_kaligrafi = mysqli_real_escape_string($koneksi, $kaligrafi[$id_siswa] ?? '');
            
            $v_sakit = (int)($sakit_arr[$id_siswa] ?? 0);
            $v_izin = (int)($izin_arr[$id_siswa] ?? 0);
            $v_alpha = (int)($alpha_arr[$id_siswa] ?? 0);
            
            $v_catatan = mysqli_real_escape_string($koneksi, $catatan[$id_siswa] ?? '');

            // 2. Kepribadian (Upsert)
            $q_cek_kp = mysqli_query($koneksi, "SELECT id_kepribadian FROM kepribadian WHERE id_transaksi = $id_transaksi");
            if (mysqli_num_rows($q_cek_kp) > 0) {
                mysqli_query($koneksi, "UPDATE kepribadian SET kelakuan = '$v_kelakuan', kerajinan = '$v_kerajinan', kerapian = '$v_kerapian', kedisiplinan = '$v_kedisiplinan' WHERE id_transaksi = $id_transaksi");
            } else {
                mysqli_query($koneksi, "INSERT INTO kepribadian (id_transaksi, kelakuan, kerajinan, kerapian, kedisiplinan) VALUES ($id_transaksi, '$v_kelakuan', '$v_kerajinan', '$v_kerapian', '$v_kedisiplinan')");
            }

            // 3. Ekstrakurikuler (Upsert)
            $q_cek_ex = mysqli_query($koneksi, "SELECT id_ekstrakurikuler FROM ekstrakurikuler WHERE id_transaksi = $id_transaksi");
            if (mysqli_num_rows($q_cek_ex) > 0) {
                mysqli_query($koneksi, "UPDATE ekstrakurikuler SET baca_quran = '$v_baca_quran', baca_kitab = '$v_baca_kitab', muhafadhoh = '$v_muhafadhoh', kaligrafi = '$v_kaligrafi' WHERE id_transaksi = $id_transaksi");
            } else {
                mysqli_query($koneksi, "INSERT INTO ekstrakurikuler (id_transaksi, baca_quran, baca_kitab, muhafadhoh, kaligrafi) VALUES ($id_transaksi, '$v_baca_quran', '$v_baca_kitab', '$v_muhafadhoh', '$v_kaligrafi')");
            }

            // 4. Catatan Wali Kelas (Upsert)
            $q_cek_cw = mysqli_query($koneksi, "SELECT id_catatan FROM catatan_wali_kelas WHERE id_transaksi = $id_transaksi");
            if (mysqli_num_rows($q_cek_cw) > 0) {
                mysqli_query($koneksi, "UPDATE catatan_wali_kelas SET catatan = '$v_catatan' WHERE id_transaksi = $id_transaksi");
            } else {
                mysqli_query($koneksi, "INSERT INTO catatan_wali_kelas (id_transaksi, catatan) VALUES ($id_transaksi, '$v_catatan')");
            }

            // 5. Absensi (Upsert)
            $q_cek_ab = mysqli_query($koneksi, "SELECT id_absensi FROM absensi WHERE id_transaksi = $id_transaksi");
            if (mysqli_num_rows($q_cek_ab) > 0) {
                mysqli_query($koneksi, "UPDATE absensi SET sakit = $v_sakit, izin = $v_izin, tanpa_keterangan = $v_alpha WHERE id_transaksi = $id_transaksi");
            } else {
                mysqli_query($koneksi, "INSERT INTO absensi (id_transaksi, sakit, izin, tanpa_keterangan) VALUES ($id_transaksi, $v_sakit, $v_izin, $v_alpha)");
            }
        }
        
        mysqli_commit($koneksi);
        
        // Catat aktivitas
        $query_log = "INSERT INTO log_aktivitas (id_pengguna, aktivitas, tabel_terkait, waktu) VALUES ('$id_pengguna', 'Menyimpan evaluasi kelas binaan (ID Kelas: $id_kelas)', 'kepribadian, ekstrakurikuler, catatan_wali_kelas', NOW())";
        mysqli_query($koneksi, $query_log);

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
