<?php
require 'koneksi.php';
require 'cek_sesi.php';
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
$query_identitas = mysqli_query($koneksi, "SELECT * FROM identitas_madrasah WHERE id = 1");
$identitas = mysqli_fetch_assoc($query_identitas);

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
            margin: 2cm;
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
        .sampul-container {
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
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
            display: flex;
            justify-content: center;
            align-items: center;
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
        .biodata-table {
            width: 100%;
            border-collapse: collapse;
        }
        .biodata-table td {
            padding: 8px 5px;
            vertical-align: top;
            font-size: 16px;
        }
        .biodata-label { width: 30px; }
        .biodata-label-text { width: 250px; }
        .biodata-colon { width: 20px; text-align: center; }
        
        .biodata-photo-box {
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
        .rapor-container h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 20px;
            text-decoration: underline;
            font-weight: bold;
        }
        .rapor-header-info {
            border: 1px solid black;
            padding: 10px;
            margin-bottom: 10px;
        }
        .rapor-info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .rapor-info-table td {
            padding: 3px;
            vertical-align: middle;
            text-align: left;
            border: none;
        }
        .rapor-info-label {
            font-weight: bold;
            white-space: nowrap;
            width: 120px;
        }
        .rapor-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .rapor-table th, .rapor-table td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
        }
        .rapor-table th { background-color: #f2f2f2; }
        .rapor-section-header { font-weight: bold; background-color: #f2f2f2; }
        
        .arabic {
            font-family: "Traditional Arabic", Arial, sans-serif;
            direction: rtl;
            font-size: 16px;
        }
        .rapor-footer {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-top: 15px;
        }
        .rapor-footer table { width: 100%; border-collapse:collapse; }
        .rapor-footer th, .rapor-footer td { border: 1px solid black; padding:4px; text-align:center; }
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
        $q_ta = mysqli_query($koneksi, "SELECT tahun_ajaran FROM pengaturan LIMIT 1");
        $ta_aktif = mysqli_fetch_assoc($q_ta)['tahun_ajaran'];
        
        foreach ($siswa_ids as $id_siswa): 
        // 1. AMBIL DATA SISWA, KELAS, TRANSAKSI
$query_siswa = mysqli_query($koneksi, "SELECT s.*, 
	                        COALESCE(
	                            CONCAT(k.nama_kelas, COALESCE(k.nama_rombel, ''), ' ', tk.nama_tingkat),
	                            CONCAT(k2.nama_kelas, COALESCE(k2.nama_rombel, ''), ' ', tk2.nama_tingkat)
	                        ) as nama_kelas, 
	                        COALESCE(r.id_kelas, s.id_kelas) as id_kelas_cetak,
	                        COALESCE(w.nama, w2.nama) as nama_wali_kelas,
	                        COALESCE(t.tahun_ajaran, '$ta_aktif') as tahun_ajaran, t.id_transaksi, r.status_kenaikan as status_kenaikan_riwayat 
	                        FROM siswa s 
	                        LEFT JOIN transaksi_raport t ON s.id_siswa = t.id_siswa AND t.semester = $semester
	                        LEFT JOIN riwayat_kelas r ON s.id_siswa = r.id_siswa AND r.tahun_ajaran = COALESCE(t.tahun_ajaran, '$ta_aktif')
	                        LEFT JOIN kelas k ON r.id_kelas = k.id_kelas 
	                        LEFT JOIN tingkat_kelas tk ON k.id_tingkat = tk.id_tingkat
	                        LEFT JOIN pengguna w ON k.id_wali_kelas = w.id_pengguna
	                        LEFT JOIN kelas k2 ON s.id_kelas = k2.id_kelas 
	                        LEFT JOIN tingkat_kelas tk2 ON k2.id_tingkat = tk2.id_tingkat
	                        LEFT JOIN pengguna w2 ON k2.id_wali_kelas = w2.id_pengguna
	                        WHERE s.id_siswa = $id_siswa");
	        $siswa = mysqli_fetch_assoc($query_siswa);
	        if (!$siswa) continue;
	        $id_transaksi = $siswa['id_transaksi'] ?? null;
	        $id_kelas_cetak = $siswa['id_kelas_cetak'] ?? 0;
	        
	        // 3. AMBIL DATA NILAI (semua mapel kelas, tampil meski belum ada nilai)
	        $semua_nilai = [];
	        $total_nilai = 0;
if ($id_kelas_cetak) {
	            $query_nilai = mysqli_query($koneksi, "
		                SELECT mp.id_mapel, mp.nama_mapel, mp.nama_mapel_arab, mp.kkm, n.nilai_angka, pm.nama_kitab as nama_kitab_arab
		                FROM pengampu_mapel pm
		                JOIN mata_pelajaran mp ON pm.id_mapel = mp.id_mapel
		                LEFT JOIN nilai n ON n.id_mapel = mp.id_mapel AND n.id_transaksi = $id_transaksi
		                WHERE pm.id_kelas = $id_kelas_cetak AND pm.status = 'Aktif'
		                ORDER BY mp.id_mapel ASC
		            ");
	        
	            while ($row = mysqli_fetch_assoc($query_nilai)) {
	                if ($row['nilai_angka'] !== null) {
	                    $total_nilai += (int)$row['nilai_angka'];
	                }
	                $semua_nilai[] = $row;
	            }
	        }
	        $absensi = ['sakit' => 0, 'izin' => 0, 'tanpa_keterangan' => 0];
        $kepribadian = ['kelakuan' => '-', 'kerajinan' => '-', 'kerapian' => '-', 'kedisiplinan' => '-'];
        $ekskul = ['baca_quran' => '-', 'baca_kitab' => '-', 'muhafadhoh' => '-', 'kaligrafi' => '-'];
        $catatan = ['catatan' => ''];
        
if ($id_transaksi) {
	            $query_abs = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_transaksi = $id_transaksi LIMIT 1");
            if ($a = mysqli_fetch_assoc($query_abs)) $absensi = $a;
        
            $query_kep = mysqli_query($koneksi, "SELECT * FROM kepribadian WHERE id_transaksi = $id_transaksi LIMIT 1");
            if ($k = mysqli_fetch_assoc($query_kep)) $kepribadian = $k;
        
            $query_eks = mysqli_query($koneksi, "SELECT * FROM ekstrakurikuler WHERE id_transaksi = $id_transaksi LIMIT 1");
            if ($e = mysqli_fetch_assoc($query_eks)) $ekskul = $e;
        
            $query_cat = mysqli_query($koneksi, "SELECT * FROM catatan_wali_kelas WHERE id_transaksi = $id_transaksi LIMIT 1");
            if ($c = mysqli_fetch_assoc($query_cat)) $catatan = $c;
        }
    ?>
    <!-- ============================================== -->
    <!-- HALAMAN 1: SAMPUL                              -->
    <!-- ============================================== -->
    <div class="page" style="page-break-after: always;">
        <div class="sampul-container">
            <div class="sampul-identitas-madrasah">
                <h2>LAPORAN HASIL BELAJAR SANTRI</h2>
                <h2>MADRASAH DINIYAH TAKMILIYAH</h2>
                
                <div class="sampul-logo-placeholder">
                    Logo Madrasah
                </div>

                <h1><?= htmlspecialchars($identitas['nama_madrasah'] ?? 'MADRASAH DINIYAH') ?></h1>
                <p>NSMD : <?= htmlspecialchars($identitas['nsmd'] ?? '-') ?> &nbsp;&nbsp;&nbsp; NPSN : <?= htmlspecialchars($identitas['npsn'] ?? '-') ?></p>
                <p><?= htmlspecialchars($identitas['alamat'] ?? '-') ?></p>
            </div>

            <div class="sampul-identitas-siswa">
                <h3 style="text-align: center; margin-bottom: 20px;">NAMA SANTRI</h3>
                <table style="width: 100%;">
                    <tr>
                        <td style="font-size: 24px; font-weight: bold; text-transform: uppercase; padding-bottom: 20px;">
                            <?= htmlspecialchars($siswa['nama']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="border-top: 2px dotted #000; padding-top: 10px; font-weight: bold;">
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
            <h2>KETERANGAN TENTANG DIRI SANTRI</h2>
            <table class="biodata-table">
                <tr>
                    <td class="biodata-label">1.</td>
                    <td class="biodata-label-text">Nama Santri (Lengkap)</td>
                    <td class="biodata-colon">:</td>
                    <td><strong><?= htmlspecialchars($siswa['nama']) ?></strong></td>
                </tr>
                <tr>
                    <td class="biodata-label">2.</td>
                    <td class="biodata-label-text">Nomor Induk / NISN</td>
                    <td class="biodata-colon">:</td>
                    <td><?= htmlspecialchars($siswa['nomor_santri']) ?> / <?= htmlspecialchars($siswa['nisn'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="biodata-label">3.</td>
                    <td class="biodata-label-text">Tempat, Tanggal Lahir</td>
                    <td class="biodata-colon">:</td>
                    <td>
                        <?= htmlspecialchars($siswa['tempat_lahir'] ?? '-') ?>, 
                        <?= !empty($siswa['tanggal_lahir']) ? date('d-m-Y', strtotime($siswa['tanggal_lahir'])) : '-' ?>
                    </td>
                </tr>
                <tr>
                    <td class="biodata-label">4.</td>
                    <td class="biodata-label-text">Jenis Kelamin</td>
                    <td class="biodata-colon">:</td>
                    <td><?= ($siswa['jenis_kelamin'] ?? 'L') == 'L' ? 'Laki-Laki' : 'Perempuan' ?></td>
                </tr>
                <tr>
                    <td class="biodata-label">5.</td>
                    <td class="biodata-label-text">Alamat Peserta Didik</td>
                    <td class="biodata-colon">:</td>
                    <td><?= nl2br(htmlspecialchars($siswa['alamat'] ?? '-')) ?></td>
                </tr>
                <tr>
                    <td class="biodata-label">6.</td>
                    <td class="biodata-label-text">Nama Orang Tua / Wali</td>
                    <td class="biodata-colon">:</td>
                    <td><?= htmlspecialchars($siswa['nama_wali'] ?? '-') ?></td>
                </tr>
            </table>

            <div class="clearfix">
                <div class="biodata-photo-box">Pas Foto<br>3 x 4</div>
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
            <!-- Kop Surat Rapor -->
            <div style="text-align: center; margin-bottom: 15px; border-bottom: 3px solid black; padding-bottom: 10px;">
                <h2 style="margin: 0; font-size: 22px; font-weight: bold;"><?= htmlspecialchars($identitas['nama_madrasah'] ?? 'MADRASAH DINIYAH') ?></h2>
                <p style="margin: 3px 0;">NSMD: <?= htmlspecialchars($identitas['nsmd'] ?? '') ?> | NPSN: <?= htmlspecialchars($identitas['npsn'] ?? '') ?></p>
                <p style="margin: 3px 0; font-size: 12px;"><?= htmlspecialchars($identitas['alamat'] ?? '') ?></p>
            </div>

            <h1>LAPORAN HASIL BELAJAR</h1>

            <div class="rapor-header-info">
                <table class="rapor-info-table">
                    <tr>
                        <td class="rapor-info-label">Nama Santri</td>
                        <td>: <?= htmlspecialchars($siswa['nama']) ?></td>
                        <td class="rapor-info-label">Kelas</td>
                        <td>: <?= htmlspecialchars($siswa['nama_kelas']) ?></td>
                    </tr>
                    <tr>
                        <td class="rapor-info-label">Nomor Induk</td>
                        <td>: <?= htmlspecialchars($siswa['nomor_santri']) ?></td>
                        <td class="rapor-info-label">Tahun Pelajaran</td>
                        <td>: <?= htmlspecialchars($siswa['tahun_ajaran']) ?></td>
                    </tr>
                </table>
            </div>

<table class="rapor-table">
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
	                
	                <tr class="section-header">
	                    <td colspan="7" style="text-align:left; background-color: #d1d5db; padding: 5px;">MATA PELAJARAN</td>
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

            <?php if ($semester == 2): ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 15px;">
                <tr>
                    <td style="border:1px solid #000; padding:10px;">
                        <strong>Keputusan:</strong><br>
                        Berdasarkan hasil pencapaian di atas, santri ditetapkan:<br>
                        <strong><?php echo (isset($siswa['status_kenaikan_riwayat']) && $siswa['status_kenaikan_riwayat'] == 'Naik') ? 'NAIK KELAS' : ((isset($siswa['status_kenaikan_riwayat']) && $siswa['status_kenaikan_riwayat'] == 'Tidak') ? 'TINGGAL KELAS' : 'BELUM DITENTUKAN'); ?></strong>
                    </td>
                </tr>
            </table>
            <?php endif; ?>

            <div class="rapor-footer">
                <div>
                    <table class="rapor-table">
                        <tr><td colspan="4" style="font-weight:bold; background-color:#f2f2f2;">Kepribadian</td></tr>
                        <tr><td>1</td><td style="text-align:left;">Kelakuan</td><td><?= htmlspecialchars($kepribadian['kelakuan']) ?></td><td><?= getDeskripsiKepribadian($kepribadian['kelakuan']) ?></td></tr>
                        <tr><td>2</td><td style="text-align:left;">Kerajinan</td><td><?= htmlspecialchars($kepribadian['kerajinan']) ?></td><td><?= getDeskripsiKepribadian($kepribadian['kerajinan']) ?></td></tr>
                        <tr><td>3</td><td style="text-align:left;">Kerapian</td><td><?= htmlspecialchars($kepribadian['kerapian']) ?></td><td><?= getDeskripsiKepribadian($kepribadian['kerapian']) ?></td></tr>
                        <tr><td>4</td><td style="text-align:left;">Kedisiplinan</td><td><?= htmlspecialchars($kepribadian['kedisiplinan']) ?></td><td><?= getDeskripsiKepribadian($kepribadian['kedisiplinan']) ?></td></tr>
                    </table>
                </div>
                <div>
                    <table class="rapor-table">
                        <tr><td colspan="3" style="font-weight:bold; background-color:#f2f2f2;">Absensi</td></tr>
                        <tr><td>1</td><td style="text-align:left;">Sakit</td><td><?= htmlspecialchars($absensi['sakit']) ?></td></tr>
                        <tr><td>2</td><td style="text-align:left;">Izin</td><td><?= htmlspecialchars($absensi['izin']) ?></td></tr>
                        <tr><td>3</td><td style="text-align:left;">Tanpa Ket.</td><td><?= htmlspecialchars($absensi['tanpa_keterangan']) ?></td></tr>
                    </table>
                </div>
                <div>
                    <table class="rapor-table">
                        <tr><td colspan="4" style="font-weight:bold; background-color:#f2f2f2;">Ekstrakurikuler</td></tr>
                        <tr><td>1</td><td style="text-align:left;">Baca Al-Qur'an</td><td><?= htmlspecialchars($ekskul['baca_quran']) ?></td><td><?= getDeskripsiKepribadian($ekskul['baca_quran']) ?></td></tr>
                        <tr><td>2</td><td style="text-align:left;">Baca Kitab</td><td><?= htmlspecialchars($ekskul['baca_kitab']) ?></td><td><?= getDeskripsiKepribadian($ekskul['baca_kitab']) ?></td></tr>
                        <tr><td>3</td><td style="text-align:left;">Muhafadhoh</td><td><?= htmlspecialchars($ekskul['muhafadhoh']) ?></td><td><?= getDeskripsiKepribadian($ekskul['muhafadhoh']) ?></td></tr>
                        <tr><td>4</td><td style="text-align:left;">Kaligrafi</td><td><?= htmlspecialchars($ekskul['kaligrafi']) ?></td><td><?= getDeskripsiKepribadian($ekskul['kaligrafi']) ?></td></tr>
                    </table>
                </div>
            </div>

<table style="width:100%; border-collapse:collapse; margin-top:15px;">
                    <tr><td colspan="4" style="border:1px solid black; padding:8px; font-weight:bold; background-color:#f2f2f2;">Catatan Wali Kelas</td></tr>
                    <tr><td colspan="4" style="border:1px solid black; padding:8px; font-style:italic;"><?= htmlspecialchars($catatan['catatan'] ?? '') ?></td></tr>
                </table>

                <!-- Tanda Tangan -->
                <?php
                $nama_ortu = !empty($siswa['nama_wali']) ? $siswa['nama_wali'] : ($siswa['nama_ayah'] ?? '-');
                ?>
                <div style="display:grid; grid-template-columns: 1fr 1fr <?= $semester == 2 ? '1fr' : '' ?>; gap:10px; margin-top:25px; text-align:center; font-size:12px;">
                    <div>
                        <p style="margin-bottom:40px;">Mengetahui,<br>Orang Tua / Wali</p>
                        <p style="font-weight:bold; text-decoration:overline; padding-top:5px;">
                            <?= htmlspecialchars($nama_ortu) ?>
                        </p>
                    </div>
                    <?php if ($semester == 2): ?>
                    <div>
                        <p style="margin-bottom:40px;">Mengetahui,<br>Kepala Madrasah</p>
                        <p style="font-weight:bold; text-decoration:overline; padding-top:5px;">
                            <?= htmlspecialchars($identitas['nama_kepala'] ?? '-') ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    <div>
                        <p style="margin-bottom:40px;"><?= date('d F Y') ?><br>Wali Kelas</p>
                        <p style="font-weight:bold; text-decoration:overline; padding-top:5px;">
                            <?= htmlspecialchars($siswa['nama_wali_kelas'] ?? '-') ?>
                        </p>
                    </div>
                </div>
	        </div>
	    </div>
<?php else: ?>
	    <div class="page" style="display:flex; justify-content:center; align-items:center;">
	        <h3 style="color:red;">Tidak ada data kelas untuk siswa ini.</h3>
	    </div>
    <?php endif; ?>

    <?php endforeach; ?>
</body>
</html>
