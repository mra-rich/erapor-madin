<?php
require_once 'koneksi.php';
require_once 'cek_sesi.php';
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
$query_identitas = db_query("SELECT * FROM identitas_madrasah WHERE id = 1");
$identitas = $query_identitas ? mysqli_fetch_assoc($query_identitas) : [];

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
            display: block;
            text-align: center;
            margin: 0 auto;
            margin-bottom: 0.5cm;
            padding: 2cm;
            padding-top: 3cm;
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
            text-align: center;
            line-height: 150px;
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
        $q_ta = db_query("SELECT tahun_ajaran FROM pengaturan LIMIT 1");
        $ta_aktif = $q_ta ? mysqli_fetch_assoc($q_ta)['tahun_ajaran'] : '';

        foreach ($siswa_ids as $id_siswa): 
        $query_siswa = db_query("SELECT s.*, 
                                               COALESCE(
                                                   CONCAT(k.nama_kelas, COALESCE(k.nama_rombel, ''), ' ', tk.nama_tingkat),
                                                   CONCAT(k2.nama_kelas, COALESCE(k2.nama_rombel, ''), ' ', tk2.nama_tingkat)
                                               ) as nama_kelas 
                                               FROM siswa s 
                                               LEFT JOIN riwayat_kelas r ON s.id_siswa = r.id_siswa AND r.tahun_ajaran = ?
                                               LEFT JOIN kelas k ON r.id_kelas = k.id_kelas 
                                               LEFT JOIN tingkat_kelas tk ON k.id_tingkat = tk.id_tingkat
                                               LEFT JOIN kelas k2 ON s.id_kelas = k2.id_kelas 
                                               LEFT JOIN tingkat_kelas tk2 ON k2.id_tingkat = tk2.id_tingkat
                                               WHERE s.id_siswa = ?",
                                               [$ta_aktif, $id_siswa]);
        $siswa = $query_siswa ? mysqli_fetch_assoc($query_siswa) : null;
        if (!$siswa) continue;
    ?>
    <div class="page" style="padding: 2cm; height: 29.7cm; box-sizing: border-box; background: white; position: relative;">
        <!-- 1. JUDUL ATAS -->
        <div style="text-align: center; margin-top: 1.5cm;">
            <h2 style="margin: 0; font-size: 20pt; text-transform: uppercase; letter-spacing: 1px;">LAPORAN HASIL BELAJAR SANTRI</h2>
        </div>
        
        <!-- 2. LOGO + INFORMASI MADRASAH (TENGAH) -->
        <div style="text-align: center; margin-top: 4cm;">
            <?php 
                $logoExists = false; $logoSrc = '';
                if (!empty($identitas['logo'])) {
                    $logoSrc = 'uploads/' . htmlspecialchars($identitas['logo']);
                    $f1 = __DIR__ . '/../../public/uploads/' . $identitas['logo'];
                    $f2 = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $identitas['logo'];
                    $logoExists = file_exists($f1) || file_exists($f2);
                }
            ?>
            <div style="margin: 0 auto 25px auto; text-align: center; width: 150px; height: 150px;">
                <?php if ($logoExists): ?>
                    <img src="<?= $logoSrc ?>" alt="Logo" style="max-height: 150px; max-width: 150px;">
                <?php else: ?>
                    <div style="width: 120px; height: 120px; border: 1px dashed #999; margin: 0 auto; line-height: 120px; color: #999;">Logo</div>
                <?php endif; ?>
            </div>
            
            <h1 style="margin: 15px 0 10px 0; font-size: 22pt; font-weight: bold; text-transform: uppercase;"><?= preg_replace('/\bAl\s+(?=\S)/i', 'Al&nbsp;', htmlspecialchars($identitas['nama_madrasah'] ?? 'MADRASAH DINIYAH SALAFIYAH AL FALAHIYAH')) ?></h1>
            <p style="margin: 5px 0; font-size: 13pt; font-weight: bold; color: #333;">
                NSMD : <?= htmlspecialchars($identitas['nsmd'] ?? '321 235 240 013') ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; NPSN : <?= htmlspecialchars($identitas['npsn'] ?? '-') ?>
            </p>
            <p style="margin: 5px 0; font-size: 12pt; color: #555;"><?= htmlspecialchars($identitas['alamat'] ?? 'Ngepung Rejosari Deket Lamongan') ?></p>
        </div>
        
        <!-- 3. BOX IDENTITAS SISWA (BAWAH) -->
        <div style="margin-top: 7cm; text-align: center;">
            <div style="border: 2px solid #000; padding: 25px 30px; display: inline-block; text-align: left; width: 420px; margin: 0 auto;">
                <h3 style="text-align: center; margin: 0 0 15px 0; font-size: 12pt; color: #666; font-weight: normal; letter-spacing: 2px;">NAMA SANTRI</h3>
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        <td style="text-align: center; font-size: 20pt; font-weight: bold; text-transform: uppercase; padding-bottom: 15px; font-family: 'Times New Roman', Times, serif; border: none;">
                            <?= htmlspecialchars($siswa['nama']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center; border-top: 2px dotted #000; padding-top: 10px; font-size: 13pt; font-weight: bold; border: none;">
                            NIS / NISN: <?= htmlspecialchars($siswa['nomor_santri']) ?> / <?= htmlspecialchars($siswa['nisn'] ?? '-') ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <script>window.print();</script>
</body>
</html>
