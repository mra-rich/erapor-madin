<?php
require 'koneksi.php';
require 'cek_sesi.php';
require 'vendor/autoload.php';

use Shuchkin\SimpleXLSX;

header('Content-Type: application/json');

restrict_roles(RBAC_MANAGE_MASTER_DATA);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['file_excel_kelas']) || $_FILES['file_excel_kelas']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupload file']);
    exit;
}

$file = $_FILES['file_excel_kelas'];
$allowed_ext = ['xlsx', 'xls'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed_ext)) {
    echo json_encode(['status' => 'error', 'message' => 'Format file tidak didukung. Harus .xlsx']);
    exit;
}

if ($xlsx = SimpleXLSX::parse($file['tmp_name'])) {
    $data = $xlsx->rows();
    
    if (count($data) <= 1) {
        echo json_encode(['status' => 'error', 'message' => 'File Excel kosong atau hanya berisi header']);
        exit;
    }

    // Get valid tingkatan master data
    $tingkat_master = [];
    $res_tingkat = mysqli_query($koneksi, "SELECT id_tingkat, nama_tingkat FROM tingkat_kelas");
    while($row = mysqli_fetch_assoc($res_tingkat)){
        $tingkat_master[strtolower(trim($row['nama_tingkat']))] = $row['id_tingkat'];
    }

    $valid_data = [];
    $invalid_data = [];
    
    // Asumsikan baris pertama adalah header
    for ($i = 1; $i < count($data); $i++) {
        $row = $data[$i];
        
        // Skip baris kosong
        if (empty(array_filter($row))) continue;
        
        // Map kolom (Tingkatan, Kelas, Rombel, Wali Kelas)
        $tingkatan = trim($row[0] ?? '');
        $kelas = trim($row[1] ?? '');
        $rombel = trim($row[2] ?? '');
        $wali_kelas_input = trim($row[3] ?? '');

        
        $error = [];
        $id_tingkat = null;
        
        if (empty($tingkatan)) {
            $error[] = "Tingkatan kosong";
        } else {
            $tingkatan_key = strtolower($tingkatan);
            if(isset($tingkat_master[$tingkatan_key])){
                $id_tingkat = $tingkat_master[$tingkatan_key];
            } else {
                $error[] = "Tingkatan '$tingkatan' tidak dikenali di master data";
            }
        }
        
        if (empty($kelas)) $error[] = "Kelas kosong";
        if (empty($rombel)) $error[] = "Rombel kosong";
        
        $id_wali_kelas = null;
        if (empty($wali_kelas_input)) {
            $error[] = "Wali Kelas kosong";
        } else {
            // Cek di DB
            $stmt_wali = mysqli_prepare($koneksi, "SELECT id_pengguna FROM pengguna WHERE (nama LIKE ? OR username = ?) AND peran IN ('Guru', 'Wali Kelas') LIMIT 1");
            $like_input = '%' . $wali_kelas_input . '%';
            mysqli_stmt_bind_param($stmt_wali, "ss", $like_input, $wali_kelas_input);
            mysqli_stmt_execute($stmt_wali);
            $res_wali = mysqli_stmt_get_result($stmt_wali);
            if ($row_wali = mysqli_fetch_assoc($res_wali)) {
                $id_wali_kelas = $row_wali['id_pengguna'];
            } else {
                $error[] = "Wali Kelas '$wali_kelas_input' tidak ditemukan di data Pengguna";
            }
            mysqli_stmt_close($stmt_wali);
        }
        
        $item = [
            'baris' => $i + 1,
            'tingkatan' => $tingkatan,
            'id_tingkat' => $id_tingkat,
            'kelas' => $kelas,
            'rombel' => $rombel,
            'id_wali_kelas' => $id_wali_kelas
        ];

        if (empty($error)) {
            $valid_data[] = $item;
        } else {
            $item['alasan'] = implode(', ', $error);
            $invalid_data[] = $item;
        }
    }

    echo json_encode([
        'status' => 'success',
        'valid_count' => count($valid_data),
        'invalid_count' => count($invalid_data),
        'valid_data' => $valid_data,
        'invalid_data' => $invalid_data
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal membaca file Excel: ' . SimpleXLSX::parseError()
    ]);
}
?>
