<?php
// echo "<pre>";
// var_dump($_POST);
// echo "</pre>";
// exit;
require_once 'koneksi.php';

// Aktifkan mode error untuk debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Pastikan id_pengguna tidak kosong
    if (!isset($_SESSION['id_pengguna'])) {
        throw new Exception("ID pengguna tidak ditemukan. Silakan login kembali.");
    }

    $id_siswa = $_POST['id_siswa'] ?? null;
    $tahun_ajaran = $_POST['tahun_ajaran'] ?? null;
    $id_pengguna = $_SESSION['id_pengguna'];
    $izin = $_POST['izin'] ?? 0;
    $sakit = $_POST['sakit'] ?? 0;
    $tanpa_keterangan = $_POST['tanpa_keterangan'] ?? 0;
    $kelakuan = $_POST['kelakuan'] ?? '';
    $kerajinan = $_POST['kerajinan'] ?? '';
    $kerapian = $_POST['kerapian'] ?? '';
    $catatan = $_POST['catatan_wali_kelas'] ?? '';

    // Pastikan data utama tidak kosong
    if (empty($id_siswa) || empty($tahun_ajaran)) {
        throw new Exception("Data siswa atau tahun ajaran tidak boleh kosong.");
    }


    mysqli_begin_transaction($koneksi);

    // Insert transaksi raport
    $query_transaksi = "INSERT INTO transaksi_raport (id_siswa, tahun_ajaran, id_pengguna, semester) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query_transaksi);
    if (!$stmt) throw new Exception("Gagal menyiapkan query transaksi raport.");
    mysqli_stmt_bind_param($stmt, "isii", $id_siswa, $tahun_ajaran, $id_pengguna, $_POST['semester']);
    mysqli_stmt_execute($stmt);
    $id_transaksi = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt);

    // Insert absensi
    $query_absensi = "INSERT INTO absensi (id_transaksi, izin, sakit, tanpa_keterangan) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query_absensi);
    if (!$stmt) throw new Exception("Gagal menyiapkan query absensi.");
    mysqli_stmt_bind_param($stmt, "iiii", $id_transaksi, $izin, $sakit, $tanpa_keterangan);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Insert kepribadian
    $query_kepribadian = "INSERT INTO kepribadian (id_transaksi, kelakuan, kerajinan, kerapian) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query_kepribadian);
    if (!$stmt) throw new Exception("Gagal menyiapkan query kepribadian.");
    mysqli_stmt_bind_param($stmt, "isss", $id_transaksi, $kelakuan, $kerajinan, $kerapian);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Insert catatan wali kelas
    $query_catatan = "INSERT INTO catatan_wali_kelas (id_transaksi, catatan) VALUES (?, ?)";
    $stmt = mysqli_prepare($koneksi, $query_catatan);
    if (!$stmt) throw new Exception("Gagal menyiapkan query catatan wali kelas.");
    mysqli_stmt_bind_param($stmt, "is", $id_transaksi, $catatan);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Insert ekstrakurikuler
    $pramuka = $_POST['ekskul_pramuka'] ?? '';
    $pmr = $_POST['ekskul_pmr'] ?? '';
    $paskibra = $_POST['ekskul_paskibra'] ?? '';
    
    // Hanya insert jika ada setidaknya 1 ekskul yang diikuti
    if ($pramuka != '' || $pmr != '' || $paskibra != '') {
        $query_ekskul = "INSERT INTO ekstrakurikuler (id_transaksi, pramuka, pmr, paskibra) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query_ekskul);
        if (!$stmt) throw new Exception("Gagal menyiapkan query ekstrakurikuler.");
        mysqli_stmt_bind_param($stmt, "isss", $id_transaksi, $pramuka, $pmr, $paskibra);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Insert nilai
    $query_nilai = "INSERT INTO nilai (id_transaksi, id_mapel, nilai_angka) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query_nilai);
    if (!$stmt) throw new Exception("Gagal menyiapkan query nilai.");
    foreach ($_POST['nilai_angka'] as $id_mapel => $nilai) {
        mysqli_stmt_bind_param($stmt, "iid", $id_transaksi, $id_mapel, $nilai);
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);

    // Commit transaksi jika semua berhasil
    mysqli_commit($koneksi);
    echo "Data berhasil disimpan.";
    header("Location: data_nilai.php");
    exit;
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo "Terjadi kesalahan: " . $e->getMessage();
}

mysqli_close($koneksi);
