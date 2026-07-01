<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_kelas = isset($_POST['id_kelas']) ? (int)$_POST['id_kelas'] : 0;
    $mapel = isset($_POST['mapel']) ? $_POST['mapel'] : [];

    if ($id_kelas <= 0) {
        header("Location: atur_mapel_kelas.php?status=error&message=Kelas tidak valid!");
        exit;
    }

    mysqli_begin_transaction($koneksi);

    try {
        // Hapus pengaturan mapel kelas lama
        $query_hapus = "DELETE FROM mapel_kelas WHERE id_kelas = $id_kelas";
        if (!mysqli_query($koneksi, $query_hapus)) {
            throw new Exception("Gagal menghapus pengaturan lama.");
        }

        // Insert pengaturan baru
        if (!empty($mapel)) {
            $query_insert = "INSERT INTO mapel_kelas (id_kelas, id_mapel) VALUES (?, ?)";
            $stmt = mysqli_prepare($koneksi, $query_insert);
            
            foreach ($mapel as $id_mapel) {
                $id_mapel_int = (int)$id_mapel;
                mysqli_stmt_bind_param($stmt, "ii", $id_kelas, $id_mapel_int);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Gagal menyimpan mapel.");
                }
            }
            mysqli_stmt_close($stmt);
        }

        mysqli_commit($koneksi);

        // Catat log
        require_once 'logger.php';
        catat_log($koneksi, $_SESSION['id_pengguna'], 'Atur Mapel Kelas', "Mengatur mapel untuk kelas ID: $id_kelas");

        header("Location: atur_mapel_kelas.php?kelas=$id_kelas&status=success&message=Pengaturan mapel kelas berhasil disimpan!");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: atur_mapel_kelas.php?kelas=$id_kelas&status=error&message=" . urlencode($e->getMessage()));
        exit;
    }
}
?>
