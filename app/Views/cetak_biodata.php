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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Biodata</title>
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

        h2 {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-top: 42px;
            margin-bottom: 18px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 5px 4px;
            vertical-align: top;
            font-size: 16px;
        }
        .label {
            width: 30px;
        }
        .label-text {
            width: 250px;
        }
        .colon {
            width: 20px;
            text-align: center;
        }
        .photo-box {
            width: 113px; /* 3cm */
            height: 151px; /* 4cm */
            border: 1px solid #000;
            display: block;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            text-align: center;
            float: left;
        }
        .photo-box table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }
        .photo-box td {
            text-align: center;
            vertical-align: middle;
            border: none;
        }
        .signature-box {
            float: right;
            margin-top: 20px;
            width: 250px;
            text-align: left;
            font-size: 16px;
        }
        .signature-box .date {
            margin-bottom: 50px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        @media print {
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none; }
            .page { 
                margin: 0; 
                box-shadow: none; 
                padding: 0; 
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak Identitas</button>
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
    <div class="page">
        <h2>KETERANGAN DIRI SANTRI</h2>
        
        <table class="table-biodata">
            <tr>
                <td>1.</td>
                <td>Nama Santri (Lengkap)</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['nama']) ?></td>
            </tr>
            <tr>
                <td>2.</td>
                <td>Nomor Induk / NISN</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['nomor_santri']) ?> / <?= htmlspecialchars($siswa['nisn'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>3.</td>
                <td>Tempat, Tanggal Lahir</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['tempat_lahir'] ?? '-') ?>, <?= !empty($siswa['tanggal_lahir']) ? date('d-m-Y', strtotime($siswa['tanggal_lahir'])) : '-' ?></td>
            </tr>
            <tr>
                <td>4.</td>
                <td>Jenis Kelamin</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['jenis_kelamin'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>5.</td>
                <td>Agama</td>
                <td>:</td>
                <td class="isian">Islam</td>
            </tr>
            <tr>
                <td>6.</td>
                <td>Anak Ke</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['anak_ke'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>7.</td>
                <td>Status dalam Keluarga</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['status_dalam_keluarga'] ?? 'Anak Kandung') ?></td>
            </tr>
            <tr>
                <td>8.</td>
                <td>Alamat Santri</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['alamat'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>9.</td>
                <td>Diterima di Madrasah ini</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>a. Di Kelas</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['diterima_di_kelas'] ?? '-') ?></td>
            </tr>
            <tr>
                <td></td>
                <td>b. Pada Tanggal</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['diterima_pada_tanggal'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>10.</td>
                <td>Nama Orang Tua</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>a. Ayah</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['nama_ayah'] ?? '-') ?></td>
            </tr>
            <tr>
                <td></td>
                <td>b. Ibu</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['nama_ibu'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>11.</td>
                <td>Pekerjaan Orang Tua</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>a. Ayah</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['pekerjaan_ayah'] ?? '-') ?></td>
            </tr>
            <tr>
                <td></td>
                <td>b. Ibu</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['pekerjaan_ibu'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>12.</td>
                <td>Alamat Orang Tua</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['alamat_orang_tua'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>13.</td>
                <td>No. Telepon / HP</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['no_handphone'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>14.</td>
                <td>Nama Wali Santri</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['nama_wali'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>15.</td>
                <td>Alamat Wali Santri</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['alamat_orang_tua'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>16.</td>
                <td>Pekerjaan Wali Santri</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['pekerjaan_wali'] ?? '-') ?></td>
            </tr>
        </table>

        <div class="ttd-section">
            <div class="photo-box">
                <table style="width:100%; height:100%; border-collapse:collapse; border:none;">
                    <tr>
                        <td style="text-align:center; vertical-align:middle; border:none; height:100%;">Pas Foto<br>3 x 4</td>
                    </tr>
                </table>
            </div>
            
            <div class="signature-box">
                <div class="date">
                    Lamongan, .......................... <?= date('Y') ?><br>
                    Kepala Madrasah,
                </div>
                <div>
                    <strong>....................................................</strong>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <script>window.print();</script>
</body>
</html>
