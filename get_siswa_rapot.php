<?php
require 'koneksi.php';

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

try {
    if (isset($_POST['kelas'])) {
        $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);

        // Query untuk mengambil data siswa berdasarkan kelas
        $query = "SELECT s.*, k.nama_kelas 
                  FROM siswa s 
                  JOIN kelas k ON s.id_kelas = k.id_kelas 
                  WHERE s.id_kelas = ? 
                  ORDER BY s.nama ASC";

        $stmt = mysqli_prepare($koneksi, $query);
        if (!$stmt) {
            throw new Exception("Error preparing query: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param($stmt, "i", $kelas);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error executing query: " . mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        $html = "<option value=''>Pilih Siswa</option>";
        if (mysqli_num_rows($result) > 0) {
            while ($siswa = mysqli_fetch_assoc($result)) {
                $html .= "<option value='" . cleanOutput($siswa['id_siswa']) . "' 
                        data-nama='" . cleanOutput($siswa['nama']) . "'
                        data-nis='" . cleanOutput($siswa['nomor_santri']) . "'
                        data-kelas='" . cleanOutput($siswa['nama_kelas']) . "'
                        data-tahun-ajaran='" . cleanOutput($siswa['tahun_ajaran']) . "'>
                        " . cleanOutput($siswa['nama']) . " - " . cleanOutput($siswa['nomor_santri']) . "
                      </option>";
            }
        } else {
            $html = "<option value=''>Tidak ada siswa di kelas ini</option>";
        }

        echo json_encode(['html' => $html]);
    }
    // Endpoint untuk mengambil detail siswa
    else if (isset($_POST['siswa'])) {
        $id_siswa = mysqli_real_escape_string($koneksi, $_POST['siswa']);

        // Query untuk mengambil data siswa
        $query = "SELECT s.*, k.nama_kelas, t.tahun_ajaran 
                  FROM siswa s 
                  JOIN kelas k ON s.id_kelas = k.id_kelas 
                  LEFT JOIN transaksi_raport t ON s.id_siswa = t.id_siswa 
                  WHERE s.id_siswa = ? 
                  ORDER BY t.id_transaksi DESC LIMIT 1";

        $stmt = mysqli_prepare($koneksi, $query);
        if (!$stmt) {
            throw new Exception("Error preparing query: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param($stmt, "i", $id_siswa);
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
