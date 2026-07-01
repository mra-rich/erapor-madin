<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_VIEW_REPORTS);

// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header JSON
header('Content-Type: application/json');

// Fungsi untuk menangani error
function handleError($message)
{
    error_log("Error in get_nilai_siswa.php: " . $message);
    http_response_code(500);
    echo json_encode(['error' => $message]);
    exit;
}

// Fungsi untuk membersihkan output
function cleanOutput($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

try {
    if (!isset($_POST['siswa']) || !isset($_POST['semester'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID siswa atau semester tidak ditemukan']);
        exit;
    }

    $id_siswa = (int)mysqli_real_escape_string($koneksi, $_POST['siswa']);
    $semester = (int)mysqli_real_escape_string($koneksi, $_POST['semester']);
    
    // --- PROTEKSI IDOR WALI KELAS ---
    $is_wali = ($_SESSION['peran'] === 'Wali Kelas');
    $id_pengguna = (int)$_SESSION['id_pengguna'];

    if ($is_wali) {
        $check_query = "SELECT s.id_siswa FROM siswa s JOIN kelas k ON s.id_kelas = k.id_kelas WHERE s.id_siswa = ? AND k.id_wali_kelas = ?";
        $stmt_check = mysqli_prepare($koneksi, $check_query);
        mysqli_stmt_bind_param($stmt_check, "ii", $id_siswa, $id_pengguna);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        if (mysqli_num_rows($result_check) == 0) {
            http_response_code(403);
            echo json_encode(['error' => 'Anda tidak memiliki akses untuk melihat nilai siswa ini.']);
            exit;
        }
        mysqli_stmt_close($stmt_check);
    }
    // ---------------------------------
    
    error_log("Processing request for siswa ID: " . $id_siswa . " and semester: " . $semester);

    // Query untuk mengambil data nilai berdasarkan semester
    $query_nilai = "SELECT mp.nama_mapel, mp.nama_mapel_arab, mp.kkm, n.nilai_angka 
                    FROM transaksi_raport tr
                    JOIN nilai n ON tr.id_transaksi = n.id_transaksi
                    JOIN mata_pelajaran mp ON n.id_mapel = mp.id_mapel
                    WHERE tr.id_siswa = ? AND tr.semester = ?";

    $stmt = mysqli_prepare($koneksi, $query_nilai);
    if (!$stmt) {
        throw new Exception("Error preparing nilai query: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "ii", $id_siswa, $semester);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error executing nilai query: " . mysqli_stmt_error($stmt));
    }

    $result_nilai = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    // Ambil data absensi
    $query_absensi = "SELECT a.sakit, a.izin, a.tanpa_keterangan 
                      FROM absensi a 
                      JOIN transaksi_raport tr ON a.id_transaksi = tr.id_transaksi 
                      WHERE tr.id_siswa = ? AND tr.semester = ?
                      ORDER BY tr.id_transaksi DESC LIMIT 1";
    $stmt_absensi = mysqli_prepare($koneksi, $query_absensi);
    if (!$stmt_absensi) {
        throw new Exception("Error preparing absensi query: " . mysqli_error($koneksi));
    }
    mysqli_stmt_bind_param($stmt_absensi, "ii", $id_siswa, $semester);
    if (!mysqli_stmt_execute($stmt_absensi)) {
        throw new Exception("Error executing absensi query: " . mysqli_stmt_error($stmt_absensi));
    }
    $result_absensi = mysqli_stmt_get_result($stmt_absensi);
    mysqli_stmt_close($stmt_absensi);

    // Query untuk mengambil data kepribadian
    $query_kepribadian = "SELECT k.kelakuan, k.kerajinan, k.kerapian
                         FROM transaksi_raport tr
                         JOIN kepribadian k ON tr.id_transaksi = k.id_transaksi
                         WHERE tr.id_siswa = ? AND tr.semester = ?
                         ORDER BY tr.id_transaksi DESC LIMIT 1";

    $stmt = mysqli_prepare($koneksi, $query_kepribadian);
    if (!$stmt) {
        throw new Exception("Error preparing kepribadian query: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "ii", $id_siswa, $semester);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error executing kepribadian query: " . mysqli_stmt_error($stmt));
    }

    $result_kepribadian = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    // Query untuk mengambil catatan wali
    $query_catatan = "SELECT c.catatan
                      FROM transaksi_raport tr
                      JOIN catatan_wali_kelas c ON tr.id_transaksi = c.id_transaksi
                      WHERE tr.id_siswa = ? AND tr.semester = ?
                      ORDER BY tr.id_transaksi DESC LIMIT 1";

    $stmt = mysqli_prepare($koneksi, $query_catatan);
    if (!$stmt) {
        throw new Exception("Error preparing catatan query: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "ii", $id_siswa, $semester);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error executing catatan query: " . mysqli_stmt_error($stmt));
    }

    $result_catatan = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    // Query untuk mengambil data ekstrakurikuler
    $query_ekskul = "SELECT e.pramuka, e.pmr, e.paskibra
                      FROM transaksi_raport tr
                      JOIN ekstrakurikuler e ON tr.id_transaksi = e.id_transaksi
                      WHERE tr.id_siswa = ? AND tr.semester = ?
                      ORDER BY tr.id_transaksi DESC LIMIT 1";

    $stmt_ekskul = mysqli_prepare($koneksi, $query_ekskul);
    if (!$stmt_ekskul) {
        throw new Exception("Error preparing ekskul query: " . mysqli_error($koneksi));
    }
    mysqli_stmt_bind_param($stmt_ekskul, "ii", $id_siswa, $semester);
    mysqli_stmt_execute($stmt_ekskul);
    $result_ekskul = mysqli_stmt_get_result($stmt_ekskul);
    mysqli_stmt_close($stmt_ekskul);

    $data = [
        'nilai' => [],
        'absensi' => [
            'sakit' => '0',
            'izin' => '0',
            'alpa' => '0'
        ],
        'kepribadian' => [
            'kelakuan' => '-',
            'kerajinan' => '-',
            'kerapian' => '-'
        ],
        'ekstrakurikuler' => [
            'pramuka' => '-',
            'pmr' => '-',
            'paskibra' => '-'
        ],
        'catatan' => ''
    ];

    // Proses data nilai
    if ($result_nilai && mysqli_num_rows($result_nilai) > 0) {
        while ($row = mysqli_fetch_assoc($result_nilai)) {
            $data['nilai'][] = [
                'nama_mapel' => cleanOutput($row['nama_mapel']),
                'nama_mapel_arab' => cleanOutput($row['nama_mapel_arab']),
                'kategori' => 'Mata Pelajaran',
                'kkm' => cleanOutput($row['kkm'] ?? '65'),
                'nilai_angka' => cleanOutput($row['nilai_angka'])
            ];
        }
    }

    // Proses data absensi
    if ($result_absensi && mysqli_num_rows($result_absensi) > 0) {
        if ($row = mysqli_fetch_assoc($result_absensi)) {
            $data['absensi'] = [
                'sakit' => cleanOutput($row['sakit'] ?? '0'),
                'izin' => cleanOutput($row['izin'] ?? '0'),
                'alpa' => cleanOutput($row['tanpa_keterangan'] ?? '0')
            ];
        }
    }

    // Proses data kepribadian
    if ($result_kepribadian && mysqli_num_rows($result_kepribadian) > 0) {
        if ($row = mysqli_fetch_assoc($result_kepribadian)) {
            $data['kepribadian'] = [
                'kelakuan' => cleanOutput($row['kelakuan'] ?? '-'),
                'kerajinan' => cleanOutput($row['kerajinan'] ?? '-'),
                'kerapian' => cleanOutput($row['kerapian'] ?? '-')
            ];
        }
    }

    // Proses data catatan
    if ($result_catatan && mysqli_num_rows($result_catatan) > 0) {
        if ($row = mysqli_fetch_assoc($result_catatan)) {
            $data['catatan'] = cleanOutput($row['catatan'] ?? '');
        }
    }

    // Proses data ekstrakurikuler
    if ($result_ekskul && mysqli_num_rows($result_ekskul) > 0) {
        if ($row = mysqli_fetch_assoc($result_ekskul)) {
            $data['ekstrakurikuler'] = [
                'pramuka' => cleanOutput($row['pramuka'] ?? '-'),
                'pmr' => cleanOutput($row['pmr'] ?? '-'),
                'paskibra' => cleanOutput($row['paskibra'] ?? '-')
            ];
        }
    }

    error_log("Successfully processed data for siswa ID: " . $id_siswa);
    echo json_encode($data);
} catch (Exception $e) {
    error_log("Exception in get_nilai_siswa.php: " . $e->getMessage());
    handleError($e->getMessage());
}
