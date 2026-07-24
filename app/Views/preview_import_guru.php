<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
require_once dirname(__DIR__, 2) . "/vendor/autoload.php";
restrict_roles(RBAC_MANAGE_MASTER_DATA);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        echo json_encode(['status' => 'error', 'message' => 'Token CSRF tidak valid.']);
        exit;
    }

    if (isset($_FILES['file_excel_guru']) && $_FILES['file_excel_guru']['error'] == 0) {
        $file_name = $_FILES['file_excel_guru']['name'];
        $file_tmp = $_FILES['file_excel_guru']['tmp_name'];
        
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if (in_array($ext, ['xlsx', 'xls'])) {
            if ($xlsx = \Shuchkin\SimpleXLSX::parse($file_tmp)) {
                $rows = $xlsx->rows();
                $header = array_shift($rows);
                
                $valid_data = [];
                $invalid_data = [];
                $baris = 1;
                
                $existing_nip = [];
                $q = mysqli_query($koneksi, "SELECT nip FROM guru WHERE nip != ''");
                while ($r = mysqli_fetch_assoc($q)) {
                    $existing_nip[] = $r['nip'];
                }
                
                // Header: NO, NIP, NAMA_LENGKAP, JENIS_KELAMIN, TEMPAT_LAHIR, TANGGAL_LAHIR, ALAMAT, NO_HP, PENDIDIKAN_TERAKHIR, JABATAN, STATUS_GURU
                foreach ($rows as $row) {
                    $baris++;
                    if (empty($row[1]) && empty($row[2])) {
                        continue;
                    }
                    
                    $nip = trim($row[1] ?? '');
                    $nama = trim($row[2] ?? '');
                    
                    $reasons = [];
                    
                    if (empty($nama)) {
                        $reasons[] = 'Nama Kosong';
                    }
                    
                    if (!empty($nip) && in_array($nip, $existing_nip)) {
                        $reasons[] = 'NIP sudah terdaftar';
                    }
                    
                    $rowData = [
                        'baris' => $baris,
                        'nip' => $nip,
                        'nama_lengkap' => $nama,
                        'jenis_kelamin' => trim($row[3] ?? 'L'),
                        'tempat_lahir' => trim($row[4] ?? ''),
                        'tanggal_lahir' => trim($row[5] ?? ''),
                        'alamat' => trim($row[6] ?? ''),
                        'no_hp' => trim($row[7] ?? ''),
                        'peran' => trim($row[8] ?? 'Guru'),
                        'username' => trim($row[9] ?? '')
                    ];
                    
                    if (count($reasons) > 0) {
                        $rowData['alasan'] = implode(', ', $reasons);
                        $invalid_data[] = $rowData;
                    } else {
                        $valid_data[] = $rowData;
                        if (!empty($nip)) $existing_nip[] = $nip;
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
                echo json_encode(['status' => 'error', 'message' => 'Gagal membaca file: ' . \Shuchkin\SimpleXLSX::parseError()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ekstensi file tidak valid.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengupload file.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid.']);
}
?>
