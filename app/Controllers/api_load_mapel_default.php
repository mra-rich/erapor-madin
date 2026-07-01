<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak valid']);
    exit;
}

$id_kelas = isset($_POST['id_kelas']) ? (int)$_POST['id_kelas'] : 0;

if ($id_kelas <= 0) {
    echo json_encode(['success' => false, 'message' => 'Kelas tidak valid']);
    exit;
}

// Daftar mata pelajaran baku (hardcoded) sesuai instruksi
$mapel_default = [
    "Ilmu Tafsir",
    "Ilmu Hadits",
    "Hadits",
    "Tauhid",
    "Akhlaq",
    "Fiqhi",
    "Ushul Fiqhi",
    "Qowaidul Fiqhi",
    "Faroidl",
    "Balaghoh",
    "Nahwu",
    "Shorof",
    "I'lal",
    "Bahasa Arab",
    "Pego",
    "Tajwid",
    "Tarekh",
    "Fasholatan",
    "Al-Qur'an",
    "Tes Lisan"
];

$inserted = 0;
$skipped = 0;

foreach ($mapel_default as $nama_mapel) {
    $nama_mapel = mysqli_real_escape_string($koneksi, $nama_mapel);

    // 1. Pastikan mapel ini ada di master data (tabel mata_pelajaran)
    $cek_master = mysqli_query($koneksi, "SELECT id_mapel FROM mata_pelajaran WHERE nama_mapel = '$nama_mapel'");
    
    if (mysqli_num_rows($cek_master) > 0) {
        $row_master = mysqli_fetch_assoc($cek_master);
        $id_mapel = $row_master['id_mapel'];
    } else {
        // Jika belum ada di master, tambahkan dulu ke master (sesuai kolom di proses_tambah_mapel.php)
        $insert_master = mysqli_query($koneksi, "INSERT INTO mata_pelajaran (nama_mapel, nama_mapel_arab, status, kkm) VALUES ('$nama_mapel', '', 'Aktif', 65)");
        if (!$insert_master) {
            echo json_encode(['success' => false, 'message' => 'Gagal insert master mapel: ' . mysqli_error($koneksi)]);
            exit;
        }
        $id_mapel = mysqli_insert_id($koneksi);
    }

    // 2. Tambahkan ke pengampu_mapel kelas terkait (jika belum ada)
    $cek_kelas = mysqli_query($koneksi, "SELECT id FROM pengampu_mapel WHERE id_kelas = $id_kelas AND id_mapel = $id_mapel");
    
    if (mysqli_num_rows($cek_kelas) > 0) {
        $skipped++;
    } else {
        // Insert dengan status Aktif sesuai permintaan
        $stmt = mysqli_prepare($koneksi, "INSERT INTO pengampu_mapel (id_kelas, id_mapel, id_guru, status) VALUES (?, ?, NULL, 'Aktif')");
        mysqli_stmt_bind_param($stmt, "ii", $id_kelas, $id_mapel);
        if (mysqli_stmt_execute($stmt)) {
            $inserted++;
        }
        mysqli_stmt_close($stmt);
    }
}

echo json_encode([
    'success' => true,
    'message' => "Berhasil memuat 20 mapel default. $inserted mapel baru ditambahkan ke kelas, $skipped sudah ada.",
    'inserted' => $inserted,
    'skipped' => $skipped
]);
?>
