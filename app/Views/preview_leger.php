<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_VIEW_REPORTS);

$selectedKelas = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;
$selectedSemester = isset($_GET['smt']) ? (int)$_GET['smt'] : (isset($_GET['semester']) ? (int)$_GET['semester'] : (int)$_SESSION['semester']);
$tahun_ajaran = mysqli_real_escape_string($koneksi, $_SESSION['tahun_ajaran']);

if (!$selectedKelas) {
    die("Kelas tidak dipilih.");
}

$query_kelas = mysqli_query($koneksi, "SELECT nama_kelas FROM kelas WHERE id_kelas = '$selectedKelas'");
$nama_kelas = mysqli_fetch_assoc($query_kelas)['nama_kelas'] ?? 'Kelas';

$siswaData = [];
$mapelList = [];

// Ambil daftar mapel
$queryMapel = "SELECT id_mapel, nama_mapel FROM mata_pelajaran ORDER BY id_mapel ASC";
$resultMapel = mysqli_query($koneksi, $queryMapel);
if (!$resultMapel) {
    die("Query Mapel Error: " . mysqli_error($koneksi) . " | Query: " . $queryMapel);
}
while ($mapel = mysqli_fetch_assoc($resultMapel)) {
    $mapelList[] = $mapel;
}

// Ambil data siswa & nilai
$query = "SELECT 
            s.id_siswa, s.nama, s.nomor_santri,
            t.id_transaksi,
            a.izin, a.sakit, a.tanpa_keterangan,
            k.kelakuan, k.kerajinan, k.kerapian
          FROM riwayat_kelas r
          JOIN siswa s ON r.id_siswa = s.id_siswa
          LEFT JOIN transaksi_raport t ON s.id_siswa = t.id_siswa AND t.semester = '$selectedSemester' AND t.tahun_ajaran = r.tahun_ajaran
          LEFT JOIN absensi a ON t.id_transaksi = a.id_transaksi
          LEFT JOIN kepribadian k ON t.id_transaksi = k.id_transaksi
          WHERE r.id_kelas = '$selectedKelas' AND r.tahun_ajaran = '$tahun_ajaran' AND s.status = 'Aktif'
          GROUP BY s.id_siswa
          ORDER BY s.nama ASC";

$result = mysqli_query($koneksi, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $id_transaksi = $row['id_transaksi'];
    $nilaiMapel = [];
    $totalNilai = 0;
    
    if ($id_transaksi) {
        $queryNilai = "SELECT id_mapel, nilai_angka FROM nilai WHERE id_transaksi = '$id_transaksi'";
        $resNilai = mysqli_query($koneksi, $queryNilai);
        while ($n = mysqli_fetch_assoc($resNilai)) {
            $nilaiMapel[$n['id_mapel']] = $n['nilai_angka'];
            $totalNilai += $n['nilai_angka'];
        }
    }
    
    $row['nilai'] = $nilaiMapel;
    $row['total_nilai'] = $totalNilai;
    $row['rata_rata'] = count($mapelList) > 0 ? $totalNilai / count($mapelList) : 0;
    
    $siswaData[] = $row;
}

// Hitung Ranking (berdasarkan total nilai)
usort($siswaData, function($a, $b) {
    return $b['total_nilai'] <=> $a['total_nilai'];
});
$rank = 1;
foreach ($siswaData as $key => $siswa) {
    $siswaData[$key]['ranking'] = $rank++;
}
// Sortir kembali berdasarkan nama
usort($siswaData, function($a, $b) {
    return strcmp($a['nama'], $b['nama']);
});

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Leger Nilai - <?= htmlspecialchars($nama_kelas) ?></title>
    <?php
      $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
      $assetBase = rtrim($scriptDir, '/');
      if (str_contains($assetBase, '/public')) { $assetBase = dirname($assetBase); }
      $assetBase = rtrim($assetBase, '/') . '/';
    ?>
    <link href="<?= $assetBase ?>css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { background-color: #f3f4f6; }
        .no-print {
            position: sticky;
            top: 0;
            z-index: 50;
            background: #1f2937;
            padding: 15px;
            display: flex;
            justify-content: center;
            gap: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .page {
            background: white;
            margin: 20px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            max-width: 98%;
            overflow-x: auto;
        }
        table { border-collapse: collapse; width: 100%; font-size: 12px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background-color: #e5e7eb; font-weight: bold; }
        .text-left { text-align: left; }
        .title { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; }
        
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none; }
            .page { 
                margin: 0; 
                padding: 0; 
                box-shadow: none; 
                max-width: 100%; 
                border-radius: 0;
                overflow: visible !important;
                overflow-x: visible !important;
            }
            table { 
                width: 100%; 
                max-width: 100%;
                table-layout: fixed; /* Memaksa agar tabel fit di kertas */
                word-wrap: break-word;
            }
            /* Perkecil font jika kolom sangat banyak saat print */
            th, td { 
                padding: 3px; 
                font-size: 10px; 
            }
            .title {
                margin-top: 0;
                font-size: 16px;
            }
            @page { size: landscape; margin: 1cm; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="ri-printer-line mr-2"></i> Cetak ke Printer / PDF
        </button>
        <button onclick="window.location.href='export_leger?kelas=<?= $selectedKelas ?>&semester=<?= $selectedSemester ?>'" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="ri-file-excel-2-line mr-2"></i> Download Excel
        </button>
    </div>

    <div class="page">
        <div class="title">
            LEGER NILAI SANTRI<br>
            <?= htmlspecialchars($nama_kelas) ?> - SEMESTER <?= $selectedSemester ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 3%;">No</th>
                    <th rowspan="2" style="width: 6%;">NIS</th>
                    <th rowspan="2" class="text-left" style="width: 18%;">Nama Santri</th>
                    <?php if (count($mapelList) > 0): ?>
                    <th colspan="<?= count($mapelList) ?>">Mata Pelajaran</th>
                    <?php endif; ?>
                    <th colspan="3" style="width: 6%;">Absensi</th>
                    <th colspan="3" style="width: 6%;">Kepribadian</th>
                    <th rowspan="2" style="width: 5%;">Jml Nilai</th>
                    <th rowspan="2" style="width: 5%;">Rata-rata</th>
                    <th rowspan="2" style="width: 4%;">Rank</th>
                </tr>
                <tr>
                    <?php foreach ($mapelList as $mapel): ?>
                        <th><div style="writing-mode: vertical-rl; transform: rotate(180deg); height: 100px; margin: 0 auto;"><?= htmlspecialchars($mapel['nama_mapel']) ?></div></th>
                    <?php endforeach; ?>
                    <th>S</th>
                    <th>I</th>
                    <th>A</th>
                    <th>Kel</th>
                    <th>Ker</th>
                    <th>Rap</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($siswaData as $siswa): 
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($siswa['nomor_santri']) ?></td>
                    <td class="text-left font-bold"><?= htmlspecialchars($siswa['nama']) ?></td>
                    
                    <?php foreach ($mapelList as $mapel): 
                        $nilai = $siswa['nilai'][$mapel['id_mapel']] ?? 0;
                    ?>
                        <td style="<?= $nilai < 70 ? 'color: red;' : '' ?>">
                            <?= $nilai > 0 ? $nilai : '-' ?>
                        </td>
                    <?php endforeach; ?>
                    
                    <td><?= $siswa['sakit'] ?? 0 ?></td>
                    <td><?= $siswa['izin'] ?? 0 ?></td>
                    <td><?= $siswa['tanpa_keterangan'] ?? 0 ?></td>
                    
                    <td><?= $siswa['kelakuan'] ?? '-' ?></td>
                    <td><?= $siswa['kerajinan'] ?? '-' ?></td>
                    <td><?= $siswa['kerapian'] ?? '-' ?></td>
                    
                    <td style="font-weight:bold;"><?= $siswa['total_nilai'] ?></td>
                    <td style="font-weight:bold;"><?= number_format($siswa['rata_rata'], 2) ?></td>
                    <td style="font-weight:bold;"><?= $siswa['ranking'] ?></td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (count($siswaData) == 0): ?>
                <tr>
                    <td colspan="<?= 9 + count($mapelList) ?>">Tidak ada data siswa aktif.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
