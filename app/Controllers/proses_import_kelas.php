<?php
require 'koneksi.php';
require 'cek_sesi.php';

header('Content-Type: application/json');

restrict_roles(RBAC_MANAGE_MASTER_DATA);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['status' => 'error', 'message' => 'Token CSRF tidak valid.']);
    exit;
}

if (!isset($data['import_data']) || !is_array($data['import_data'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
    exit;
}

$import_data = $data['import_data'];
$success_count = 0;
$fail_count = 0;

mysqli_begin_transaction($koneksi);

try {
    $stmt = mysqli_prepare($koneksi, "INSERT INTO kelas (id_tingkat, nama_kelas, nama_rombel, id_wali_kelas) VALUES (?, ?, ?, ?)");

    foreach ($import_data as $item) {
        $id_tingkat = $item['id_tingkat'];
        $kelas = $item['kelas'];
        $rombel = $item['rombel'];
        $id_wali_kelas = $item['id_wali_kelas'];

        // Cek duplikasi manual karena UNIQUE key mungkin belum terpasang dengan baik
        $cek = mysqli_query($koneksi, "SELECT id_kelas FROM kelas WHERE id_tingkat='$id_tingkat' AND nama_kelas='$kelas' AND nama_rombel='$rombel'");
        if(mysqli_num_rows($cek) > 0) {
            $fail_count++;
            continue; // Skip existing class
        }

        mysqli_stmt_bind_param($stmt, "issi", $id_tingkat, $kelas, $rombel, $id_wali_kelas);
        if (mysqli_stmt_execute($stmt)) {
            $success_count++;
        } else {
            $fail_count++;
        }
    }
    
    mysqli_commit($koneksi);
    
    echo json_encode([
        'status' => 'success', 
        'message' => "Berhasil mengimport $success_count data kelas. " . ($fail_count > 0 ? "($fail_count gagal/duplikat)" : "")
    ]);
    
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
    ]);
}
?>
