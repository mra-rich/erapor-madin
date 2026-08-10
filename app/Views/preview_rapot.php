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

// Ambil identitas
$query_identitas = mysqli_query($koneksi, "SELECT * FROM identitas_madrasah WHERE id = 1");
$identitas = mysqli_fetch_assoc($query_identitas);

// Fungsi Helper
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
    <title>Cetak Rapor</title>
<style>
	        @page {
	            margin: 1cm;
	            size: A4;
	        }

	        body {
	            font-family: Arial, sans-serif;
	            font-size: 13px;
	            margin: 0;
	            padding: 0;
	        }

	        .page {
	            background: white;
	            padding: 0;
	            page-break-after: always;
	        }

	        @media print {
	            body { margin: 0; padding: 0; }
	        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
            margin-top: 6px;
            font-size: 20px;
            text-decoration: underline;
            font-weight: bold;
        }

        .header-info {
            border: 1px solid black;
            padding: 6px 8px;
            margin-bottom: 8px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 2px;
            vertical-align: middle;
            text-align: left;
            border: none;
        }

        .info-label {
            font-weight: bold;
            white-space: nowrap;
            width: 120px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        th, td {
            border: 1px solid black;
            padding: 3px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .section-header {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .arabic {
            font-family: "Traditional Arabic", Arial, sans-serif;
            direction: rtl;
            font-size: 16px;
        }

        .footer {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-top: 15px;
        }

@media print {
	            body { background: white; margin: 0; padding: 0; }
	            .page { margin: 0; padding: 0; }
	        }
	</style>
	</head>
	<body>

	    <div class="preview-wrapper">
    <?php 
        $q_ta = db_query("SELECT tahun_ajaran FROM pengaturan LIMIT 1");
        $ta_aktif = $q_ta ? mysqli_fetch_assoc($q_ta)['tahun_ajaran'] : '';
        
        foreach ($siswa_ids as $id_siswa): 
// Ambil data siswa
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
	        
	        // Ambil semua mapel untuk kelas ini (tampil semua meski belum ada nilai)
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
	
<?php if ($id_kelas_cetak): ?>
		    <div class="page" id="laporan-container">
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
<td class="info-value">: <?= htmlspecialchars($siswa['nama'] ?? '') ?></td>
                    <td class="info-label">Kelas</td>
                    <td class="info-value">: <?= htmlspecialchars($siswa['nama_kelas'] ?? '') ?></td>
	                </tr>
	                <tr>
	                    <td class="info-label">Nomor Induk</td>
<td class="info-value">: <?= htmlspecialchars($siswa['nomor_santri'] ?? '') ?></td>
                    <td class="info-label">Tahun Ajaran</td>
                    <td class="info-value">: <?= htmlspecialchars($siswa['tahun_ajaran'] ?? '') ?></td>
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

<!-- Keputusan dipindah ke bawah Catatan Wali Kelas -->

<div style="margin-top:8px; width:100%;">
            <table style="width:32%; float:left; border-collapse:collapse; margin-right:2%;">
                <tr><td colspan="2" style="border:1px solid black; padding:3px; font-weight:bold; background-color:#f2f2f2; text-align:center;">Kepribadian</td></tr>
                <tr><td style="border:1px solid black; padding:3px;">Kelakuan</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($kepribadian['kelakuan'] ?? '-') ?></td></tr>
                <tr><td style="border:1px solid black; padding:3px;">Kerajinan</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($kepribadian['kerajinan'] ?? '-') ?></td></tr>
                <tr><td style="border:1px solid black; padding:3px;">Kerapian</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($kepribadian['kerapian'] ?? '-') ?></td></tr>
                <tr><td style="border:1px solid black; padding:3px;">Kedisiplinan</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($kepribadian['kedisiplinan'] ?? '-') ?></td></tr>
            </table>
            <table style="width:32%; float:left; border-collapse:collapse; margin-right:2%;">
                <tr><td colspan="2" style="border:1px solid black; padding:3px; font-weight:bold; background-color:#f2f2f2; text-align:center;">Absensi</td></tr>
                <tr><td style="border:1px solid black; padding:3px;">Sakit</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($absensi['sakit'] ?? '0') ?></td></tr>
                <tr><td style="border:1px solid black; padding:3px;">Izin</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($absensi['izin'] ?? '0') ?></td></tr>
                <tr><td style="border:1px solid black; padding:3px;">Tanpa Keterangan</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($absensi['tanpa_keterangan'] ?? '0') ?></td></tr>
            </table>
            <table style="width:32%; float:left; border-collapse:collapse;">
                <tr><td colspan="2" style="border:1px solid black; padding:3px; font-weight:bold; background-color:#f2f2f2; text-align:center;">Ekstrakurikuler</td></tr>
                <tr><td style="border:1px solid black; padding:3px;">Baca Al-Qur'an</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($ekskul['baca_quran'] ?? '-') ?></td></tr>
                <tr><td style="border:1px solid black; padding:3px;">Baca Kitab</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($ekskul['baca_kitab'] ?? '-') ?></td></tr>
                <tr><td style="border:1px solid black; padding:3px;">Muhafadhoh</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($ekskul['muhafadhoh'] ?? '-') ?></td></tr>
                <tr><td style="border:1px solid black; padding:3px;">Kaligrafi</td><td style="border:1px solid black; padding:3px; text-align:center;"><?= htmlspecialchars($ekskul['kaligrafi'] ?? '-') ?></td></tr>
            </table>
            <div style="clear:both;"></div>
        </div>
	
<table style="width:100%; border-collapse:collapse; margin-top:8px;">
                    <tr><td colspan="4" style="border:1px solid black; padding:5px 8px; font-weight:bold; background-color:#f2f2f2;">Catatan Wali Kelas</td></tr>
                    <tr><td colspan="4" style="border:1px solid black; padding:5px 8px; font-style:italic;"><?= htmlspecialchars($catatan['catatan'] ?? '') ?></td></tr>
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
        <table style="width:100%; border-collapse:collapse; margin-top:12px; text-align:center; font-size:12px;">
            <tr>
                <td style="width:<?= $semester == 2 ? '33%' : '50%' ?>; text-align:center; vertical-align:top; border:none; padding:0 10px;">
                    <p style="margin-bottom:28px;">Mengetahui,<br>Orang Tua / Wali</p>
                    <p style="font-weight:bold; padding-top:8px; margin:0 auto; display:inline-block; border-bottom:1px solid black; padding-bottom:2px;">
                        <?= htmlspecialchars(!empty($siswa['nama_wali']) ? $siswa['nama_wali'] : ($siswa['nama_ayah'] ?? '-')) ?>
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
                    <p style="margin-bottom:28px;"><?= tanggalIndonesia($semester == 2 ? ($identitas['tanggal_rapor_genap'] ?? null) : ($identitas['tanggal_rapor_ganjil'] ?? null), $identitas['tempat_rapor'] ?? '') ?><br>Wali Kelas</p>
                    <p style="font-weight:bold; padding-top:8px; margin:0 auto; display:inline-block; border-bottom:1px solid black; padding-bottom:2px;">
                        <?= htmlspecialchars($siswa['nama_wali_kelas'] ?? '-') ?>
                    </p>
                </td>
            </tr>
        </table>
		    </div>
<?php else: ?>
		    <div class="page" style="text-align:center; padding-top:5cm;">
		        <h3 style="color:red;">Tidak ada data kelas untuk siswa ini.</h3>
		    </div>
	    <?php endif; ?>
	    <?php endforeach; ?>
    </div>

<script>
<script>window.print();</script>
	</body>
	</html>
