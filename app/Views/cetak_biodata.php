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
            margin-bottom: 30px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 8px 5px;
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
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 40px;
            font-size: 12px;
            color: #666;
            text-align: center;
            float: left;
        }
        .signature-box {
            float: right;
            margin-top: 40px;
            width: 250px;
            text-align: left;
            font-size: 16px;
        }
        .signature-box .date {
            margin-bottom: 70px;
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
                <td class="isian"><?= htmlspecialchars($siswa['tempat_lahir'] ?? '-') ?>, <?= htmlspecialchars($siswa['tanggal_lahir'] ?? '-') ?></td>
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
                <td class="isian"><?= htmlspecialchars($siswa['status_keluarga'] ?? 'Anak Kandung') ?></td>
            </tr>
            <tr>
                <td>8.</td>
                <td>Alamat Santri</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['alamat_siswa'] ?? '-') ?></td>
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
                <td class="isian"><?= htmlspecialchars($siswa['diterima_kelas'] ?? '-') ?></td>
            </tr>
            <tr>
                <td></td>
                <td>b. Pada Tanggal</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['diterima_tanggal'] ?? '-') ?></td>
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
                <td>Nama Wali Santri</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['nama_wali'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>13.</td>
                <td>Alamat Wali Santri</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['alamat_wali'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>14.</td>
                <td>Pekerjaan Wali Santri</td>
                <td>:</td>
                <td class="isian"><?= htmlspecialchars($siswa['pekerjaan_wali'] ?? '-') ?></td>
            </tr>
        </table>

        <div class="ttd-section">
            <div class="photo-box">
                Pas Foto<br>3 x 4
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
</body>
</html>
