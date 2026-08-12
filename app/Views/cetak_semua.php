<?php
require_once 'koneksi.php';
require_once 'cek_sesi.php';
restrict_roles(RBAC_VIEW_REPORTS);

$is_wali = ($_SESSION['peran'] === 'Wali Kelas');
$id_pengguna = (int)$_SESSION['id_pengguna'];
$semester = isset($_GET['smt']) ? (int)$_GET['smt'] : 1;

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

// ==========================================
// 2. AMBIL IDENTITAS MADRASAH
// ==========================================
$query_identitas = db_query("SELECT * FROM identitas_madrasah WHERE id = 1");
$identitas = $query_identitas ? mysqli_fetch_assoc($query_identitas) : [];

// Fungsi Helper (Rapor)
function konversiNilaiKeHuruf($nilai) {
    $satuan = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan"];
    $belasan = ["Sepuluh", "Sebelas", "Dua Belas", "Tiga Belas", "Empat Belas", "Lima Belas", "Enam Belas", "Tujuh Belas", "Delapan Belas", "Sembilan Belas"];
    $puluhan = ["", "", "Dua Puluh", "Tiga Puluh", "Empat Puluh", "Lima Puluh", "Enam Puluh", "Tujuh Puluh", "Delapan Puluh", "Sembilan Puluh"];
    $ratusan = ["", "Seratus", "Dua Ratus", "Tiga Ratus", "Empat Ratus", "Lima Ratus", "Enam Ratus", "Tujuh Ratus", "Delapan Ratus", "Sembilan Ratus"];

    if ($nilai < 10) return $satuan[$nilai];
    elseif ($nilai < 20) return $belasan[$nilai - 10];
    elseif ($nilai < 100) {
        $puluh = floor($nilai / 10);
        $sisa = $nilai % 10;
        return $puluhan[$puluh] . ($sisa ? " " . $satuan[$sisa] : "");
    } else {
        $ratus = floor($nilai / 100);
        $sisa = $nilai % 100;
        return $ratusan[$ratus] . ($sisa ? " " . konversiNilaiKeHuruf($sisa) : "");
    }
}

function angkaKeArab($angka) {
    $angkaArab = ["٠", "١", "٢", "٣", "٤", "٥", "٦", "٧", "٨", "٩"];
    return implode('', array_map(function($num) use ($angkaArab) { return $angkaArab[$num]; }, str_split((string)$angka)));
}

function angkaKeHurufArab($angka) {
    $satuanArab = ["", "واحد", "اثنان", "ثلاثة", "أربعة", "خمسة", "ستة", "سبعة", "ثمانية", "تسعة"];
    $belasanArab = ["عشرة", "أحد عشر", "اثنا عشر", "ثلاثة عشر", "أربعة عشر", "خمسة عشر", "ستة عشر", "سبعة عشر", "ثمانية عشر", "تسعة عشر"];
    $puluhanArab = ["", "", "عشرون", "ثلاثون", "أربعون", "خمسون", "ستون", "سبعون", "ثمانون", "تسعون"];
    $ratusanArab = ["", "مائة", "مائتان", "ثلاثمائة", "أربعمائة", "خمسمائة", "ستمائة", "سبعمائة", "ثمانمائة", "تسعمائة"];

    if ($angka < 10) return $satuanArab[$angka];
    elseif ($angka < 20) return $belasanArab[$angka - 10];
    elseif ($angka < 100) {
        $puluh = floor($angka / 10);
        $sisa = $angka % 10;
        return $puluhanArab[$puluh] . ($sisa ? " و" . $satuanArab[$sisa] : "");
    } else {
        $ratus = floor($angka / 100);
        $sisa = $angka % 100;
        return $ratusanArab[$ratus] . ($sisa ? " و" . angkaKeHurufArab($sisa) : "");
    }
}

function getPredikat($angka) {
    if ($angka >= 90) return 'A';
    if ($angka >= 80) return 'B';
    if ($angka >= 70) return 'C';
    return 'D';
}

function getDeskripsiKepribadian($nilai) {
    if ($nilai == 'A') return 'Sangat Baik';
    if ($nilai == 'B') return 'Baik';
    if ($nilai == 'C') return 'Cukup';
    if ($nilai == 'D') return 'Kurang';
    return '-';
}

// Format tanggal Indonesia, mis. "20 Juni 2026"
function tanggalIndonesia($tgl, $tempat = '') {
    if (empty($tgl) || $tgl === '0000-00-00') { $tgl = date('Y-m-d'); }
    $ts = strtotime($tgl);
    if ($ts === false) { $ts = time(); }
    $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $hasil = date('j', $ts) . ' ' . $bulan[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts);
    // Kolom tempat_rapor bisa NULL, jadi cast dulu agar tidak deprecated di PHP 8.2
    $tempat = trim((string)$tempat);
    // Tempat berasal dari input admin, escape di sini karena hasil fungsi dicetak mentah
    return $tempat !== '' ? htmlspecialchars($tempat) . ', ' . $hasil : $hasil;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Semua Dokumen</title>
    <style>
        /* Global & Print Settings */
        @page {
            size: A4;
            margin: 1cm;
        }
        body {
            font-family: 'Times New Roman', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #525659; /* Like PDF viewer */
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
            page-break-after: always; /* Pemisah halaman untuk Print */
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

        /* ---------------------------------------------------
           STYLE HALAMAN 1 : SAMPUL
           --------------------------------------------------- */
        /* Jangan pakai flexbox: dompdf mendegradasi display:flex jadi block,
           jadi justify-content/align-items diabaikan. Centering pakai text-align. */
        .sampul-container {
            text-align: center;
            display: block;
            padding-top: 50px;
        }
        .sampul-container h1, .sampul-container h2, .sampul-container h3 {
            margin: 10px 0;
        }
        .sampul-logo-placeholder {
            width: 150px;
            height: 150px;
            margin: 40px auto;
            border: 2px dashed #ccc;
            display: block;
            text-align: center;
            line-height: 150px;
            color: #999;
            font-style: italic;
        }
        .sampul-identitas-madrasah {
            margin-bottom: 50px;
        }
        .sampul-identitas-madrasah h1 { font-size: 28px; text-transform: uppercase; }
        .sampul-identitas-madrasah h2 { font-size: 22px; text-transform: uppercase; }
        .sampul-identitas-madrasah p { font-size: 16px; margin: 5px 0; }
        
        .sampul-identitas-siswa {
            margin-top: 50px;
            border: 2px solid #000;
            padding: 30px;
            display: inline-block;
            text-align: left;
            min-width: 400px;
        }
        .sampul-identitas-siswa td {
            text-align: center;
        }

        /* ---------------------------------------------------
           STYLE HALAMAN 2 : BIODATA
           --------------------------------------------------- */
        .biodata-container h2 {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
            text-transform: uppercase;
        }
        .table-biodata {
            width: 100%;
            border-collapse: collapse;
        }
        .table-biodata td {
            padding: 8px 5px;
            vertical-align: top;
            font-size: 16px;
        }
        .biodata-container .label { width: 30px; }
        .biodata-container .label-text { width: 250px; }
        .biodata-container .colon { width: 20px; text-align: center; }
        
        .biodata-photo-box {
            width: 113px; /* 3cm */
            height: 151px; /* 4cm */
            border: 1px solid #000;
            display: block;
            margin-top: 40px;
            font-size: 12px;
            color: #666;
            text-align: center;
            float: left;
        }
        .biodata-photo-box table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }
        .biodata-photo-box td {
            text-align: center;
            vertical-align: middle;
            border: none;
        }
        .biodata-signature-box {
            float: right;
            margin-top: 40px;
            width: 250px;
            text-align: left;
            font-size: 16px;
        }
        .biodata-signature-box .date { margin-bottom: 70px; }
        .clearfix::after { content: ""; clear: both; display: table; }


        /* ---------------------------------------------------
           STYLE HALAMAN 3 : RAPOR
           --------------------------------------------------- */
        .rapor-container {
            font-size: 13px;
        }
        /* Style rapor — disamakan persis dengan view cetak rapor standalone
           (preview_rapot.php) agar hasil cetak semua identik */
        .rapor-container h1 {
            text-align: center;
            margin-bottom: 10px;
            margin-top: 6px;
            font-size: 20px;
            text-decoration: underline;
            font-weight: bold;
        }
        .rapor-container .header-info {
            border: 1px solid black;
            padding: 6px 8px;
            margin-bottom: 8px;
        }
        .rapor-container .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .rapor-container .info-table td {
            padding: 2px;
            vertical-align: middle;
            text-align: left;
            border: none;
        }
        .rapor-container .info-label {
            font-weight: bold;
            white-space: nowrap;
            width: 120px;
        }
        .rapor-container table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .rapor-container th, .rapor-container td {
            border: 1px solid black;
            padding: 3px;
            text-align: center;
        }
        .rapor-container th { background-color: #f2f2f2; }
        .rapor-section-header { font-weight: bold; background-color: #f2f2f2; }
        
        .arabic {
            font-family: "Traditional Arabic", Arial, sans-serif;
            direction: rtl;
            font-size: 16px;
        }
        .rapor-catatan {
            margin-top: 15px;
            font-style: italic;
        }

        /* ---------------------------------------------------
           PENGATURAN SAAT DIPRINT (Menghilangkan efek bayangan kertas)
           --------------------------------------------------- */
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none; }
            .page { 
                margin: 0; 
                box-shadow: none; 
                padding: 0; 
                /* Padding diserahkan ke setting margin browser atau @page */
            }
            .sampul-container {
                padding-top: 100px; /* Jarak atas khusus sampul */
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak Semua Dokumen</button>
        <p style="margin-top:5px; font-size:12px; font-weight:normal;">Tekan tombol di atas untuk mencetak Sampul, Biodata, dan Rapor sekaligus.</p>
    </div>

    <?php 
        $q_ta = db_query("SELECT tahun_ajaran FROM pengaturan LIMIT 1");
        $ta_aktif = $q_ta ? mysqli_fetch_assoc($q_ta)['tahun_ajaran'] : '';
        
        foreach ($siswa_ids as $id_siswa): 
        // 1. AMBIL DATA SISWA, KELAS, TRANSAKSI
$query_siswa = db_query("SELECT s.*, 
		                        COALESCE(
		                            CONCAT(k.nama_kelas, COALESCE(k.nama_rombel, ''), ' ', tk.nama_tingkat),
		                            CONCAT(k2.nama_kelas, COALESCE(k2.nama_rombel, ''), ' ', tk2.nama_tingkat)
		                        ) as nama_kelas, 
		                        COALESCE(r.id_kelas, s.id_kelas) as id_kelas_cetak,
		                        COALESCE(w.nama, w2.nama) as nama_wali_kelas,
		                        COALESCE(t.tahun_ajaran, ?) as tahun_ajaran, t.id_transaksi, r.status_kenaikan as status_kenaikan_riwayat 
		                        FROM siswa s 
		                        LEFT JOIN transaksi_raport t ON s.id_siswa = t.id_siswa AND t.semester = ?
		                        LEFT JOIN riwayat_kelas r ON s.id_siswa = r.id_siswa AND r.tahun_ajaran = COALESCE(t.tahun_ajaran, ?)
		                        LEFT JOIN kelas k ON r.id_kelas = k.id_kelas 
		                        LEFT JOIN tingkat_kelas tk ON k.id_tingkat = tk.id_tingkat
		                        LEFT JOIN pengguna w ON k.id_wali_kelas = w.id_pengguna
		                        LEFT JOIN kelas k2 ON s.id_kelas = k2.id_kelas 
		                        LEFT JOIN tingkat_kelas tk2 ON k2.id_tingkat = tk2.id_tingkat
		                        LEFT JOIN pengguna w2 ON k2.id_wali_kelas = w2.id_pengguna
		                        WHERE s.id_siswa = ?",
		                        [$ta_aktif, $semester, $ta_aktif, $id_siswa]);
		        $siswa = $query_siswa ? mysqli_fetch_assoc($query_siswa) : null;
		        if (!$siswa) continue;
		        $id_transaksi = $siswa['id_transaksi'] ?? null;
		        $id_kelas_cetak = $siswa['id_kelas_cetak'] ?? 0;
		        
		        // 3. AMBIL DATA NILAI (semua mapel kelas, tampil meski belum ada nilai)
		        $semua_nilai = [];
		        $total_nilai = 0;
	if ($id_kelas_cetak) {
		            $query_nilai = db_query("
			                SELECT mp.id_mapel, mp.nama_mapel, mp.nama_mapel_arab, mp.kkm, n.nilai_angka, pm.nama_kitab as nama_kitab_arab
			                FROM pengampu_mapel pm
			                JOIN mata_pelajaran mp ON pm.id_mapel = mp.id_mapel
			                LEFT JOIN nilai n ON n.id_mapel = mp.id_mapel AND n.id_transaksi = ?
			                WHERE pm.id_kelas = ? AND pm.status = 'Aktif'
			                ORDER BY mp.id_mapel ASC",
			                [$id_transaksi ?: 0, $id_kelas_cetak]);
		        
		            if ($query_nilai) {
			            while ($row = mysqli_fetch_assoc($query_nilai)) {
			                if ($row['nilai_angka'] !== null) {
			                    $total_nilai += (int)$row['nilai_angka'];
			                }
			                $semua_nilai[] = $row;
			            }
			        }
		        }
		        $absensi = ['sakit' => 0, 'izin' => 0, 'tanpa_keterangan' => 0];
	        $kepribadian = ['kelakuan' => '-', 'kerajinan' => '-', 'kerapian' => '-', 'kedisiplinan' => '-'];
	        $ekskul = ['baca_quran' => '-', 'baca_kitab' => '-', 'muhafadhoh' => '-', 'kaligrafi' => '-'];
	        $catatan = ['catatan' => ''];
	        
	if ($id_transaksi) {
		            $res_abs = db_query("SELECT * FROM absensi WHERE id_transaksi = ? LIMIT 1", [$id_transaksi]);
	            if ($res_abs) $absensi = mysqli_fetch_assoc($res_abs) ?: $absensi;
	        
	            $res_kep = db_query("SELECT * FROM kepribadian WHERE id_transaksi = ? LIMIT 1", [$id_transaksi]);
	            if ($res_kep) $kepribadian = mysqli_fetch_assoc($res_kep) ?: $kepribadian;
	        
	            $res_eks = db_query("SELECT * FROM ekstrakurikuler WHERE id_transaksi = ? LIMIT 1", [$id_transaksi]);
	            if ($res_eks) $ekskul = mysqli_fetch_assoc($res_eks) ?: $ekskul;
	        
	            $res_cat = db_query("SELECT * FROM catatan_wali_kelas WHERE id_transaksi = ? LIMIT 1", [$id_transaksi]);
	            if ($res_cat) $catatan = mysqli_fetch_assoc($res_cat) ?: $catatan;
	        }
    ?>
    <div class="page" style="page-break-after: always; background: white;">
        <!-- 1. JUDUL ATAS -->
        <div style="text-align: center; margin-top: 1cm;">
            <h2 style="margin: 0 0 5px 0; font-size: 20pt; text-transform: uppercase; letter-spacing: 1px;">LAPORAN HASIL BELAJAR SANTRI</h2>
            <h2 style="margin: 0; font-size: 16pt; text-transform: uppercase; font-weight: normal;"><?= htmlspecialchars($identitas['jenjang'] ?? 'MADRASAH DINIYAH') ?></h2>
        </div>

        <!-- 2. LOGO + INFORMASI MADRASAH (TENGAH) -->
        <div style="text-align: center; margin-top: 3cm;">
            <?php
                $logoExists = false;
                if (!empty($identitas['logo'])) {
                    $f1 = __DIR__ . '/../../public/uploads/' . $identitas['logo'];
                    $f2 = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $identitas['logo'];
                    $logoExists = file_exists($f1) || file_exists($f2);
                }
            ?>
            <div style="margin: 0 auto 25px auto; text-align: center; width: 150px; height: 150px;">
                <?php if ($logoExists): ?>
                    <img src="uploads/<?= htmlspecialchars($identitas['logo']) ?>" alt="Logo" style="max-height: 150px; max-width: 150px;">
                <?php else: ?>
                    <div style="width: 120px; height: 120px; border: 1px dashed #999; margin: 0 auto; line-height: 120px; color: #999;">Logo</div>
                <?php endif; ?>
            </div>

            <h1 style="margin: 15px 0 10px 0; font-size: 22pt; font-weight: bold; text-transform: uppercase;"><?= htmlspecialchars($identitas['nama_madrasah'] ?? 'MADRASAH DINIYAH') ?></h1>
            <p style="margin: 5px 0; font-size: 13pt; font-weight: bold; color: #333;">
                NSMD : <?= htmlspecialchars($identitas['nsmd'] ?? '-') ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; NPSN : <?= htmlspecialchars($identitas['npsn'] ?? '-') ?>
            </p>
            <p style="margin: 5px 0; font-size: 12pt; color: #555;"><?= htmlspecialchars($identitas['alamat'] ?? '-') ?></p>
        </div>

        <!-- 3. BOX IDENTITAS SISWA (BAWAH) -->
        <div style="margin-top: 3cm; text-align: center;">
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

    <!-- ============================================== -->
    <!-- HALAMAN 2: BIODATA                             -->
    <!-- ============================================== -->
    <div class="page" style="page-break-after: always;">
        <div class="biodata-container">
            <h2>KETERANGAN DIRI SANTRI</h2>
            <table class="table-biodata">
                <tr>
                    <td class="label">1.</td>
                    <td class="label-text">Nama Santri (Lengkap)</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['nama']) ?></td>
                </tr>
                <tr>
                    <td class="label">2.</td>
                    <td class="label-text">Nomor Induk / NISN</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['nomor_santri']) ?> / <?= htmlspecialchars($siswa['nisn'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">3.</td>
                    <td class="label-text">Tempat, Tanggal Lahir</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['tempat_lahir'] ?? '-') ?>, <?= !empty($siswa['tanggal_lahir']) ? date('d-m-Y', strtotime($siswa['tanggal_lahir'])) : '-' ?></td>
                </tr>
                <tr>
                    <td class="label">4.</td>
                    <td class="label-text">Jenis Kelamin</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= ($siswa['jenis_kelamin'] ?? 'L') == 'L' ? 'Laki-Laki' : 'Perempuan' ?></td>
                </tr>
                <tr>
                    <td class="label">5.</td>
                    <td class="label-text">Agama</td>
                    <td class="colon">:</td>
                    <td class="isian">Islam</td>
                </tr>
                <tr>
                    <td class="label">6.</td>
                    <td class="label-text">Anak Ke</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['anak_ke'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">7.</td>
                    <td class="label-text">Status dalam Keluarga</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['status_dalam_keluarga'] ?? 'Anak Kandung') ?></td>
                </tr>
                <tr>
                    <td class="label">8.</td>
                    <td class="label-text">Alamat Santri</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= nl2br(htmlspecialchars($siswa['alamat'] ?? '-')) ?></td>
                </tr>
                <tr>
                    <td class="label">9.</td>
                    <td class="label-text">Diterima di Madrasah ini</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="label-text">a. Di Kelas</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['diterima_di_kelas'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="label-text">b. Pada Tanggal</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['diterima_pada_tanggal'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">10.</td>
                    <td class="label-text">Nama Orang Tua</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="label-text">a. Ayah</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['nama_ayah'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="label-text">b. Ibu</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['nama_ibu'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">11.</td>
                    <td class="label-text">Pekerjaan Orang Tua</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="label-text">a. Ayah</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['pekerjaan_ayah'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="label-text">b. Ibu</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['pekerjaan_ibu'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">12.</td>
                    <td class="label-text">Alamat Orang Tua</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['alamat_orang_tua'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">13.</td>
                    <td class="label-text">No. Telepon / HP</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['no_handphone'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">14.</td>
                    <td class="label-text">Nama Wali Santri</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['nama_wali'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">15.</td>
                    <td class="label-text">Alamat Wali Santri</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['alamat_orang_tua'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">16.</td>
                    <td class="label-text">Pekerjaan Wali Santri</td>
                    <td class="colon">:</td>
                    <td class="isian"><?= htmlspecialchars($siswa['pekerjaan_wali'] ?? '-') ?></td>
                </tr>
            </table>

            <div class="clearfix">
                <div class="biodata-photo-box">
                    <table style="width:100%; height:100%; border-collapse:collapse; border:none;">
                        <tr>
                            <td style="text-align:center; vertical-align:middle; border:none; height:100%;">Pas Foto<br>3 x 4</td>
                        </tr>
                    </table>
                </div>
                <div class="biodata-signature-box">
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
    </div>

    <!-- ============================================== -->
    <!-- HALAMAN 3: RAPOR                               -->
    <!-- ============================================== -->
<?php if ($id_kelas_cetak): ?>
	    <div class="page" style="page-break-after: always;">
	        <div class="rapor-container">
<!-- Kop Surat dengan Logo -->
            <div style="margin-bottom: 15px; border-bottom: 3px solid black; padding-bottom: 10px;">
                <?php if (!empty($identitas['logo'])):
                    $logoFile = __DIR__ . '/../../public/uploads/' . $identitas['logo'];
                    if (!file_exists($logoFile)) $logoFile = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $identitas['logo'];
                    $logoExists = file_exists($logoFile);
                ?>
                <?php if ($logoExists): ?>
                <div style="float:left; width:80px; margin-right:18px;">
                    <img src="uploads/<?= htmlspecialchars($identitas['logo']) ?>" alt="Logo" style="max-width:80px; max-height:80px;">
                </div>
                <?php endif; endif; ?>
                <div style="text-align: left;">
                    <?php if (!empty($identitas['nama_yayasan'])): ?>
                    <p style="margin: 0; font-size: 16px;"><?= htmlspecialchars($identitas['nama_yayasan']) ?></p>
                    <?php endif; ?>
                    <h2 style="margin: 0; font-size: 22px; font-weight: bold;"><?= htmlspecialchars($identitas['nama_madrasah'] ?? 'MADRASAH DINIYAH') ?></h2>
                    <p style="margin: 3px 0;">NSMD: <?= htmlspecialchars($identitas['nsmd'] ?? '') ?> | NPSN: <?= htmlspecialchars($identitas['npsn'] ?? '') ?></p>
                    <p style="margin: 3px 0; font-size: 12px;"><?= htmlspecialchars($identitas['alamat'] ?? '') ?></p>
                </div>
                <div style="clear:both;"></div>
            </div>

            <h1>LAPORAN HASIL BELAJAR SEMESTER <?= ($semester == 2) ? 'GENAP' : 'GANJIL' ?></h1>

            <div class="header-info">
                <table class="info-table">
                    <tr>
                        <td class="info-label">Nama Santri</td>
                        <td>: <?= htmlspecialchars($siswa['nama']) ?></td>
                        <td class="info-label">Kelas</td>
                        <td>: <?= htmlspecialchars($siswa['nama_kelas']) ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Nomor Induk</td>
                        <td>: <?= htmlspecialchars($siswa['nomor_santri']) ?></td>
                        <td class="info-label">Tahun Ajaran</td>
                        <td>: <?= htmlspecialchars($siswa['tahun_ajaran']) ?></td>
                    </tr>
                </table>
            </div>

<table>
	                <tr>
	                    <th rowspan="2">No</th>
	                    <th rowspan="2">Mata Pelajaran</th>
	                    <th rowspan="2">Kitab</th>
	                    <th rowspan="2">KKM</th>
	                    <th colspan="3">Hasil Tes</th>
	                </tr>
		                <tr>
		                    <th>Angka</th>
		                    <th>Predikat</th>
		                    <th>Huruf</th>
		                </tr>
		                
		<?php 
	                $no = 1;
	                foreach ($semua_nilai as $n) { 
	                    $angka = $n['nilai_angka'] !== null ? (int)$n['nilai_angka'] : null;
	                ?>
	                <tr>
	                    <td><?= $no++ ?></td>
	                    <td style="text-align:left;"><?= htmlspecialchars($n['nama_mapel'] ?? '') ?></td>
	                    <td style="text-align:left;"><?= htmlspecialchars($n['nama_kitab_arab'] ?? '') ?></td>
	                    <td><?= htmlspecialchars($n['kkm'] ?? '65') ?></td>
	                    <td><?= $angka !== null ? $angka : '-' ?></td>
	                    <td style="font-weight:bold;"><?= $angka !== null ? getPredikat($angka) : '-' ?></td>
	                    <td><?= $angka !== null ? konversiNilaiKeHuruf($angka) : '-' ?></td>
	                </tr>
	                <?php } ?>

	                <tr style="font-weight:bold; background-color:#f9f9f9;">
	                    <td colspan="2">JUMLAH</td>
	                    <td colspan="2"></td>
	                    <td><?= $total_nilai ?></td>
	                    <td></td>
	                    <td><?= konversiNilaiKeHuruf($total_nilai) ?></td>
	                </tr>
	                <tr style="font-weight:bold; background-color:#f9f9f9;">
	                    <td colspan="2">RANGKING</td>
	                    <td colspan="5"></td>
	                </tr>
	            </table>

                <div style="margin-top:8px; width:100%;">
                <table style="width:32%; float:left; border-collapse:collapse; margin-right:2%;">
                    <tr><td colspan="2" style="border:1px solid black; padding:3px; font-weight:bold; background-color:#f2f2f2; text-align:center;">Kepribadian</td></tr>
                    <tr><td style="border:1px solid black; padding:3px;">Kelakuan</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($kepribadian['kelakuan']) ?></td></tr>
                    <tr><td style="border:1px solid black; padding:3px;">Kerajinan</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($kepribadian['kerajinan']) ?></td></tr>
                    <tr><td style="border:1px solid black; padding:3px;">Kerapian</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($kepribadian['kerapian']) ?></td></tr>
                    <tr><td style="border:1px solid black; padding:3px;">Kedisiplinan</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($kepribadian['kedisiplinan']) ?></td></tr>
                </table>
                <table style="width:32%; float:left; border-collapse:collapse; margin-right:2%;">
                    <tr><td colspan="2" style="border:1px solid black; padding:3px; font-weight:bold; background-color:#f2f2f2; text-align:center;">Absensi</td></tr>
                    <tr><td style="border:1px solid black; padding:3px;">Sakit</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($absensi['sakit']) ?></td></tr>
                    <tr><td style="border:1px solid black; padding:3px;">Izin</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($absensi['izin']) ?></td></tr>
                    <tr><td style="border:1px solid black; padding:3px;">Tanpa Keterangan</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($absensi['tanpa_keterangan']) ?></td></tr>
                </table>
                <table style="width:32%; float:left; border-collapse:collapse;">
                    <tr><td colspan="2" style="border:1px solid black; padding:3px; font-weight:bold; background-color:#f2f2f2; text-align:center;">Ekstrakurikuler</td></tr>
                    <tr><td style="border:1px solid black; padding:3px;">Baca Al-Qur'an</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($ekskul['baca_quran']) ?></td></tr>
                    <tr><td style="border:1px solid black; padding:3px;">Baca Kitab</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($ekskul['baca_kitab']) ?></td></tr>
                    <tr><td style="border:1px solid black; padding:3px;">Muhafadhoh</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($ekskul['muhafadhoh']) ?></td></tr>
                    <tr><td style="border:1px solid black; padding:3px;">Kaligrafi</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($ekskul['kaligrafi']) ?></td></tr>
                </table>
                <div style="clear:both;"></div>
            </div>

<table style="width:100%; border-collapse:collapse; margin-top:8px;">
                    <tr>
                        <td style="border:1px solid black; padding:5px 8px; font-weight:bold; background-color:#f2f2f2; vertical-align:middle;">Catatan Wali Kelas</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid black; padding:5px 8px; font-style:italic; height:38px; vertical-align:middle;"><?= htmlspecialchars($catatan['catatan'] ?? '') ?></td>
                    </tr>
                </table>

                        <?php if ($semester == 2): ?>
        <table style="width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 8px;">
            <tr>
                <td style="border:1px solid #000; padding:6px 8px;">
                    <strong>Keputusan:</strong><br>
                    Berdasarkan hasil pencapaian di atas, santri ditetapkan:<br>
                    <strong><?php echo (isset($siswa['status_kenaikan_riwayat']) && $siswa['status_kenaikan_riwayat'] == 'Naik') ? 'NAIK KELAS' : ((isset($siswa['status_kenaikan_riwayat']) && $siswa['status_kenaikan_riwayat'] == 'Tidak') ? 'TINGGAL KELAS' : 'BELUM DITENTUKAN'); ?></strong>
                </td>
            </tr>
        </table>
        <?php endif; ?>
<!-- Tanda Tangan -->
                <?php
                $nama_ortu = !empty($siswa['nama_wali']) ? $siswa['nama_wali'] : ($siswa['nama_ayah'] ?? '-');
                $tanggal_rapor = tanggalIndonesia($semester == 2 ? ($identitas['tanggal_rapor_genap'] ?? null) : ($identitas['tanggal_rapor_ganjil'] ?? null), $identitas['tempat_rapor'] ?? '');
                ?>
                <table style="width:100%; border-collapse:collapse; margin-top:12px; text-align:center; font-size:12px;">
                    <tr>
                        <td style="width:<?= $semester == 2 ? '33%' : '50%' ?>; text-align:center; vertical-align:top; border:none; padding:0 10px;">
                            <p style="margin-bottom:28px;">Mengetahui,<br>Orang Tua / Wali</p>
                            <p style="font-weight:bold; padding-top:8px; margin:0 auto; display:inline-block; border-bottom:1px solid black; padding-bottom:2px;">
                                <?= htmlspecialchars($nama_ortu) ?>
                            </p>
                        </td>
                        <?php if ($semester == 2): ?>
                        <td style="width:33%; text-align:center; vertical-align:top; border:none; padding:0 10px;">
                            <p style="margin-bottom:28px;">Mengetahui,<br>Kepala Madrasah</p>
                            <p style="font-weight:bold; padding-top:8px; margin:0 auto; display:inline-block; border-bottom:1px solid black; padding-bottom:2px;">
                                <?= htmlspecialchars($identitas['nama_kepala'] ?? '-') ?>
                            </p>
                        </td>
                        <?php endif; ?>
                        <td style="width:<?= $semester == 2 ? '33%' : '50%' ?>; text-align:center; vertical-align:top; border:none; padding:0 10px;">
                            <p style="margin-bottom:28px;"><?= $tanggal_rapor ?><br>Wali Kelas</p>
                            <p style="font-weight:bold; padding-top:8px; margin:0 auto; display:inline-block; border-bottom:1px solid black; padding-bottom:2px;">
                                <?= htmlspecialchars($siswa['nama_wali_kelas'] ?? '-') ?>
                            </p>
                        </td>
                    </tr>
                </table>
	        </div>
	    </div>
<?php else: ?>
	    <div class="page" style="text-align:center; padding-top:5cm;">
	        <h3 style="color:red;">Tidak ada data kelas untuk siswa ini.</h3>
	    </div>
    <?php endif; ?>

    <?php endforeach; ?>
    <script>window.print();</script>
</body>
</html>
