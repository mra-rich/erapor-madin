<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_kelas = isset($_POST['id_kelas']) ? (int)$_POST['id_kelas'] : 0;
    $status_mapel = isset($_POST['status_mapel']) ? $_POST['status_mapel'] : [];
    $guru_mapel = isset($_POST['guru_mapel']) ? $_POST['guru_mapel'] : [];

    if ($id_kelas <= 0) {
        header("Location: data_mata_pelajaran.php?status=error&message=Kelas tidak valid!");
        exit;
    }

    mysqli_begin_transaction($koneksi);

    try {
        // Hapus pengaturan lama untuk kelas ini
        $query_hapus = "DELETE FROM pengampu_mapel WHERE id_kelas = $id_kelas";
        if (!mysqli_query($koneksi, $query_hapus)) {
            throw new Exception("Gagal mereset pengaturan lama.");
        }

        // Insert pengaturan baru
        if (!empty($status_mapel)) {
            $query_insert = "INSERT INTO pengampu_mapel (id_kelas, id_mapel, id_guru, status) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query_insert);
            
            foreach ($status_mapel as $id_mapel => $status) {
                if ($status === 'Aktif') {
                    $id_guru = isset($guru_mapel[$id_mapel]) && $guru_mapel[$id_mapel] !== '' ? (int)$guru_mapel[$id_mapel] : NULL;
                    $id_mapel_int = (int)$id_mapel;
                    
                    mysqli_stmt_bind_param($stmt, "iiis", $id_kelas, $id_mapel_int, $id_guru, $status);
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Gagal menyimpan pengaturan mapel ID: $id_mapel");
                    }
                }
            }
            mysqli_stmt_close($stmt);
        }

        mysqli_commit($koneksi);

        // Catat log
        require_once 'logger.php';
        catat_log($koneksi, $_SESSION['id_pengguna'], 'Atur Pengaturan Mapel', "Mengatur mapel dan pengampu untuk kelas ID: $id_kelas");

        header("Location: data_mata_pelajaran.php?kelas=$id_kelas&status=success&message=Pengaturan mata pelajaran berhasil disimpan!");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: data_mata_pelajaran.php?kelas=$id_kelas&status=error&message=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: data_mata_pelajaran.php");
    exit;
}
?>
