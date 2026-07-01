<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'vendor/autoload.php';

restrict_roles(['Admin']);

use Shuchkin\SimpleXLSX;

header('Content-Type: application/json');

if (!isset($_FILES['file_excel_mapel']) || $_FILES['file_excel_mapel']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File tidak ditemukan atau gagal diupload']);
    exit;
}

$file = $_FILES['file_excel_mapel']['tmp_name'];

if ($xlsx = SimpleXLSX::parse($file)) {
    // Cek apakah sheet 'Pengaturan Mapel' ada
    $sheetNames = $xlsx->sheetNames();
    $sheetIndex = -1;
    foreach ($sheetNames as $index => $name) {
        if (strpos(strtolower($name), 'pengaturan mapel') !== false) {
            $sheetIndex = $index;
            break;
        }
    }
    
    // Jika tidak ketemu nama spesifik, pakai sheet pertama
    if ($sheetIndex == -1) $sheetIndex = 0;
    
    $rows = $xlsx->rows($sheetIndex);
    
    if (count($rows) <= 1) {
        echo json_encode(['success' => false, 'message' => 'File Excel kosong atau hanya berisi header']);
        exit;
    }
    
    // Ambil data referensi guru untuk validasi nama
    $guru_query = mysqli_query($koneksi, "SELECT id_pengguna, nama FROM pengguna WHERE peran IN ('Guru', 'Wali Kelas') AND status = 'Aktif'");
    $guru_map = []; // Map Nama => ID
    while ($g = mysqli_fetch_assoc($guru_query)) {
        // Simpan dalam huruf kecil dan trim untuk pencocokan yang lebih toleran
        $guru_map[strtolower(trim($g['nama']))] = $g['id_pengguna'];
    }

    $valid_data = [];
    $invalid_rows = [];
    $row_num = 2; // Mulai dari baris 2 karena baris 1 adalah header

    // Asumsi urutan kolom:
    // 0: ID Kelas, 1: Nama Kelas, 2: ID Mapel, 3: Nama Mapel, 4: Nama Kitab, 5: Nama Guru, 6: Status
    // Tunggu, urutan kolom di download template:
    // 0: ID Kelas, 1: Nama Kelas, 2: ID Mapel, 3: Nama Mapel, 4: Nama Kitab, 5: Nama Guru, 6: Status
    
    foreach ($rows as $index => $row) {
        if ($index === 0) continue; // Skip header
        
        // Pengecekan baris kosong
        if (empty(trim($row[0] ?? '')) || empty(trim($row[2] ?? ''))) {
            continue; 
        }

        $id_kelas = (int)trim($row[0]);
        $id_mapel = (int)trim($row[2]);
        $nama_kitab = trim($row[4] ?? '');
        $nama_guru = trim($row[5] ?? '');
        $status_input = trim($row[6] ?? '');
        
        $id_guru_valid = null;
        $is_valid = true;
        
        // Aturan validasi:
        // Jika Nama Guru diisi, cek apakah ada di database
        if (!empty($nama_guru)) {
            $nama_guru_lower = strtolower($nama_guru);
            if (isset($guru_map[$nama_guru_lower])) {
                $id_guru_valid = $guru_map[$nama_guru_lower];
            } else {
                $is_valid = false;
                $invalid_rows[] = [
                    'row' => $row_num,
                    'message' => "Nama Guru '{$nama_guru}' tidak ditemukan di database."
                ];
            }
        }
        
        // Logika Status Otomatis
        // JIKA TIDAK ADA GURUNYA DAN KITABNYA MAKA AUTO TIDAK AKTIF
        if (empty($nama_guru) && empty($nama_kitab)) {
            $status = 'Non-Aktif';
            $id_guru_valid = null; // Pastikan guru null kalau non-aktif otomatis
        } else {
            // Jika ada guru dan ada kitab, jadikan Aktif, kecuali di-set eksplisit Non-Aktif
            if (strtolower($status_input) == 'non-aktif' || strtolower($status_input) == 'non aktif') {
                $status = 'Non-Aktif';
                $id_guru_valid = null;
            } else {
                $status = 'Aktif';
            }
        }
        
        if ($is_valid) {
            $valid_data[] = [
                'id_kelas' => $id_kelas,
                'id_mapel' => $id_mapel,
                'nama_kitab' => $nama_kitab,
                'id_guru' => $id_guru_valid,
                'status' => $status
            ];
        }
        
        $row_num++;
    }

    echo json_encode([
        'success' => true,
        'total' => count($valid_data),
        'invalid_count' => count($invalid_rows),
        'invalid_rows' => $invalid_rows,
        'data' => $valid_data
    ]);

} else {
    echo json_encode(['success' => false, 'message' => SimpleXLSX::parseError()]);
}
