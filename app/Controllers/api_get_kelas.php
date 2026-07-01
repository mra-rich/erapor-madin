<?php
require 'koneksi.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$pfx = isset($_GET['pfx']) ? $_GET['pfx'] : '';

if ($action === 'get_kelas') {
    $id_tingkat_param = $pfx ? $pfx . '_id_tingkat' : 'id_tingkat';
    $id_tingkat = isset($_GET[$id_tingkat_param]) ? (int)$_GET[$id_tingkat_param] : 0;
    
    echo '<option value="">-- Pilih Kelas --</option>';
    
    if ($id_tingkat > 0) {
        $query = mysqli_query($koneksi, "SELECT DISTINCT nama_kelas FROM kelas WHERE id_tingkat = $id_tingkat AND status = 'Aktif' ORDER BY CAST(nama_kelas AS UNSIGNED) ASC, nama_kelas ASC");
        while ($row = mysqli_fetch_assoc($query)) {
            $nama = htmlspecialchars($row['nama_kelas']);
            echo "<option value=\"$nama\">$nama</option>";
        }
    }
} elseif ($action === 'get_rombel') {
    $id_tingkat_param = $pfx ? $pfx . '_id_tingkat' : 'id_tingkat';
    $nama_kelas_param = $pfx ? $pfx . '_nama_kelas' : 'nama_kelas';
    
    $id_tingkat = isset($_GET[$id_tingkat_param]) ? (int)$_GET[$id_tingkat_param] : 0;
    $nama_kelas = isset($_GET[$nama_kelas_param]) ? mysqli_real_escape_string($koneksi, $_GET[$nama_kelas_param]) : '';
    
    echo '<option value="">-- Pilih Rombel --</option>';
    
    if ($id_tingkat > 0 && $nama_kelas !== '') {
        $query = mysqli_query($koneksi, "SELECT id_kelas, nama_rombel FROM kelas WHERE id_tingkat = $id_tingkat AND nama_kelas = '$nama_kelas' AND status = 'Aktif' ORDER BY nama_rombel ASC");
        while ($row = mysqli_fetch_assoc($query)) {
            $id = $row['id_kelas'];
            $nama = htmlspecialchars($row['nama_rombel']);
            echo "<option value=\"$id\">$nama</option>";
        }
    }
}
?>
