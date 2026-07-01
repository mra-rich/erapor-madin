<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_VIEW_REPORTS);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("Aksi diblokir karena token keamanan tidak valid (Potensi serangan CSRF).");
    }

    $kelas_asal = intval($_POST['kelas_asal']);
    $kelas_tujuan = intval($_POST['kelas_tujuan']);
    $tahun_ajaran = trim($_POST['tahun_ajaran']);
    $siswa_ids = isset($_POST['siswa_ids']) ? $_POST['siswa_ids'] : [];

    $action_type = isset($_POST['action_type']) ? $_POST['action_type'] : 'naik';

    if ($action_type === 'naik' && (empty($kelas_tujuan) || empty($tahun_ajaran))) {
        header("Location: kenaikan_kelas.php?kelas_asal=$kelas_asal&status=error&message=Kelas tujuan dan Tahun Ajaran harus diisi!");
        exit;
    }

    if (empty($siswa_ids) || !is_array($siswa_ids)) {
        header("Location: kenaikan_kelas.php?kelas_asal=$kelas_asal&status=error&message=Pilih minimal satu siswa untuk dinaikkan kelas!");
        exit;
    }

    // Update kelas & tahun ajaran untuk siswa yang dipilih
    $success_count = 0;
    
    mysqli_begin_transaction($koneksi);
    
    try {
        // Ambil tahun ajaran aktif dari pengaturan untuk mengupdate riwayat lama
        $q_ta = mysqli_query($koneksi, "SELECT tahun_ajaran FROM pengaturan LIMIT 1");
        $ta_aktif = mysqli_fetch_assoc($q_ta)['tahun_ajaran'];

        // Set 'Tidak' untuk semua siswa di kelas asal terlebih dahulu di tabel siswa dan riwayat_kelas
        $reset_query = "UPDATE siswa SET status_kenaikan = 'Tidak' WHERE id_kelas = ?";
        $stmt_reset = mysqli_prepare($koneksi, $reset_query);
        mysqli_stmt_bind_param($stmt_reset, "i", $kelas_asal);
        mysqli_stmt_execute($stmt_reset);
        mysqli_stmt_close($stmt_reset);

        $reset_riwayat = "UPDATE riwayat_kelas SET status_kenaikan = 'Tidak' WHERE id_kelas = ? AND tahun_ajaran = ?";
        $stmt_reset_rw = mysqli_prepare($koneksi, $reset_riwayat);
        mysqli_stmt_bind_param($stmt_reset_rw, "is", $kelas_asal, $ta_aktif);
        mysqli_stmt_execute($stmt_reset_rw);
        mysqli_stmt_close($stmt_reset_rw);

        if ($action_type === 'lulus') {
            // Update menjadi 'Alumni' dan cabut id_kelas (atau biarkan tapi status = Alumni)
            $update_query = "UPDATE siswa SET status = 'Alumni' WHERE id_siswa = ?";
            $stmt = mysqli_prepare($koneksi, $update_query);
            
            $update_riwayat_naik = "UPDATE riwayat_kelas SET status_kenaikan = 'Lulus' WHERE id_siswa = ? AND tahun_ajaran = ?";
            $stmt_rn = mysqli_prepare($koneksi, $update_riwayat_naik);

            foreach ($siswa_ids as $id_siswa) {
                $id = intval($id_siswa);
                
                // 1. Update riwayat lama jadi 'Naik' (lulus dari kelas)
                mysqli_stmt_bind_param($stmt_rn, "is", $id, $ta_aktif);
                mysqli_stmt_execute($stmt_rn);

                // 2. Update data siswa menjadi Alumni
                mysqli_stmt_bind_param($stmt, "i", $id);
                if(mysqli_stmt_execute($stmt)) {
                    $success_count++;
                }
            }
            if (isset($stmt_rn)) mysqli_stmt_close($stmt_rn);
            
            mysqli_commit($koneksi);
            require_once 'logger.php';
            catat_log($koneksi, $_SESSION['id_pengguna'], 'Kelulusan', "Meluluskan $success_count siswa menjadi Alumni");
            
            header("Location: kenaikan_kelas.php?status=success&message=Berhasil meluluskan $success_count siswa menjadi Alumni!");
        } else {
            // Update menjadi 'Naik' dan pindah kelas untuk siswa yang dipilih
            $update_query = "UPDATE siswa SET id_kelas = ?, tahun_ajaran = ?, status_kenaikan = 'Naik' WHERE id_siswa = ?";
            $stmt = mysqli_prepare($koneksi, $update_query);

            $update_riwayat_naik = "UPDATE riwayat_kelas SET status_kenaikan = 'Naik' WHERE id_siswa = ? AND tahun_ajaran = ?";
            $stmt_rn = mysqli_prepare($koneksi, $update_riwayat_naik);
            
            $insert_riwayat_baru = "INSERT IGNORE INTO riwayat_kelas (id_siswa, id_kelas, tahun_ajaran, status_kenaikan) VALUES (?, ?, ?, NULL)";
            $stmt_ib = mysqli_prepare($koneksi, $insert_riwayat_baru);
            
            foreach ($siswa_ids as $id_siswa) {
                $id = intval($id_siswa);
                
                // 1. Update riwayat lama jadi 'Naik'
                mysqli_stmt_bind_param($stmt_rn, "is", $id, $ta_aktif);
                mysqli_stmt_execute($stmt_rn);

                // 2. Update data siswa saat ini
                mysqli_stmt_bind_param($stmt, "isi", $kelas_tujuan, $tahun_ajaran, $id);
                if(mysqli_stmt_execute($stmt)) {
                    $success_count++;
                }

                // 3. Insert riwayat baru untuk kelas tujuan
                mysqli_stmt_bind_param($stmt_ib, "iis", $id, $kelas_tujuan, $tahun_ajaran);
                mysqli_stmt_execute($stmt_ib);
            }
            
            if (isset($stmt_rn)) mysqli_stmt_close($stmt_rn);
            if (isset($stmt_ib)) mysqli_stmt_close($stmt_ib);
            
            mysqli_commit($koneksi);
            
            // Log Aktivitas
            require_once 'logger.php';
            catat_log($koneksi, $_SESSION['id_pengguna'], 'Kenaikan Kelas', "Memindahkan $success_count siswa ke Kelas ID $kelas_tujuan (TA: $tahun_ajaran)");
            
            header("Location: kenaikan_kelas.php?status=success&message=Berhasil memindahkan $success_count siswa ke kelas baru!");
        }
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: kenaikan_kelas.php?kelas_asal=$kelas_asal&status=error&message=Terjadi kesalahan sistem: " . $e->getMessage());
    }
    
    if (isset($stmt)) mysqli_stmt_close($stmt);
    mysqli_close($koneksi);
    exit;
} else {
    header("Location: kenaikan_kelas.php");
    exit;
}
?>
