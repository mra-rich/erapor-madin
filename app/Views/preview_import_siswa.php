<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
require 'vendor/autoload.php';
restrict_roles(RBAC_VIEW_REPORTS);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        echo json_encode(['status' => 'error', 'message' => 'Token CSRF tidak valid.']);
        exit;
    }

    if (isset($_FILES['file_excel']) && $_FILES['file_excel']['error'] == 0) {
        $file_name = $_FILES['file_excel']['name'];
        $file_tmp = $_FILES['file_excel']['tmp_name'];
        
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if ($ext == 'xlsx') {
            if ($xlsx = Shuchkin\SimpleXLSX::parse($file_tmp)) {
                $rows = $xlsx->rows();
                $header = array_shift($rows);
                
                $valid_data = [];
                $invalid_data = [];
                $baris = 1;
                
                $existing_nisn = [];
                $existing_nomor = [];
                $q = mysqli_query($koneksi, "SELECT nisn, nomor_santri FROM siswa");
                while ($r = mysqli_fetch_assoc($q)) {
                    if (!empty($r['nisn'])) $existing_nisn[] = $r['nisn'];
                    if (!empty($r['nomor_santri'])) $existing_nomor[] = $r['nomor_santri'];
                }
                
                foreach ($rows as $row) {
                    $baris++;
                    if (empty($row[1]) && empty($row[2]) && empty($row[3])) {
                        continue;
                    }
                    
                    $nisn = trim($row[1] ?? '');
                    $nomor_santri = trim($row[2] ?? '');
                    $nama = trim($row[3] ?? '');
                    
                    $reasons = [];
                    
                    if (empty($nama)) {
                        $reasons[] = 'Nama Kosong';
                    }
                    if (empty($nomor_santri)) {
                        $reasons[] = 'No. Induk Santri Kosong';
                    }
                    
                    if (!empty($nisn) && in_array($nisn, $existing_nisn)) {
                        $reasons[] = 'NISN sudah terdaftar';
                    }
                    if (!empty($nomor_santri) && in_array($nomor_santri, $existing_nomor)) {
                        $reasons[] = 'No. Induk Santri sudah terdaftar';
                    }
                    
                    $rowData = [
                        'baris' => $baris,
                        'nisn' => $nisn,
                        'nomor_santri' => $nomor_santri,
                        'nama' => $nama,
                        'tempat_lahir' => trim($row[4] ?? ''),
                        'tanggal_lahir' => trim($row[5] ?? ''),
                        'jenis_kelamin' => trim($row[6] ?? 'L'),
                        'status_dalam_keluarga' => trim($row[7] ?? ''),
                        'anak_ke' => trim($row[8] ?? ''),
                        'alamat' => trim($row[9] ?? ''),
                        'sekolah_asal' => trim($row[10] ?? ''),
                        'diterima_di_kelas' => trim($row[11] ?? ''),
                        'diterima_pada_tanggal' => trim($row[12] ?? ''),
                        'id_kelas' => trim($row[13] ?? ''),
                        'tahun_ajaran' => trim($row[14] ?? ''),
                        'nama_ayah' => trim($row[15] ?? ''),
                        'nama_ibu' => trim($row[16] ?? ''),
                        'pekerjaan_ayah' => trim($row[17] ?? ''),
                        'pekerjaan_ibu' => trim($row[18] ?? ''),
                        'alamat_orang_tua' => trim($row[19] ?? ''),
                        'nama_wali' => trim($row[20] ?? ''),
                        'pekerjaan_wali' => trim($row[21] ?? ''),
                        'no_handphone' => trim($row[22] ?? '')
                    ];
                    
                    if (count($reasons) > 0) {
                        $rowData['alasan'] = implode(', ', $reasons);
                        $invalid_data[] = $rowData;
                    } else {
                        $valid_data[] = $rowData;
                        if (!empty($nisn)) $existing_nisn[] = $nisn;
                        if (!empty($nomor_santri)) $existing_nomor[] = $nomor_santri;
                    }
                }
                
                echo json_encode([
                    'status' => 'success',
                    'valid' => $valid_data,
                    'invalid' => $invalid_data
                ]);
                exit;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal membaca Excel: ' . Shuchkin\SimpleXLSX::parseError()]);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Hanya menerima file .xlsx']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Silakan pilih file Excel.']);
        exit;
    }
}
?>
