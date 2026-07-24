<?php
require 'koneksi.php';
require 'cek_sesi.php';
require 'vendor/autoload.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);

// Set header for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Data_Guru_' . date('Ymd_His') . '.xlsx"');
header('Cache-Control: max-age=0');

// Header row
$header_style = '<style bgcolor="#059669" color="#FFFFFF" border="thin"><center><b>';
$header_close = '</b></center></style>';

$data = [
    [
        $header_style . 'NO' . $header_close,
        $header_style . 'NIP / NIK' . $header_close,
        $header_style . 'NAMA LENGKAP' . $header_close,
        $header_style . 'L/P' . $header_close,
        $header_style . 'TEMPAT LAHIR' . $header_close,
        $header_style . 'TANGGAL LAHIR' . $header_close,
        $header_style . 'ALAMAT' . $header_close,
        $header_style . 'NO HP' . $header_close,
        $header_style . 'PERAN' . $header_close,
        $header_style . 'USERNAME' . $header_close,
        $header_style . 'PASSWORD' . $header_close,
        $header_style . 'STATUS' . $header_close
    ]
];

$search = trim(isset($_GET['search']) ? $_GET['search'] : '');
$like = '%' . $search . '%';
$base_where = "WHERE p.peran IN ('Guru', 'Wali Kelas', 'Kepala Madrasah', 'Admin')";
$search_condition = " AND (p.nama LIKE ? OR g.nip LIKE ? OR p.username LIKE ?)";

// Ambil data guru dan pengguna
$export_sql = "SELECT p.username, p.peran, p.status, 
                 g.nip, g.nama_lengkap, g.jenis_kelamin, g.tempat_lahir, g.tanggal_lahir, g.no_hp, g.alamat
          FROM pengguna p 
          LEFT JOIN guru g ON p.id_pengguna = g.id_pengguna 
          $base_where";
if (!empty($search)) {
    $export_sql .= $search_condition;
}
$export_sql .= " ORDER BY p.nama ASC";
$stmt = mysqli_prepare($koneksi, $export_sql);
if (!empty($search)) {
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$no = 1;
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Prepare cell data with border style
        $data[] = [
            '<style border="thin">' . $no++ . '</style>',
            '<style border="thin">' . ($row['nip'] ?? '') . '</style>',
            '<style border="thin">' . ($row['nama_lengkap'] ?? '') . '</style>',
            '<style border="thin"><center>' . ($row['jenis_kelamin'] ?? '') . '</center></style>',
            '<style border="thin">' . ($row['tempat_lahir'] ?? '') . '</style>',
            '<style border="thin">' . ($row['tanggal_lahir'] ?? '') . '</style>',
            '<style border="thin">' . ($row['alamat'] ?? '') . '</style>',
            '<style border="thin">' . ($row['no_hp'] ?? '') . '</style>',
            '<style border="thin"><center>' . ($row['peran'] ?? '') . '</center></style>',
            '<style border="thin">' . ($row['username'] ?? '') . '</style>',
            '<style border="thin">123456</style>',
            '<style border="thin"><center>' . ($row['status'] ?? '') . '</center></style>'
        ];
    }
} else {
    // If empty, add a dummy row or just empty array
    $data[] = [
        '<style border="thin">-</style>',
        '<style border="thin">-</style>',
        '<style border="thin">-</style>',
        '<style border="thin">-</style>',
        '<style border="thin">-</style>',
        '<style border="thin">-</style>',
        '<style border="thin">-</style>',
        '<style border="thin">-</style>',
        '<style border="thin">-</style>',
        '<style border="thin">-</style>',
        '<style border="thin">-</style>',
        '<style border="thin">-</style>'
    ];
}

require_once 'logger.php';
catat_log($koneksi, $_SESSION['id_pengguna'], 'Export Guru', 'Melakukan export data guru ke Excel.');

// Generate Excel
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('Data_Guru_' . date('Ymd_His') . '.xlsx');
exit;
?>
