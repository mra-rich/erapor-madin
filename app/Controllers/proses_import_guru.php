<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    
    if (!isset($input['csrf_token']) || !verify_csrf_token($input['csrf_token'])) {
        echo json_encode(['status' => 'error', 'message' => 'Token CSRF tidak valid.']);
        exit;
    }
    
    $import_data = $input['import_data'] ?? [];
    
    if (empty($import_data)) {
        echo json_encode(['status' => 'error', 'message' => 'Tidak ada data valid untuk diimport.']);
        exit;
    }
    
    mysqli_begin_transaction($koneksi);
    $success_count = 0;
    
    try {
        foreach ($import_data as $data) {
            $nip = mysqli_real_escape_string($koneksi, $data['nip']);
            $nama_lengkap = mysqli_real_escape_string($koneksi, $data['nama_lengkap']);
            $jenis_kelamin = mysqli_real_escape_string($koneksi, $data['jenis_kelamin']);
            $tempat_lahir = mysqli_real_escape_string($koneksi, $data['tempat_lahir']);
            $tanggal_lahir = mysqli_real_escape_string($koneksi, $data['tanggal_lahir']);
            $alamat = mysqli_real_escape_string($koneksi, $data['alamat']);
            $no_hp = mysqli_real_escape_string($koneksi, $data['no_hp']);
            
            $peran_input = trim($data['peran'] ?? '');
            $valid_roles = ['Guru', 'Wali Kelas', 'Kepala Madrasah', 'Admin'];
            $peran = in_array($peran_input, $valid_roles) ? $peran_input : 'Guru';

            // Generate username and default password
            $username_input = trim($data['username'] ?? '');
            if (!empty($username_input)) {
                $username = mysqli_real_escape_string($koneksi, strtolower(str_replace(' ', '', $username_input)));
            } else {
                $username = empty($nip) ? strtolower(str_replace(' ', '', explode(' ', $nama_lengkap)[0])) . rand(10, 99) : $nip;
            }
            
            // Ensure username is unique
            $check_usr = mysqli_query($koneksi, "SELECT id_pengguna FROM pengguna WHERE username = '$username'");
            if (mysqli_num_rows($check_usr) > 0) {
                $username = $username . rand(10, 99);
            }
            $password_hash = password_hash('123456', PASSWORD_DEFAULT);
            
            // Insert ke tabel pengguna
            $query_pengguna = "INSERT INTO pengguna (nama, username, password, peran, status) 
                              VALUES ('$nama_lengkap', '$username', '$password_hash', '$peran', 'Aktif')";
            
            if (mysqli_query($koneksi, $query_pengguna)) {
                $id_pengguna = mysqli_insert_id($koneksi);
                
                // Insert ke tabel guru
                $query_guru = "INSERT INTO guru (id_pengguna, nip, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, no_hp, alamat) 
                              VALUES ('$id_pengguna', '$nip', '$nama_lengkap', '$jenis_kelamin', '$tempat_lahir', '$tanggal_lahir', '$no_hp', '$alamat')";
                
                if (mysqli_query($koneksi, $query_guru)) {
                    $success_count++;
                } else {
                    throw new Exception("Gagal menyimpan data guru untuk " . $nama_lengkap);
                }
            } else {
                throw new Exception("Gagal membuat akun untuk " . $nama_lengkap);
            }
        }
        
        mysqli_commit($koneksi);
        require_once 'logger.php';
        catat_log($koneksi, $_SESSION['id_pengguna'], 'Import Guru', "Mengimpor $success_count data guru.");
        echo json_encode(['status' => 'success', 'message' => "$success_count Data guru berhasil diimport. Username: (NIP / Nama / Input), Password default: 123456"]);
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid.']);
}
?>
