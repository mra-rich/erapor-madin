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

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rapor</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
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
            transform-origin: top left;
        }

        /* Responsive Viewport Wrapper untuk Layar Kecil (Mobile/Tablet) */
        .preview-wrapper {
            width: 100%;
            overflow-x: hidden;
            padding: 15px;
            box-sizing: border-box;
            display: block;
            position: relative;
        }

        @media (max-width: 21.5cm) {
            body {
                background-color: #f1f5f9;
            }
            .preview-wrapper {
                padding: 10px;
            }
            .page {
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
                margin-left: 0;
                margin-right: 0;
            }
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 20px;
            text-decoration: underline;
            font-weight: bold;
        }

        .header-info {
            border: 1px solid black;
            padding: 10px;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px;
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
            margin-bottom: 10px;
        }

        th, td {
            border: 1px solid black;
            padding: 5px;
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

        .catatan {
            margin-top: 15px;
            font-style: italic;
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
        <button onclick="window.print()">🖨️ Cetak Rapor</button>
        <p style="margin-top:5px; font-size:12px; font-weight:normal;">Tekan tombol di atas untuk mencetak dokumen.</p>
    </div>

    <div class="preview-wrapper">
    <?php 
        $q_ta = mysqli_query($koneksi, "SELECT tahun_ajaran FROM pengaturan LIMIT 1");
        $ta_aktif = mysqli_fetch_assoc($q_ta)['tahun_ajaran'];
        
        foreach ($siswa_ids as $id_siswa): 
        // Ambil data siswa
        $query_siswa = mysqli_query($koneksi, "SELECT s.*, k.nama_kelas, COALESCE(t.tahun_ajaran, '$ta_aktif') as tahun_ajaran, t.id_transaksi, r.status_kenaikan as status_kenaikan_riwayat 
                        FROM siswa s 
                        LEFT JOIN transaksi_raport t ON s.id_siswa = t.id_siswa AND t.semester = $semester
                        LEFT JOIN riwayat_kelas r ON s.id_siswa = r.id_siswa AND r.tahun_ajaran = COALESCE(t.tahun_ajaran, '$ta_aktif')
                        LEFT JOIN kelas k ON r.id_kelas = k.id_kelas 
                        WHERE s.id_siswa = $id_siswa");
        $siswa = mysqli_fetch_assoc($query_siswa);
        if (!$siswa) continue;
        $id_transaksi = $siswa['id_transaksi'] ?? null;
        
        // Ambil data nilai
        $semua_nilai = [];
        $total_nilai = 0;
        $absensi = ['sakit' => 0, 'izin' => 0, 'tanpa_keterangan' => 0];
        $kepribadian = ['kelakuan' => '-', 'kerajinan' => '-', 'kerapian' => '-'];
        $ekskul = ['baca_quran' => '-', 'baca_kitab' => '-', 'muhafadhoh' => '-', 'kaligrafi' => '-'];
        $catatan = ['catatan' => ''];
        
        if ($id_transaksi) {
            $query_nilai = mysqli_query($koneksi, "
                SELECT mp.nama_mapel, mp.nama_mapel_arab, mp.kkm, n.nilai_angka 
                FROM nilai n 
                JOIN mata_pelajaran mp ON n.id_mapel = mp.id_mapel
                WHERE n.id_transaksi = $id_transaksi
            ");
            while ($row = mysqli_fetch_assoc($query_nilai)) {
                $total_nilai += (int)$row['nilai_angka'];
                $semua_nilai[] = $row;
            }
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
	
	    <?php if ($id_transaksi): ?>
	    <div class="page" id="laporan-container">
	        <!-- Kop Surat -->
	        <div style="text-align: center; margin-bottom: 15px; border-bottom: 3px solid black; padding-bottom: 10px;">
	            <h2 style="margin: 0; font-size: 22px; font-weight: bold;"><?= htmlspecialchars($identitas['nama_madrasah'] ?? 'MADRASAH DINIYAH') ?></h2>
	            <p style="margin: 3px 0;">NSMD: <?= htmlspecialchars($identitas['nsmd'] ?? '') ?> | NPSN: <?= htmlspecialchars($identitas['npsn'] ?? '') ?></p>
	            <p style="margin: 3px 0; font-size: 12px;"><?= htmlspecialchars($identitas['alamat'] ?? '') ?></p>
	        </div>
	
	        <h1>LAPORAN HASIL BELAJAR</h1>
	
	        <div class="header-info">
	            <table class="info-table">
	                <tr>
	                    <td class="info-label">Nama Santri</td>
	                    <td class="info-value">: <?= htmlspecialchars($siswa['nama']) ?></td>
	                    <td class="info-label">Kelas</td>
	                    <td class="info-value">: <?= htmlspecialchars($siswa['nama_kelas']) ?></td>
	                </tr>
	                <tr>
	                    <td class="info-label">Nomor Induk</td>
	                    <td class="info-value">: <?= htmlspecialchars($siswa['nomor_santri']) ?></td>
	                    <td class="info-label">Tahun Pelajaran</td>
	                    <td class="info-value">: <?= htmlspecialchars($siswa['tahun_ajaran']) ?></td>
	                </tr>
	            </table>
	        </div>
	
	        <table>
	            <tr>
	                <th rowspan="2">No</th>
	                <th rowspan="2">Mata Pelajaran</th>
	                <th rowspan="2">KKM</th>
	                <th colspan="3">Hasil Tes</th>
	                <th colspan="2" class="arabic"> نتائج التمرين الأول </th>
	                <th class="arabic" rowspan="2">الفنون</th>
	                <th class="arabic" rowspan="2">الرقم</th>
	            </tr>
	            <tr>
	                <th>Angka</th>
	                <th>Predikat</th>
	                <th>Huruf</th>
	                <th class="arabic">اللفظ</th>
	                <th class="arabic">الرقم</th>
	            </tr>
	            
	            <tr class="section-header">
	                <td colspan="10" style="text-align:left; background-color: #d1d5db; padding: 5px;">MATA PELAJARAN</td>
	            </tr>
	            <?php 
	            $no = 1;
	            foreach ($semua_nilai as $n) { 
	                $angka = (int)$n['nilai_angka'];
	            ?>
	            <tr>
	                <td><?= $no ?></td>
	                <td style="text-align:left;"><?= htmlspecialchars($n['nama_mapel']) ?></td>
	                <td><?= htmlspecialchars($n['kkm'] ?? '65') ?></td>
	                <td><?= $angka ?></td>
	                <td style="font-weight:bold;"><?= getPredikat($angka) ?></td>
	                <td><?= konversiNilaiKeHuruf($angka) ?></td>
	                <td class="arabic"><?= angkaKeHurufArab($angka) ?></td>
	                <td class="arabic"><?= angkaKeArab($angka) ?></td>
	                <td class="arabic"><?= htmlspecialchars($n['nama_mapel_arab']) ?></td>
	                <td class="arabic"><?= angkaKeArab($no++) ?></td>
	            </tr>
	            <?php } ?>
	
	            <tr style="font-weight:bold; background-color:#f9f9f9;">
	                <td colspan="2">JUMLAH</td>
	                <td></td>
	                <td><?= $total_nilai ?></td>
	                <td></td>
	                <td><?= konversiNilaiKeHuruf($total_nilai) ?></td>
	                <td class="arabic"><?= angkaKeHurufArab($total_nilai) ?></td>
	                <td class="arabic"><?= angkaKeArab($total_nilai) ?></td>
	                <td class="arabic"></td>
	                <td colspan="2" class="arabic">الجملة</td>
	            </tr>
	            <tr style="font-weight:bold; background-color:#f9f9f9;">
	                <td colspan="2">RANGKING</td>
	                <td></td><td></td><td></td><td></td><td></td><td></td>
	                <td colspan="2" class="arabic">المقام/ة</td>
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
	
	        <div class="footer">
	            <div>
	                <table>
	                    <tr><td colspan="4" style="font-weight:bold; background-color:#f2f2f2;">Kepribadian</td></tr>
	                    <tr><td>1</td><td style="text-align:left;">Kelakuan</td><td><?= htmlspecialchars($kepribadian['kelakuan']) ?></td><td><?= getDeskripsiKepribadian($kepribadian['kelakuan']) ?></td></tr>
	                    <tr><td>2</td><td style="text-align:left;">Kerajinan</td><td><?= htmlspecialchars($kepribadian['kerajinan']) ?></td><td><?= getDeskripsiKepribadian($kepribadian['kerajinan']) ?></td></tr>
	                    <tr><td>3</td><td style="text-align:left;">Kerapian</td><td><?= htmlspecialchars($kepribadian['kerapian']) ?></td><td><?= getDeskripsiKepribadian($kepribadian['kerapian']) ?></td></tr>
	                </table>
	            </div>
	            <div>
	                <table>
	                    <tr><td colspan="2" style="font-weight:bold; background-color:#f2f2f2;">Absensi</td><td colspan="2" class="arabic" style="background-color:#f2f2f2;">الغياب</td></tr>
	                    <tr><td style="text-align:left;">Sakit</td><td><?= htmlspecialchars($absensi['sakit']) ?></td><td class="arabic"><?= angkaKeArab($absensi['sakit']) ?></td><td class="arabic">مريض</td></tr>
	                    <tr><td style="text-align:left;">Izin</td><td><?= htmlspecialchars($absensi['izin']) ?></td><td class="arabic"><?= angkaKeArab($absensi['izin']) ?></td><td class="arabic">إذن</td></tr>
	                    <tr><td style="text-align:left;">Tanpa Keterangan</td><td><?= htmlspecialchars($absensi['tanpa_keterangan']) ?></td><td class="arabic"><?= angkaKeArab($absensi['tanpa_keterangan']) ?></td><td class="arabic">غائب</td></tr>
	                </table>
	            </div>
	            <div>
	                <table>
	                    <tr><td colspan="4" style="font-weight:bold; background-color:#f2f2f2;">Ekstrakurikuler</td></tr>
	                    <tr><td>1</td><td style="text-align:left;">Baca Al-Qur'an</td><td><?= htmlspecialchars($ekskul['baca_quran']) ?></td><td><?= getDeskripsiKepribadian($ekskul['baca_quran']) ?></td></tr>
	                    <tr><td>2</td><td style="text-align:left;">Baca Kitab</td><td><?= htmlspecialchars($ekskul['baca_kitab']) ?></td><td><?= getDeskripsiKepribadian($ekskul['baca_kitab']) ?></td></tr>
	                    <tr><td>3</td><td style="text-align:left;">Muhafadhoh</td><td><?= htmlspecialchars($ekskul['muhafadhoh']) ?></td><td><?= getDeskripsiKepribadian($ekskul['muhafadhoh']) ?></td></tr>
	                    <tr><td>4</td><td style="text-align:left;">Kaligrafi</td><td><?= htmlspecialchars($ekskul['kaligrafi']) ?></td><td><?= getDeskripsiKepribadian($ekskul['kaligrafi']) ?></td></tr>
	                </table>
	            </div>
	        </div>
	
	        <div class="catatan">
	            <p style="margin-bottom:2px; font-weight:bold;">Catatan Wali Kelas:</p>
	            <p style="margin-top:0;"><?= htmlspecialchars($catatan['catatan'] ?? '') ?></p>
	        </div>
	    </div>
	    <?php else: ?>
	    <div class="page" style="display:flex; justify-content:center; align-items:center; height:100vh;">
	        <h3 style="color:red;">Belum ada data rapor/nilai untuk siswa ini pada semester tersebut.</h3>
	    </div>
	    <?php endif; ?>
	    <?php endforeach; ?>
    </div>

    <script>
        function adjustPreviewScale() {
            if (window.matchMedia('(max-width: 21.5cm)').matches) {
                const wrapper = document.querySelector('.preview-wrapper');
                const pages = document.querySelectorAll('.page');
                if (!wrapper || pages.length === 0) return;

                const wrapperWidth = wrapper.clientWidth - 20; // clientWidth excludes scrollbar
                const originalWidth = 794; // Standard A4 width pixel estimation
                const scale = wrapperWidth / originalWidth;
                
                // Calculate left margin to center the scaled page
                const leftMargin = Math.max(0, (wrapper.clientWidth - (originalWidth * scale)) / 2);

                pages.forEach(page => {
                    page.style.transform = `scale(${scale})`;
                    page.style.marginLeft = `${leftMargin}px`;
                    
                    const scaledHeight = page.offsetHeight * scale;
                    const gap = page.offsetHeight - scaledHeight;
                    page.style.marginBottom = `-${gap - 15}px`;
                });
            } else {
                document.querySelectorAll('.page').forEach(page => {
                    page.style.transform = '';
                    page.style.marginLeft = '';
                    page.style.marginBottom = '';
                });
            }
        }

        window.addEventListener('resize', adjustPreviewScale);
        window.addEventListener('load', adjustPreviewScale);
        // Jalankan berkala untuk memastikan HTMX load juga ter-scale
        setTimeout(adjustPreviewScale, 500);
    </script>
</body>
</html>
