<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_VIEW_REPORTS);

$is_wali = ($_SESSION['peran'] === 'Wali Kelas');
$id_pengguna = (int)$_SESSION['id_pengguna'];

$siswa_ids = [];
if (isset($_GET['kelas'])) {
    $id_kelas = (int)$_GET['kelas'];
    $sql_list = "SELECT id_siswa FROM siswa WHERE id_kelas = ? AND status = 'Aktif'";
    if ($is_wali) {
        $sql_list = "SELECT s.id_siswa FROM siswa s JOIN kelas k ON s.id_kelas = k.id_kelas WHERE s.id_kelas = ? AND k.id_wali_kelas = ? AND s.status = 'Aktif'";
    }
    $stmt_list = mysqli_prepare($koneksi, $sql_list);
    if ($is_wali) {
        mysqli_stmt_bind_param($stmt_list, "ii", $id_kelas, $id_pengguna);
    } else {
        mysqli_stmt_bind_param($stmt_list, "i", $id_kelas);
    }
    mysqli_stmt_execute($stmt_list);
    $res_list = mysqli_stmt_get_result($stmt_list);
    while ($row = mysqli_fetch_assoc($res_list)) {
        $siswa_ids[] = (int)$row['id_siswa'];
    }
} elseif (isset($_GET['id'])) {
    $siswa_ids = [(int)$_GET['id']];
} else {
    die("ID Siswa atau Kelas tidak ditemukan.");
}

if (empty($siswa_ids)) {
    die("Siswa tidak ditemukan atau Anda tidak memiliki akses.");
}

// Ambil identitas madrasah
$query_identitas = mysqli_query($koneksi, "SELECT * FROM identitas_madrasah WHERE id = 1");
$identitas = mysqli_fetch_assoc($query_identitas);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Sampul Raport</title>
    <style>
        @page {
            size: A4;
            margin: 2cm;
        }
        body {
            font-family: 'Times New Roman', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #525659;
        }
        
        .page {
            background: white;
            width: 21cm;
            min-height: 29.7cm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
            margin-bottom: 0.5cm;
            padding: 2cm;
            box-sizing: border-box;
            box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
            page-break-after: always;
            position: relative;
        }

        .no-print {
            text-align: center;
            padding: 15px;
            background-color: #333;
            color: white;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .no-print button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        h1, h2, h3, h4 {
            margin: 10px 0;
            text-align: center;
        }
        .logo-placeholder {
            width: 150px;
            height: 150px;
            margin: 40px auto;
            border: 2px dashed #ccc;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #999;
            font-style: italic;
        }
        .identitas-madrasah {
            margin-bottom: 50px;
            text-align: center;
        }
        .identitas-madrasah h1 {
            font-size: 28px;
            text-transform: uppercase;
        }
        .identitas-madrasah h2 {
            font-size: 22px;
            text-transform: uppercase;
        }
        .identitas-madrasah p {
            font-size: 16px;
            margin: 5px 0;
        }
        .identitas-siswa {
            margin-top: 50px;
            border: 2px solid #000;
            padding: 30px;
            display: inline-block;
            text-align: left;
            min-width: 400px;
        }
        .identitas-siswa table {
            width: 100%;
        }
        .identitas-siswa td {
            padding: 8px;
            font-size: 18px;
            font-weight: bold;
        }
        .identitas-siswa .label {
            width: 120px;
        }

        @media print {
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none; }
            .page { 
                margin: 0; 
                box-shadow: none; 
                padding: 0; 
                justify-content: flex-start;
                padding-top: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak Sampul</button>
        <p style="margin-top:5px; font-size:12px; font-weight:normal;">Tekan tombol di atas untuk mencetak dokumen.</p>
    </div>
    <?php 
        $q_ta = mysqli_query($koneksi, "SELECT tahun_ajaran FROM pengaturan LIMIT 1");
        $ta_aktif = mysqli_fetch_assoc($q_ta)['tahun_ajaran'];

        foreach ($siswa_ids as $id_siswa): 
        $query_siswa = mysqli_query($koneksi, "SELECT s.*, k.nama_kelas 
                                               FROM siswa s 
                                               LEFT JOIN riwayat_kelas r ON s.id_siswa = r.id_siswa AND r.tahun_ajaran = '$ta_aktif'
                                               LEFT JOIN kelas k ON r.id_kelas = k.id_kelas 
                                               WHERE s.id_siswa = $id_siswa");
        $siswa = mysqli_fetch_assoc($query_siswa);
        if (!$siswa) continue;
    ?>
    <div class="page">
        <div class="identitas-madrasah">
            <h2>LAPORAN HASIL BELAJAR SANTRI</h2>
            <h2>MADRASAH DINIYAH TAKMILIYAH</h2>
            
            <div class="logo-placeholder">
                <!-- <img src="logo.png" alt="Logo" style="max-width: 100%;"> -->
                Logo Madrasah
            </div>

            <h1><?= htmlspecialchars($identitas['nama_madrasah'] ?? 'MADRASAH DINIYAH SALAFIYAH AL FALAHIYAH') ?></h1>
            <p>NSMD : <?= htmlspecialchars($identitas['nsmd'] ?? '321 235 240 013') ?> &nbsp;&nbsp;&nbsp; NPSN : <?= htmlspecialchars($identitas['npsn'] ?? '') ?></p>
            <p><?= htmlspecialchars($identitas['alamat'] ?? 'Ngepung Rejosari Deket Lamongan') ?></p>
        </div>

        <div class="identitas-siswa">
            <h3 style="text-align: center; margin-bottom: 20px;">NAMA SANTRI</h3>
            <table>
                <tr>
                    <td style="text-align: center; font-size: 24px; text-transform: uppercase; padding-bottom: 20px;">
                        <?= htmlspecialchars($siswa['nama']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center; border-top: 2px dotted #000; padding-top: 10px;">
                        NIS / NISN: <?= htmlspecialchars($siswa['nomor_santri']) ?> / <?= htmlspecialchars($siswa['nisn'] ?? '-') ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
</body>
</html>
