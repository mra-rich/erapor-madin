<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_VIEW_REPORTS);
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['import_status'] = 'gagal';
        $_SESSION['import_msg'] = 'Token CSRF tidak valid.';
        header("Location: import_nilai.php");
        exit;
    }

    $semester = (int)$_POST['semester'];
    $tahun_ajaran = $_SESSION['tahun_ajaran'];
    $id_pengguna = $_SESSION['id_pengguna'];

    if (isset($_FILES['file_excel']) && $_FILES['file_excel']['error'] == 0) {
        $filename = $_FILES['file_excel']['tmp_name'];
        
        if ($xlsx = SimpleXLSX::parse($filename)) {
            $rows = $xlsx->rows();
            
            if (count($rows) > 0) {
                // Baca baris pertama (header)
                $header = $rows[0];
                
                // Cari index mapel
                $mapel_indices = [];
                for ($i = 13; $i < count($header); $i++) {
                    if (strpos($header[$i], 'NILAI_') === 0) {
                        $parts = explode('_', $header[$i]);
                        $id_mapel = (int)$parts[1];
                        $mapel_indices[$i] = $id_mapel;
                    }
                }

                mysqli_begin_transaction($koneksi);
                try {
                    $baris_berhasil = 0;
                    for ($r = 1; $r < count($rows); $r++) {
                        $data = $rows[$r];
                        $id_siswa = (int)$data[0];
                        if ($id_siswa == 0) continue; // Skip jika ID kosong (mungkin baris kosong)

                        // Hapus transaksi lama jika ada (menghindari duplikat)
                        $query_cek = "SELECT id_transaksi FROM transaksi_raport WHERE id_siswa = ? AND semester = ? AND tahun_ajaran = ?";
                        $stmt_cek = mysqli_prepare($koneksi, $query_cek);
                        mysqli_stmt_bind_param($stmt_cek, "iis", $id_siswa, $semester, $tahun_ajaran);
                        mysqli_stmt_execute($stmt_cek);
                        $result_cek = mysqli_stmt_get_result($stmt_cek);
                        if ($row_cek = mysqli_fetch_assoc($result_cek)) {
                            $id_transaksi_lama = $row_cek['id_transaksi'];
                            mysqli_query($koneksi, "DELETE FROM transaksi_raport WHERE id_transaksi = '$id_transaksi_lama'");
                        }
                        mysqli_stmt_close($stmt_cek);

                        // Insert transaksi raport baru
                        $query_transaksi = "INSERT INTO transaksi_raport (id_siswa, tahun_ajaran, id_pengguna, semester) VALUES (?, ?, ?, ?)";
                        $stmt = mysqli_prepare($koneksi, $query_transaksi);
                        mysqli_stmt_bind_param($stmt, "isii", $id_siswa, $tahun_ajaran, $id_pengguna, $semester);
                        mysqli_stmt_execute($stmt);
                        $id_transaksi = mysqli_insert_id($koneksi);
                        mysqli_stmt_close($stmt);

                        // Insert absensi
                        $izin = (int)$data[3];
                        $sakit = (int)$data[4];
                        $alpa = (int)$data[5];
                        $query_absensi = "INSERT INTO absensi (id_transaksi, izin, sakit, tanpa_keterangan) VALUES (?, ?, ?, ?)";
                        $stmt = mysqli_prepare($koneksi, $query_absensi);
                        mysqli_stmt_bind_param($stmt, "iiii", $id_transaksi, $izin, $sakit, $alpa);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);

                        // Insert kepribadian
                        $kelakuan = $data[6];
                        $kerajinan = $data[7];
                        $kerapian = $data[8];
                        $query_kepribadian = "INSERT INTO kepribadian (id_transaksi, kelakuan, kerajinan, kerapian) VALUES (?, ?, ?, ?)";
                        $stmt = mysqli_prepare($koneksi, $query_kepribadian);
                        mysqli_stmt_bind_param($stmt, "isss", $id_transaksi, $kelakuan, $kerajinan, $kerapian);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);

                        // Insert catatan wali kelas
                        $catatan = $data[9];
                        $query_catatan = "INSERT INTO catatan_wali_kelas (id_transaksi, catatan) VALUES (?, ?)";
                        $stmt = mysqli_prepare($koneksi, $query_catatan);
                        mysqli_stmt_bind_param($stmt, "is", $id_transaksi, $catatan);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);

                        // Insert ekstrakurikuler
                        $pramuka = $data[10];
                        $pmr = $data[11];
                        $paskibra = $data[12];
                        if ($pramuka != '' || $pmr != '' || $paskibra != '') {
                            $query_ekskul = "INSERT INTO ekstrakurikuler (id_transaksi, pramuka, pmr, paskibra) VALUES (?, ?, ?, ?)";
                            $stmt = mysqli_prepare($koneksi, $query_ekskul);
                            mysqli_stmt_bind_param($stmt, "isss", $id_transaksi, $pramuka, $pmr, $paskibra);
                            mysqli_stmt_execute($stmt);
                            mysqli_stmt_close($stmt);
                        }

                        // Insert nilai mapel
                        $query_nilai = "INSERT INTO nilai (id_transaksi, id_mapel, nilai_angka) VALUES (?, ?, ?)";
                        $stmt = mysqli_prepare($koneksi, $query_nilai);
                        foreach ($mapel_indices as $index => $id_mapel) {
                            $nilai = (float)str_replace(',', '.', $data[$index]);
                            mysqli_stmt_bind_param($stmt, "iid", $id_transaksi, $id_mapel, $nilai);
                            mysqli_stmt_execute($stmt);
                        }
                        mysqli_stmt_close($stmt);

                        $baris_berhasil++;
                    }
                    
                    mysqli_commit($koneksi);
                    
                    $_SESSION['import_status'] = 'sukses';
                    $_SESSION['import_msg'] = "Berhasil mengimpor $baris_berhasil data siswa.";
                    
                } catch (Exception $e) {
                    mysqli_rollback($koneksi);
                    $_SESSION['import_status'] = 'gagal';
                    $_SESSION['import_msg'] = "Error saat proses DB: " . $e->getMessage();
                }
            } else {
                $_SESSION['import_status'] = 'gagal';
                $_SESSION['import_msg'] = "File Excel kosong.";
            }
        } else {
            $_SESSION['import_status'] = 'gagal';
            $_SESSION['import_msg'] = "Tidak dapat membuka file Excel. Error: " . SimpleXLSX::parseError();
        }
    } else {
        $_SESSION['import_status'] = 'gagal';
        $_SESSION['import_msg'] = "Terjadi kesalahan saat mengunggah file.";
    }

    header("Location: import_nilai.php");
    exit;
}
?>
