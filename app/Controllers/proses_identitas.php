<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
require_once 'logger.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi CSRF Token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("Validasi token gagal.");
    }

    $nama_madrasah = mysqli_real_escape_string($koneksi, trim($_POST['nama_madrasah']));
    $nsmd = mysqli_real_escape_string($koneksi, trim($_POST['nsmd']));
    $npsn = mysqli_real_escape_string($koneksi, trim($_POST['npsn']));
    $alamat = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));
    $kecamatan = mysqli_real_escape_string($koneksi, trim($_POST['kecamatan']));
    $kabupaten = mysqli_real_escape_string($koneksi, trim($_POST['kabupaten']));
    $provinsi = mysqli_real_escape_string($koneksi, trim($_POST['provinsi']));
    $kode_pos = mysqli_real_escape_string($koneksi, trim($_POST['kode_pos']));
    $telepon = mysqli_real_escape_string($koneksi, trim($_POST['telepon']));
    $website = mysqli_real_escape_string($koneksi, trim($_POST['website']));
    $nama_kepala = mysqli_real_escape_string($koneksi, trim($_POST['nama_kepala']));
    $nip_kepala = mysqli_real_escape_string($koneksi, trim($_POST['nip_kepala']));
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $tahun_ajaran = mysqli_real_escape_string($koneksi, trim($_POST['tahun_ajaran']));
    $semester = intval($_POST['semester']);

    $logo_sql = "";
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['logo']['tmp_name'];
        $file_name = $_FILES['logo']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png'];
        $allowed_mimes = ['image/jpeg', 'image/png'];

        // Validasi Magic Bytes menggunakan Fileinfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if (in_array($file_ext, $allowed_exts) && in_array($mime_type, $allowed_mimes)) {
            $new_file_name = 'logo_madrasah_' . time() . '.' . $file_ext;
            $upload_dir = 'uploads/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Ambil logo lama untuk dihapus
            $q_lama = mysqli_query($koneksi, "SELECT logo FROM identitas_madrasah WHERE id = 1");
            if ($row_lama = mysqli_fetch_assoc($q_lama)) {
                $old_logo = $upload_dir . $row_lama['logo'];
                if (!empty($row_lama['logo']) && file_exists($old_logo)) {
                    unlink($old_logo);
                }
            }

            if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                $logo_sql = "logo = '$new_file_name',";
            }
        } else {
            $_SESSION['flash_message'] = "Format logo tidak valid atau berkas palsu terdeteksi. Gunakan JPG atau PNG sungguhan.";
            header("Location: identitas_madrasah.php");
            exit();
        }
    }

    $query = "UPDATE identitas_madrasah SET 
                $logo_sql
                nama_madrasah = '$nama_madrasah',
                nsmd = '$nsmd',
                npsn = '$npsn',
                alamat = '$alamat',
                kecamatan = '$kecamatan',
                kabupaten = '$kabupaten',
                provinsi = '$provinsi',
                kode_pos = '$kode_pos',
                telepon = '$telepon',
                website = '$website',
                email = '$email',
                nama_kepala = '$nama_kepala',
                nip_kepala = '$nip_kepala'
              WHERE id = 1";

    if (mysqli_query($koneksi, $query)) {
        // Simpan juga tahun ajaran & semester ke tabel pengaturan
        mysqli_query($koneksi, "UPDATE pengaturan SET tahun_ajaran = '$tahun_ajaran', semester = $semester LIMIT 1");
        
        catat_log($koneksi, $_SESSION['id_pengguna'], "Mengubah Identitas Madrasah");
        $_SESSION['flash_message'] = "Identitas Madrasah & Pengaturan Tahun Ajaran berhasil diperbarui.";
    } else {
        $_SESSION['flash_message'] = "Gagal memperbarui Identitas Madrasah.";
    }

    header("Location: identitas_madrasah.php");
    exit();
}
?>
