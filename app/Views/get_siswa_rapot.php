<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_VIEW_ALL);

// Set header JSON
header('Content-Type: application/json');

// Fungsi untuk menangani error
function handleError($message)
{
    http_response_code(500);
    echo json_encode(['error' => $message]);
    exit;
}

// Fungsi untuk membersihkan output
function cleanOutput($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Fungsi format tanggal Indonesia
function formatTanggalIndo($tanggal) {
    if (!$tanggal || $tanggal == '0000-00-00' || $tanggal == '-') return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $pecahkan = explode('-', $tanggal);
    if (count($pecahkan) === 3) {
        return (int)$pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }
    return $tanggal;
}

try {
    if (isset($_POST['kelas'])) {
        $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);

        // Proteksi IDOR Wali Kelas
        $is_wali = ($_SESSION['peran'] === 'Wali Kelas');
        $id_pengguna = (int)$_SESSION['id_pengguna'];

        $q_ta = mysqli_query($koneksi, "SELECT tahun_ajaran FROM pengaturan LIMIT 1");
        $ta_aktif = mysqli_fetch_assoc($q_ta)['tahun_ajaran'];

        // Query untuk mengambil data siswa berdasarkan kelas dan riwayat
        $query = "SELECT s.*, r.status_kenaikan as status_kenaikan, k.nama_kelas 
                  FROM riwayat_kelas r
                  JOIN siswa s ON r.id_siswa = s.id_siswa 
                  JOIN kelas k ON r.id_kelas = k.id_kelas 
                  WHERE r.id_kelas = ? AND r.tahun_ajaran = ? AND s.status = 'Aktif' ";
        
        if ($is_wali) {
            $query .= " AND k.id_wali_kelas = ? ";
        }
        $query .= " ORDER BY s.nama ASC";

        $stmt = mysqli_prepare($koneksi, $query);
        if (!$stmt) {
            throw new Exception("Error preparing query: " . mysqli_error($koneksi));
        }

        if ($is_wali) {
            mysqli_stmt_bind_param($stmt, "isi", $kelas, $ta_aktif, $id_pengguna);
        } else {
            mysqli_stmt_bind_param($stmt, "is", $kelas, $ta_aktif);
        }
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error executing query: " . mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        $data_siswa = [];
        if (mysqli_num_rows($result) > 0) {
            while ($siswa = mysqli_fetch_assoc($result)) {
                $data_siswa[] = [
                    'id_siswa' => $siswa['id_siswa'],
                    'nama' => $siswa['nama'],
                    'nis' => $siswa['nomor_santri'],
                    'nisn' => isset($siswa['nisn']) ? $siswa['nisn'] : '-',
                    'kelas' => $siswa['nama_kelas'],
                    'tempat_lahir' => isset($siswa['tempat_lahir']) && $siswa['tempat_lahir'] ? $siswa['tempat_lahir'] : '-',
                    'tanggal_lahir' => isset($siswa['tanggal_lahir']) ? formatTanggalIndo($siswa['tanggal_lahir']) : '-',
                    'status_kenaikan' => isset($siswa['status_kenaikan']) && $siswa['status_kenaikan'] ? $siswa['status_kenaikan'] : '-'
                ];
            }
        }

        echo json_encode(['status' => 'success', 'data' => $data_siswa]);
    }
    // Endpoint untuk mengambil detail siswa
    else if (isset($_POST['siswa'])) {
        $id_siswa = mysqli_real_escape_string($koneksi, $_POST['siswa']);

        // Proteksi IDOR Wali Kelas
        $is_wali = ($_SESSION['peran'] === 'Wali Kelas');
        $id_pengguna = (int)$_SESSION['id_pengguna'];

        // Query untuk mengambil data siswa
        $query = "SELECT s.*, k.nama_kelas, t.tahun_ajaran 
                  FROM siswa s 
                  JOIN kelas k ON s.id_kelas = k.id_kelas 
                  LEFT JOIN transaksi_raport t ON s.id_siswa = t.id_siswa 
                  WHERE s.id_siswa = ? AND s.status = 'Aktif' ";
        
        if ($is_wali) {
            $query .= " AND k.id_wali_kelas = ? ";
        }
        $query .= " ORDER BY t.id_transaksi DESC LIMIT 1";

        $stmt = mysqli_prepare($koneksi, $query);
        if (!$stmt) {
            throw new Exception("Error preparing query: " . mysqli_error($koneksi));
        }

        if ($is_wali) {
            mysqli_stmt_bind_param($stmt, "ii", $id_siswa, $id_pengguna);
        } else {
            mysqli_stmt_bind_param($stmt, "i", $id_siswa);
        }
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error executing query: " . mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode([
                'nama' => cleanOutput($row['nama']),
                'nis' => cleanOutput($row['nomor_santri']),
                'kelas' => cleanOutput($row['nama_kelas']),
                'tahun_ajaran' => cleanOutput($row['tahun_ajaran'])
            ]);
        } else {
            throw new Exception("Data siswa tidak ditemukan");
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Parameter tidak valid']);
    }
} catch (Exception $e) {
    handleError($e->getMessage());
}
