<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_VIEW_REPORTS);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Membaca input JSON
    $json = file_get_contents('php://input');
    file_put_contents('import_debug.log', "Received JSON: " . $json . "\n", FILE_APPEND);
    $data = json_decode($json, true);

    if (!$data) {
        file_put_contents('import_debug.log', "JSON Decode failed: " . json_last_error_msg() . "\n", FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'Data tidak valid atau kosong.']);
        exit;
    }

    if (!isset($data['csrf_token']) || !verify_csrf_token($data['csrf_token'])) {
        echo json_encode(['status' => 'error', 'message' => 'Token CSRF tidak valid.']);
        exit;
    }

    if (!isset($data['import_data']) || !is_array($data['import_data']) || count($data['import_data']) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Tidak ada data valid yang bisa disimpan.']);
        exit;
    }

    $success_count = 0;
    $error_count = 0;

    foreach ($data['import_data'] as $row) {
        $nisn = mysqli_real_escape_string($koneksi, $row['nisn'] ?? '');
        $nomor_santri = mysqli_real_escape_string($koneksi, $row['nomor_santri'] ?? '');
        $nama = mysqli_real_escape_string($koneksi, $row['nama'] ?? '');
        $tempat_lahir = mysqli_real_escape_string($koneksi, $row['tempat_lahir'] ?? '');
        
        $tanggal_lahir = !empty($row['tanggal_lahir']) ? "'" . mysqli_real_escape_string($koneksi, $row['tanggal_lahir']) . "'" : "NULL";
        $jenis_kelamin = mysqli_real_escape_string($koneksi, $row['jenis_kelamin'] ?? 'L');
        $status_dalam_keluarga = mysqli_real_escape_string($koneksi, $row['status_dalam_keluarga'] ?? '');
        $anak_ke = !empty($row['anak_ke']) ? intval($row['anak_ke']) : "NULL";
        $alamat = mysqli_real_escape_string($koneksi, $row['alamat'] ?? '');
        $sekolah_asal = mysqli_real_escape_string($koneksi, $row['sekolah_asal'] ?? '');
        $diterima_di_kelas = mysqli_real_escape_string($koneksi, $row['diterima_di_kelas'] ?? '');
        $diterima_pada_tanggal = !empty($row['diterima_pada_tanggal']) ? "'" . mysqli_real_escape_string($koneksi, $row['diterima_pada_tanggal']) . "'" : "NULL";
        $id_kelas = !empty($row['id_kelas']) ? intval($row['id_kelas']) : "NULL";
        $tahun_ajaran = mysqli_real_escape_string($koneksi, $row['tahun_ajaran'] ?? '');
        $nama_ayah = mysqli_real_escape_string($koneksi, $row['nama_ayah'] ?? '');
        $nama_ibu = mysqli_real_escape_string($koneksi, $row['nama_ibu'] ?? '');
        $pekerjaan_ayah = mysqli_real_escape_string($koneksi, $row['pekerjaan_ayah'] ?? '');
        $pekerjaan_ibu = mysqli_real_escape_string($koneksi, $row['pekerjaan_ibu'] ?? '');
        $alamat_orang_tua = mysqli_real_escape_string($koneksi, $row['alamat_orang_tua'] ?? '');
        $nama_wali = mysqli_real_escape_string($koneksi, $row['nama_wali'] ?? '');
        $pekerjaan_wali = mysqli_real_escape_string($koneksi, $row['pekerjaan_wali'] ?? '');
        $no_handphone = mysqli_real_escape_string($koneksi, $row['no_handphone'] ?? '');
        
        // Cek ganda hanya untuk memastikan (meski sudah dicek di preview)
        $cek = mysqli_query($koneksi, "SELECT id_siswa FROM siswa WHERE (nisn = '$nisn' AND nisn != '') OR (nomor_santri = '$nomor_santri' AND nomor_santri != '')");
        if (mysqli_num_rows($cek) == 0 && !empty($nama) && !empty($nomor_santri)) {
            $query = "INSERT INTO siswa (nisn, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, status_dalam_keluarga, anak_ke, nomor_santri, id_kelas, tahun_ajaran, alamat, sekolah_asal, diterima_di_kelas, diterima_pada_tanggal, nama_ayah, nama_ibu, pekerjaan_ayah, pekerjaan_ibu, alamat_orang_tua, nama_wali, pekerjaan_wali, no_handphone, status) 
                      VALUES ('$nisn', '$nama', '$tempat_lahir', $tanggal_lahir, '$jenis_kelamin', '$status_dalam_keluarga', $anak_ke, '$nomor_santri', $id_kelas, '$tahun_ajaran', '$alamat', '$sekolah_asal', '$diterima_di_kelas', $diterima_pada_tanggal, '$nama_ayah', '$nama_ibu', '$pekerjaan_ayah', '$pekerjaan_ibu', '$alamat_orang_tua', '$nama_wali', '$pekerjaan_wali', '$no_handphone', 'Aktif')";
            if (mysqli_query($koneksi, $query)) {
                $success_count++;
            } else {
                $error_count++;
                file_put_contents('import_error.log', "MySQL Error: " . mysqli_error($koneksi) . "\nQuery: " . $query . "\n\n", FILE_APPEND);
            }
        } else {
            $error_count++;
            file_put_contents('import_error.log', "Duplicate or empty name/nomor_santri. nisn: $nisn, nama: $nama, nomor: $nomor_santri\n", FILE_APPEND);
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => "Berhasil import $success_count santri. Gagal/Double: $error_count santri."
    ]);
    exit;
}
?>