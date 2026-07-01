<?php
require_once 'koneksi.php';
require_once 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_GRADES);

// Aktifkan mode error untuk debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    try {
        if (!isset($_SESSION['id_pengguna'])) {
            throw new Exception("ID pengguna tidak ditemukan. Silakan login kembali.");
        }

        $id_pengguna = $_SESSION['id_pengguna'];
        $id_mapel = isset($_POST['id_mapel']) ? (int)$_POST['id_mapel'] : 0;
        $id_kelas = isset($_POST['id_kelas']) ? (int)$_POST['id_kelas'] : 0;
        $nilai_array = $_POST['nilai'] ?? []; // Format: [id_siswa => nilai_angka]

        if ($id_mapel == 0 || $id_kelas == 0) {
            throw new Exception("Parameter kelas atau mapel tidak valid.");
        }

        // Ambil tahun ajaran & semester aktif
        $q_pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
        $data_pengaturan = mysqli_fetch_assoc($q_pengaturan);
        $tahun_aktif = $data_pengaturan['tahun_ajaran'] ?? '2024/2025';
        $semester_aktif = $data_pengaturan['semester'] ?? 1;

        mysqli_begin_transaction($koneksi);

        // Siapkan prepared statements
        $q_cek_transaksi = "SELECT id_transaksi FROM transaksi_raport WHERE id_siswa = ? AND tahun_ajaran = ? AND semester = ?";
        $stmt_cek_transaksi = mysqli_prepare($koneksi, $q_cek_transaksi);
        
        $q_insert_transaksi = "INSERT INTO transaksi_raport (id_siswa, tahun_ajaran, semester, id_pengguna) VALUES (?, ?, ?, ?)";
        $stmt_insert_transaksi = mysqli_prepare($koneksi, $q_insert_transaksi);

        $q_cek_nilai = "SELECT id_nilai FROM nilai WHERE id_transaksi = ? AND id_mapel = ?";
        $stmt_cek_nilai = mysqli_prepare($koneksi, $q_cek_nilai);

        $q_insert_nilai = "INSERT INTO nilai (id_transaksi, id_mapel, nilai_angka) VALUES (?, ?, ?)";
        $stmt_insert_nilai = mysqli_prepare($koneksi, $q_insert_nilai);

        $q_update_nilai = "UPDATE nilai SET nilai_angka = ? WHERE id_transaksi = ? AND id_mapel = ?";
        $stmt_update_nilai = mysqli_prepare($koneksi, $q_update_nilai);

        $jumlah_berhasil = 0;

        foreach ($nilai_array as $id_siswa => $nilai_angka) {
            // Abaikan jika nilai kosong
            if ($nilai_angka === '' || $nilai_angka === null) {
                continue;
            }
            
            $id_siswa = (int)$id_siswa;
            $nilai_angka = (float)$nilai_angka;

            // 1. Cek apakah transaksi_raport sudah ada
            mysqli_stmt_bind_param($stmt_cek_transaksi, "isi", $id_siswa, $tahun_aktif, $semester_aktif);
            mysqli_stmt_execute($stmt_cek_transaksi);
            $res_transaksi = mysqli_stmt_get_result($stmt_cek_transaksi);
            
            if ($row_transaksi = mysqli_fetch_assoc($res_transaksi)) {
                $id_transaksi = $row_transaksi['id_transaksi'];
            } else {
                // Buat transaksi baru
                mysqli_stmt_bind_param($stmt_insert_transaksi, "isii", $id_siswa, $tahun_aktif, $semester_aktif, $id_pengguna);
                mysqli_stmt_execute($stmt_insert_transaksi);
                $id_transaksi = mysqli_insert_id($koneksi);
            }

            // 2. Cek apakah nilai untuk mapel ini sudah ada di transaksi tsb
            mysqli_stmt_bind_param($stmt_cek_nilai, "ii", $id_transaksi, $id_mapel);
            mysqli_stmt_execute($stmt_cek_nilai);
            $res_nilai = mysqli_stmt_get_result($stmt_cek_nilai);

            if ($row_nilai = mysqli_fetch_assoc($res_nilai)) {
                // Update nilai
                mysqli_stmt_bind_param($stmt_update_nilai, "dii", $nilai_angka, $id_transaksi, $id_mapel);
                mysqli_stmt_execute($stmt_update_nilai);
            } else {
                // Insert nilai baru
                mysqli_stmt_bind_param($stmt_insert_nilai, "iid", $id_transaksi, $id_mapel, $nilai_angka);
                mysqli_stmt_execute($stmt_insert_nilai);
            }
            
            $jumlah_berhasil++;
        }

        // Catat di log aktivitas
        $aktivitas = "Menyimpan/memperbarui $jumlah_berhasil nilai santri secara massal pada id_mapel=$id_mapel, id_kelas=$id_kelas, $tahun_aktif Smt $semester_aktif";
        $q_log = "INSERT INTO log_aktivitas (id_pengguna, aksi, detail) VALUES (?, 'Input Nilai Massal', ?)";
        $stmt_log = mysqli_prepare($koneksi, $q_log);
        mysqli_stmt_bind_param($stmt_log, "is", $id_pengguna, $aktivitas);
        mysqli_stmt_execute($stmt_log);

        // Tutup statements
        mysqli_stmt_close($stmt_cek_transaksi);
        mysqli_stmt_close($stmt_insert_transaksi);
        mysqli_stmt_close($stmt_cek_nilai);
        mysqli_stmt_close($stmt_insert_nilai);
        mysqli_stmt_close($stmt_update_nilai);
        mysqli_stmt_close($stmt_log);

        mysqli_commit($koneksi);

        header("Location: input_nilai_massal.php?id_mapel=$id_mapel&id_kelas=$id_kelas&status=success");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        die("Terjadi kesalahan sistem: " . $e->getMessage());
    }
} else {
    header("Location: penilaian_mapel.php");
    exit;
}
