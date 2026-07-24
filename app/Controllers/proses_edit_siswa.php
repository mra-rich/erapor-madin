<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_STUDENTS);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $id_siswa = intval($_POST['id_siswa']);
    
    // Core fields
    $nisn = trim($_POST['nisn'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $nomor_santri = trim($_POST['nomor_santri'] ?? '');
    $id_kelas = !empty($_POST['id_kelas']) ? intval($_POST['id_kelas']) : "NULL";
    $tahun_ajaran = trim($_POST['tahun_ajaran'] ?? '');
    $status = trim($_POST['status'] ?? 'Aktif');

    // Additional fields
    $tempat_lahir = trim($_POST['tempat_lahir'] ?? '');
    $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']) . "'" : "NULL";
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? 'L');
    $status_dalam_keluarga = trim($_POST['status_dalam_keluarga'] ?? '');
    $anak_ke = !empty($_POST['anak_ke']) ? intval($_POST['anak_ke']) : "NULL";
    
    $alamat = trim($_POST['alamat'] ?? '');
    $sekolah_asal = trim($_POST['sekolah_asal'] ?? '');
    $diterima_di_kelas = trim($_POST['diterima_di_kelas'] ?? '');
    $diterima_pada_tanggal = !empty($_POST['diterima_pada_tanggal']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['diterima_pada_tanggal']) . "'" : "NULL";

    $nama_ayah = trim($_POST['nama_ayah'] ?? '');
    $nama_ibu = trim($_POST['nama_ibu'] ?? '');
    $pekerjaan_ayah = trim($_POST['pekerjaan_ayah'] ?? '');
    $pekerjaan_ibu = trim($_POST['pekerjaan_ibu'] ?? '');
    $alamat_orang_tua = trim($_POST['alamat_orang_tua'] ?? '');

    $nama_wali = trim($_POST['nama_wali'] ?? '');
    $pekerjaan_wali = trim($_POST['pekerjaan_wali'] ?? '');
    $no_handphone = trim($_POST['no_handphone'] ?? '');

    // Cek duplikasi No. Induk Santri
    $cek_query = "SELECT id_siswa FROM siswa WHERE nomor_santri = ? AND id_siswa != ?";
    $stmt_cek = mysqli_prepare($koneksi, $cek_query);
    mysqli_stmt_bind_param($stmt_cek, "si", $nomor_santri, $id_siswa);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);

    if (mysqli_stmt_num_rows($stmt_cek) > 0) {
        mysqli_stmt_close($stmt_cek);
        header("Location: data_santri.php?status=error&message=Gagal: No. Induk Santri sudah terdaftar!");
        exit;
    }
    mysqli_stmt_close($stmt_cek);

    // Siapkan data untuk di-update via Query Builder
    $data_update = [
        'nisn' => $nisn,
        'nama' => $nama,
        'tempat_lahir' => $tempat_lahir,
        'tanggal_lahir' => !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null,
        'jenis_kelamin' => $jenis_kelamin,
        'status_dalam_keluarga' => $status_dalam_keluarga,
        'anak_ke' => !empty($_POST['anak_ke']) ? (int)$_POST['anak_ke'] : null,
        'nomor_santri' => $nomor_santri,
        'id_kelas' => !empty($_POST['id_kelas']) ? (int)$_POST['id_kelas'] : null,
        'tahun_ajaran' => $tahun_ajaran,
        'alamat' => $alamat,
        'sekolah_asal' => $sekolah_asal,
        'diterima_di_kelas' => $diterima_di_kelas,
        'diterima_pada_tanggal' => !empty($_POST['diterima_pada_tanggal']) ? $_POST['diterima_pada_tanggal'] : null,
        'nama_ayah' => $nama_ayah,
        'nama_ibu' => $nama_ibu,
        'pekerjaan_ayah' => $pekerjaan_ayah,
        'pekerjaan_ibu' => $pekerjaan_ibu,
        'alamat_orang_tua' => $alamat_orang_tua,
        'nama_wali' => $nama_wali,
        'pekerjaan_wali' => $pekerjaan_wali,
        'no_handphone' => $no_handphone,
        'status' => $status
    ];

    $where_clause = [
        'id_siswa' => (int)$id_siswa
    ];

    // Eksekusi update via Query Builder
    if ($db->update('siswa', $data_update, $where_clause)) {
        require_once 'logger.php';
        catat_log($koneksi, $_SESSION['id_pengguna'], 'Edit Siswa', "Mengubah data siswa: {$nama}");

        header("Location: data_santri.php?status=success&message=Data siswa berhasil diperbarui!");
        exit();
    } else {
        $error_msg = mysqli_error($koneksi);
        error_log("Gagal memperbarui data: " . $error_msg);
        header("Location: data_santri.php?status=error&message=Gagal memperbarui data: " . $error_msg);
        exit();
    }
}
?>
