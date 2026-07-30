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
        $nisn = $row['nisn'] ?? '';
        $nomor_santri = $row['nomor_santri'] ?? '';
        $nama = $row['nama'] ?? '';
        $tempat_lahir = $row['tempat_lahir'] ?? '';
        $tanggal_lahir = !empty($row['tanggal_lahir']) ? $row['tanggal_lahir'] : null;
        $jenis_kelamin = $row['jenis_kelamin'] ?? 'L';
        $status_dalam_keluarga = $row['status_dalam_keluarga'] ?? '';
        $anak_ke = !empty($row['anak_ke']) ? (int)$row['anak_ke'] : null;
        $alamat = $row['alamat'] ?? '';
        $sekolah_asal = $row['sekolah_asal'] ?? '';
        $diterima_di_kelas = $row['diterima_di_kelas'] ?? '';
        $diterima_pada_tanggal = !empty($row['diterima_pada_tanggal']) ? $row['diterima_pada_tanggal'] : null;
        $id_kelas_import = !empty($row['id_kelas']) ? (int)$row['id_kelas'] : null;
        $tahun_ajaran = $row['tahun_ajaran'] ?? '';
        $nama_ayah = $row['nama_ayah'] ?? '';
        $nama_ibu = $row['nama_ibu'] ?? '';
        $pekerjaan_ayah = $row['pekerjaan_ayah'] ?? '';
        $pekerjaan_ibu = $row['pekerjaan_ibu'] ?? '';
        $alamat_orang_tua = $row['alamat_orang_tua'] ?? '';
        $nama_wali = $row['nama_wali'] ?? '';
        $pekerjaan_wali = $row['pekerjaan_wali'] ?? '';
        $no_handphone = $row['no_handphone'] ?? '';
        
        // Cek duplikat (nisn atau nomor_santri)
        $cek = db_query(
            "SELECT id_siswa FROM siswa WHERE (nisn = ? AND nisn != '') OR (nomor_santri = ? AND nomor_santri != '')",
            [$nisn, $nomor_santri]
        );
        
        if ($cek && mysqli_num_rows($cek) == 0 && !empty($nama) && !empty($nomor_santri)) {
            $inserted = db_execute(
                "INSERT INTO siswa (nisn, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, status_dalam_keluarga, anak_ke, nomor_santri, id_kelas, tahun_ajaran, alamat, sekolah_asal, diterima_di_kelas, diterima_pada_tanggal, nama_ayah, nama_ibu, pekerjaan_ayah, pekerjaan_ibu, alamat_orang_tua, nama_wali, pekerjaan_wali, no_handphone, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif')",
                [$nisn, $nama, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $status_dalam_keluarga, $anak_ke, $nomor_santri, $id_kelas_import, $tahun_ajaran, $alamat, $sekolah_asal, $diterima_di_kelas, $diterima_pada_tanggal, $nama_ayah, $nama_ibu, $pekerjaan_ayah, $pekerjaan_ibu, $alamat_orang_tua, $nama_wali, $pekerjaan_wali, $no_handphone]
            );
            if ($inserted) {
                $success_count++;
            } else {
                $error_count++;
                file_put_contents('import_error.log', "MySQL Error saat insert siswa: $nama ($nomor_santri)\n", FILE_APPEND);
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