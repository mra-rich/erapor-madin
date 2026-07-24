<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_VIEW_ALL);

$action = isset($_GET['action']) ? $_GET['action'] : '';
$pfx = isset($_GET['pfx']) ? $_GET['pfx'] : '';

if ($action === 'get_kelas') {
    $id_tingkat_param = $pfx ? $pfx . '_id_tingkat' : 'id_tingkat';
    $id_tingkat = isset($_GET[$id_tingkat_param]) ? (int)$_GET[$id_tingkat_param] : 0;
    
    echo '<option value="">-- Pilih Kelas --</option>';
    
    if ($id_tingkat > 0) {
        $stmt = mysqli_prepare($koneksi, "SELECT DISTINCT nama_kelas FROM kelas WHERE id_tingkat = ? AND status = 'Aktif' ORDER BY CAST(nama_kelas AS UNSIGNED) ASC, nama_kelas ASC");
        mysqli_stmt_bind_param($stmt, "i", $id_tingkat);
        mysqli_stmt_execute($stmt);
        $query = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($query)) {
            $nama = htmlspecialchars($row['nama_kelas'], ENT_QUOTES, 'UTF-8');
            echo "<option value=\"$nama\">$nama</option>";
        }
    }
} elseif ($action === 'get_rombel') {
    $id_tingkat_param = $pfx ? $pfx . '_id_tingkat' : 'id_tingkat';
    $nama_kelas_param = $pfx ? $pfx . '_nama_kelas' : 'nama_kelas';
    
    $id_tingkat = isset($_GET[$id_tingkat_param]) ? (int)$_GET[$id_tingkat_param] : 0;
    $nama_kelas = isset($_GET[$nama_kelas_param]) ? $_GET[$nama_kelas_param] : '';
    
    echo '<option value="">-- Pilih Rombel --</option>';
    
    if ($id_tingkat > 0 && $nama_kelas !== '') {
        $stmt = mysqli_prepare($koneksi, "SELECT id_kelas, nama_rombel FROM kelas WHERE id_tingkat = ? AND nama_kelas = ? AND status = 'Aktif' ORDER BY nama_rombel ASC");
        mysqli_stmt_bind_param($stmt, "is", $id_tingkat, $nama_kelas);
        mysqli_stmt_execute($stmt);
        $query = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($query)) {
            $id = (int)$row['id_kelas'];
            $nama = htmlspecialchars($row['nama_rombel'], ENT_QUOTES, 'UTF-8');
            echo "<option value=\"$id\">$nama</option>";
        }
    }
}
?>
