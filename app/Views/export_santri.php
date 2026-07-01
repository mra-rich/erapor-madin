<?php
ob_start(); // Mulai output buffering untuk mencegah whitespace tidak disengaja
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_VIEW_REPORTS);

$id_pengguna = $_SESSION['id_pengguna'];
$filter_kelas = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;
$where_clause = "";

if ($filter_kelas > 0) {
    $where_clause = " AND kelas.id_kelas = $filter_kelas";
}

if ($_SESSION['peran'] === 'Wali Kelas') {
    $query = "SELECT siswa.*, CONCAT(kelas.nama_kelas, ' ', IFNULL(kelas.nama_rombel,''), ' ', tingkat_kelas.nama_tingkat) as nama_kelas 
            FROM siswa 
            LEFT JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
            LEFT JOIN tingkat_kelas ON kelas.id_tingkat = tingkat_kelas.id_tingkat
            WHERE kelas.id_wali_kelas = '$id_pengguna' AND siswa.status = 'Aktif' $where_clause
            ORDER BY siswa.id_siswa DESC";
} else {
    $query = "SELECT siswa.*, CONCAT(kelas.nama_kelas, ' ', IFNULL(kelas.nama_rombel,''), ' ', tingkat_kelas.nama_tingkat) as nama_kelas 
            FROM siswa 
            LEFT JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
            LEFT JOIN tingkat_kelas ON kelas.id_tingkat = tingkat_kelas.id_tingkat
            WHERE siswa.status = 'Aktif' $where_clause 
            ORDER BY siswa.id_siswa DESC";
}
$result = mysqli_query($koneksi, $query);

$filename = "Data_Santri_" . date('Y-m-d') . ".xls";

// Bersihkan semua output (seperti spasi kosong/enter dari file koneksi) sebelum mengirim header
ob_end_clean();

// Set header untuk download Excel (memaksa browser melakukan unduhan)
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export Data Santri</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 5px; text-align: left; }
        th { background-color: #059669; color: #ffffff; font-weight: bold; }
    </style>
</head>
<body>
    <h3>Data Santri</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Lengkap</th>
                <th>Nomor Induk / NISN</th>
                <th>Kelas</th>
                <th>Jenis Kelamin</th>
                <th>Tempat Lahir</th>
                <th>Tanggal Lahir</th>
                <th>Alamat Lengkap</th>
                <th>Tahun Masuk / Tahun Ajaran</th>
                <th>Nama Ayah</th>
                <th>Pekerjaan Ayah</th>
                <th>Nama Ibu</th>
                <th>Pekerjaan Ibu</th>
                <th>Nama Wali</th>
                <th>Pekerjaan Wali</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)) :
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['nama']); ?></td>
                <td><?= htmlspecialchars($row['nomor_santri']); ?></td>
                <td><?= htmlspecialchars($row['nama_kelas'] ?? 'Belum Ada Kelas'); ?></td>
                <td><?= htmlspecialchars($row['jenis_kelamin']); ?></td>
                <td><?= htmlspecialchars($row['tempat_lahir']); ?></td>
                <td><?= htmlspecialchars($row['tanggal_lahir']); ?></td>
                <td><?= htmlspecialchars($row['alamat']); ?></td>
                <td><?= htmlspecialchars($row['tahun_ajaran']); ?></td>
                <td><?= htmlspecialchars($row['nama_ayah']); ?></td>
                <td><?= htmlspecialchars($row['pekerjaan_ayah']); ?></td>
                <td><?= htmlspecialchars($row['nama_ibu']); ?></td>
                <td><?= htmlspecialchars($row['pekerjaan_ibu']); ?></td>
                <td><?= htmlspecialchars($row['nama_wali']); ?></td>
                <td><?= htmlspecialchars($row['pekerjaan_wali']); ?></td>
                <td><?= htmlspecialchars($row['status']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
