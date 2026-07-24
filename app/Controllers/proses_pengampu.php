<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $id_kelas = isset($_POST['id_kelas']) ? (int)$_POST['id_kelas'] : 0;
    $pengampu = isset($_POST['pengampu']) ? $_POST['pengampu'] : []; // Array key: id_mapel, value: id_guru

    if ($id_kelas <= 0) {
        header("Location: pengampu.php?status=error&message=Kelas tidak valid!");
        exit;
    }

    mysqli_begin_transaction($koneksi);

    try {
        // Hapus pengampu lama untuk kelas ini
        $query_hapus = "DELETE FROM pengampu_mapel WHERE id_kelas = $id_kelas";
        if (!mysqli_query($koneksi, $query_hapus)) {
            throw new Exception("Gagal menghapus pengampu lama.");
        }

        // Insert pengampu baru
        if (!empty($pengampu)) {
            $query_insert = "INSERT INTO pengampu_mapel (id_kelas, id_mapel, id_guru) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query_insert);
            
            foreach ($pengampu as $id_mapel => $id_guru) {
                // Hanya insert jika guru dipilih
                if (!empty($id_guru)) {
                    $id_mapel_int = (int)$id_mapel;
                    $id_guru_int = (int)$id_guru;
                    mysqli_stmt_bind_param($stmt, "iii", $id_kelas, $id_mapel_int, $id_guru_int);
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Gagal menyimpan pengampu.");
                    }
                }
            }
            mysqli_stmt_close($stmt);
        }

        mysqli_commit($koneksi);

        // Catat log
        require_once 'logger.php';
        catat_log($koneksi, $_SESSION['id_pengguna'], 'Atur Pengampu', "Mengatur guru pengampu untuk kelas ID: $id_kelas");

        header("Location: pengampu.php?kelas=$id_kelas&status=success&message=Pengaturan guru pengampu berhasil disimpan!");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: pengampu.php?kelas=$id_kelas&status=error&message=" . urlencode($e->getMessage()));
        exit;
    }
}
?>
