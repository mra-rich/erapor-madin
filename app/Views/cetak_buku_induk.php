<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_VIEW_REPORTS);

if (!isset($_GET['id_siswa'])) {
    die("ID Siswa tidak ditemukan!");
}
$id_siswa = intval($_GET['id_siswa']);

// Ambil data siswa
$querySiswa = "SELECT s.*, c.nama_kelas, c.nama_rombel, tk.nama_tingkat 
               FROM siswa s
               LEFT JOIN kelas c ON s.id_kelas = c.id_kelas
               LEFT JOIN tingkat_kelas tk ON c.id_tingkat = tk.id_tingkat
               WHERE s.id_siswa = ?";
$stmt = mysqli_prepare($koneksi, $querySiswa);
mysqli_stmt_bind_param($stmt, "i", $id_siswa);
mysqli_stmt_execute($stmt);
$siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$siswa) {
    die("Data siswa tidak ditemukan.");
}

// Ambil Riwayat Kelas
$queryRiwayat = "SELECT r.*, k.nama_kelas, k.nama_rombel, tk.nama_tingkat, pg.nama as wali_kelas
                 FROM riwayat_kelas r
                 JOIN kelas k ON r.id_kelas = k.id_kelas
                 JOIN tingkat_kelas tk ON k.id_tingkat = tk.id_tingkat
                 LEFT JOIN pengguna pg ON k.id_wali_kelas = pg.id_pengguna
                 WHERE r.id_siswa = ?
                 ORDER BY r.tahun_ajaran ASC";
$stmtR = mysqli_prepare($koneksi, $queryRiwayat);
mysqli_stmt_bind_param($stmtR, "i", $id_siswa);
mysqli_stmt_execute($stmtR);
$resultRiwayat = mysqli_stmt_get_result($stmtR);
$riwayat_list = [];
while($rw = mysqli_fetch_assoc($resultRiwayat)) {
    $riwayat_list[] = $rw;
}

// Ambil Identitas Madrasah
$q_set = mysqli_query($koneksi, "SELECT * FROM identitas_madrasah LIMIT 1");
$pengaturan = mysqli_fetch_assoc($q_set);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Induk - <?= htmlspecialchars($siswa['nama']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page { size: A4; margin: 20mm; }
            body { font-size: 12pt; font-family: 'Times New Roman', serif; background: white; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
            .bg-gray-100 { background-color: #f3f4f6 !important; -webkit-print-color-adjust: exact; }
        }
        body { font-family: 'Times New Roman', serif; background-color: #f0f2f5; }
        .print-container { background: white; max-w-4xl; margin: auto; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-top: 20px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; text-align: left; font-size: 11pt; }
        th { text-align: center; font-weight: bold; }
    </style>
</head>
<body>
    <div class="no-print fixed top-4 right-4 flex gap-2 z-50">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Buku Induk
        </button>
        <button onclick="window.close()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow-lg">
            Tutup
        </button>
    </div>

    <div class="print-container">
        <!-- HEADER MADRASAH -->
        <div class="text-center mb-8 border-b-2 border-black pb-4">
            <h1 class="text-2xl font-bold uppercase">BUKU INDUK SANTRI</h1>
            <h2 class="text-xl font-bold"><?= isset($pengaturan['nama_madrasah']) ? htmlspecialchars($pengaturan['nama_madrasah']) : 'Madrasah'; ?></h2>
            <p class="text-sm"><?= isset($pengaturan['alamat']) ? htmlspecialchars($pengaturan['alamat']) : ''; ?></p>
        </div>

        <!-- BIODATA -->
        <h3 class="font-bold text-lg mb-2 bg-gray-100 p-2 border border-black">A. KETERANGAN PRIBADI SANTRI</h3>
        <table class="w-full mb-8" style="border: none;">
            <tr style="border: none;"><td style="border: none; width: 30%;">1. Nama Lengkap</td><td style="border: none; width: 2%;">:</td><td style="border: none; font-weight: bold;"><?= htmlspecialchars($siswa['nama']); ?></td></tr>
            <tr style="border: none;"><td style="border: none;">2. Nomor Induk Santri / NISN</td><td style="border: none;">:</td><td style="border: none;"><?= htmlspecialchars($siswa['nomor_santri']) . ' / ' . htmlspecialchars($siswa['nisn']); ?></td></tr>
            <tr style="border: none;"><td style="border: none;">3. Tempat, Tanggal Lahir</td><td style="border: none;">:</td><td style="border: none;"><?= htmlspecialchars($siswa['tempat_lahir']) . ', ' . ($siswa['tanggal_lahir'] ? date('d-m-Y', strtotime($siswa['tanggal_lahir'])) : '-'); ?></td></tr>
            <tr style="border: none;"><td style="border: none;">4. Jenis Kelamin</td><td style="border: none;">:</td><td style="border: none;"><?= $siswa['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td></tr>
            <tr style="border: none;"><td style="border: none;">5. Alamat Santri</td><td style="border: none;">:</td><td style="border: none;"><?= htmlspecialchars($siswa['alamat']); ?></td></tr>
            <tr style="border: none;"><td style="border: none;">6. Asal Sekolah</td><td style="border: none;">:</td><td style="border: none;"><?= htmlspecialchars($siswa['sekolah_asal']); ?></td></tr>
            <tr style="border: none;"><td style="border: none;">7. Diterima di Kelas / Tanggal</td><td style="border: none;">:</td><td style="border: none;"><?= htmlspecialchars($siswa['diterima_di_kelas']) . ' / ' . ($siswa['diterima_pada_tanggal'] ? date('d-m-Y', strtotime($siswa['diterima_pada_tanggal'])) : '-'); ?></td></tr>
            <tr style="border: none;"><td style="border: none;">8. Status Saat Ini</td><td style="border: none;">:</td><td style="border: none; font-weight: bold;"><?= htmlspecialchars($siswa['status']); ?></td></tr>
        </table>

        <h3 class="font-bold text-lg mb-2 bg-gray-100 p-2 border border-black">B. KETERANGAN ORANG TUA / WALI</h3>
        <table class="w-full mb-8" style="border: none;">
            <tr style="border: none;"><td style="border: none; width: 30%;">1. Nama Ayah</td><td style="border: none; width: 2%;">:</td><td style="border: none;"><?= htmlspecialchars($siswa['nama_ayah']); ?></td></tr>
            <tr style="border: none;"><td style="border: none;">2. Nama Ibu</td><td style="border: none;">:</td><td style="border: none;"><?= htmlspecialchars($siswa['nama_ibu']); ?></td></tr>
            <tr style="border: none;"><td style="border: none;">3. Pekerjaan Ayah / Ibu</td><td style="border: none;">:</td><td style="border: none;"><?= htmlspecialchars($siswa['pekerjaan_ayah']) . ' / ' . htmlspecialchars($siswa['pekerjaan_ibu']); ?></td></tr>
            <tr style="border: none;"><td style="border: none;">4. Nama Wali</td><td style="border: none;">:</td><td style="border: none;"><?= htmlspecialchars($siswa['nama_wali']); ?></td></tr>
            <tr style="border: none;"><td style="border: none;">5. Pekerjaan Wali</td><td style="border: none;">:</td><td style="border: none;"><?= htmlspecialchars($siswa['pekerjaan_wali']); ?></td></tr>
            <tr style="border: none;"><td style="border: none;">6. Alamat Orang Tua / Wali</td><td style="border: none;">:</td><td style="border: none;"><?= htmlspecialchars($siswa['alamat_orang_tua']); ?></td></tr>
        </table>

        <!-- RIWAYAT NILAI PER TAHUN AJARAN -->
        <h3 class="font-bold text-lg mb-4 bg-gray-100 p-2 border border-black">C. RIWAYAT PENDIDIKAN & NILAI HASIL BELAJAR</h3>
        
        <?php if(empty($riwayat_list)): ?>
            <p class="italic text-gray-600">Belum ada riwayat kelas dan nilai yang tercatat.</p>
        <?php else: ?>
            <?php foreach($riwayat_list as $index => $riwayat): 
                $ta_rw = $riwayat['tahun_ajaran'];
                $id_kelas_rw = $riwayat['id_kelas'];
                
                // Ambil daftar mapel untuk kelas ini
                $qMapel = "SELECT m.id_mapel, m.nama_mapel 
                           FROM pengampu_mapel pm
                           JOIN mata_pelajaran m ON pm.id_mapel = m.id_mapel
                           WHERE pm.id_kelas = ?
                           ORDER BY m.id_mapel ASC";
                $stmtM = mysqli_prepare($koneksi, $qMapel);
                mysqli_stmt_bind_param($stmtM, "i", $id_kelas_rw);
                mysqli_stmt_execute($stmtM);
                $resM = mysqli_stmt_get_result($stmtM);
                $mapel_list = [];
                while($m = mysqli_fetch_assoc($resM)){
                    $mapel_list[] = $m;
                }

                // Ambil nilai Ganjil
                $qNilai1 = "SELECT n.id_mapel, n.nilai_angka 
                            FROM transaksi_raport t
                            JOIN nilai n ON t.id_transaksi = n.id_transaksi
                            WHERE t.id_siswa = ? AND t.tahun_ajaran = ? AND t.semester = 1";
                $stmt1 = mysqli_prepare($koneksi, $qNilai1);
                mysqli_stmt_bind_param($stmt1, "is", $id_siswa, $ta_rw);
                mysqli_stmt_execute($stmt1);
                $res1 = mysqli_stmt_get_result($stmt1);
                $nilai_ganjil = [];
                while($n = mysqli_fetch_assoc($res1)) $nilai_ganjil[$n['id_mapel']] = $n['nilai_angka'];

                // Ambil nilai Genap
                $qNilai2 = "SELECT n.id_mapel, n.nilai_angka 
                            FROM transaksi_raport t
                            JOIN nilai n ON t.id_transaksi = n.id_transaksi
                            WHERE t.id_siswa = ? AND t.tahun_ajaran = ? AND t.semester = 2";
                $stmt2 = mysqli_prepare($koneksi, $qNilai2);
                mysqli_stmt_bind_param($stmt2, "is", $id_siswa, $ta_rw);
                mysqli_stmt_execute($stmt2);
                $res2 = mysqli_stmt_get_result($stmt2);
                $nilai_genap = [];
                while($n = mysqli_fetch_assoc($res2)) $nilai_genap[$n['id_mapel']] = $n['nilai_angka'];

                $sum_gjl = 0; $count_gjl = 0;
                $sum_gnp = 0; $count_gnp = 0;
            ?>
            <div class="mb-8 <?php if($index > 0 && $index % 2 == 0) echo 'page-break'; ?>">
                <div class="flex justify-between font-bold mb-2 text-sm">
                    <div>Tahun Ajaran: <?= $ta_rw; ?></div>
                    <div>Kelas: <?= $riwayat['nama_tingkat'] . ' ' . $riwayat['nama_kelas'] . ' ' . $riwayat['nama_rombel']; ?></div>
                </div>
                <table>
                    <thead>
                        <tr class="bg-gray-100">
                            <th rowspan="2" class="w-10">No</th>
                            <th rowspan="2">Mata Pelajaran</th>
                            <th colspan="2">Nilai Semester</th>
                        </tr>
                        <tr class="bg-gray-100">
                            <th class="w-24">1 (Ganjil)</th>
                            <th class="w-24">2 (Genap)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($mapel_list)): ?>
                        <tr><td colspan="4" class="text-center italic">Tidak ada data mata pelajaran di kelas ini.</td></tr>
                        <?php endif; ?>
                        
                        <?php $no = 1; foreach($mapel_list as $mp): 
                            $v1 = isset($nilai_ganjil[$mp['id_mapel']]) ? $nilai_ganjil[$mp['id_mapel']] : '';
                            $v2 = isset($nilai_genap[$mp['id_mapel']]) ? $nilai_genap[$mp['id_mapel']] : '';
                            if($v1 !== '') { $sum_gjl += $v1; $count_gjl++; }
                            if($v2 !== '') { $sum_gnp += $v2; $count_gnp++; }
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td><?= htmlspecialchars($mp['nama_mapel']); ?></td>
                            <td class="text-center"><?= $v1; ?></td>
                            <td class="text-center"><?= $v2; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <!-- Rangkuman -->
                        <tr class="font-bold bg-gray-50">
                            <td colspan="2" class="text-right">JUMLAH NILAI</td>
                            <td class="text-center"><?= $sum_gjl; ?></td>
                            <td class="text-center"><?= $sum_gnp; ?></td>
                        </tr>
                        <tr class="font-bold bg-gray-50">
                            <td colspan="2" class="text-right">RATA-RATA</td>
                            <td class="text-center"><?= $count_gjl > 0 ? round($sum_gjl/$count_gjl, 2) : 0; ?></td>
                            <td class="text-center"><?= $count_gnp > 0 ? round($sum_gnp/$count_gnp, 2) : 0; ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="mt-2 text-sm">
                    <strong>Keputusan Akhir Tahun:</strong> 
                    <?php 
                    if($riwayat['status_kenaikan'] == 'Naik') {
                        echo "Naik Kelas";
                    } elseif ($riwayat['status_kenaikan'] == 'Tidak') {
                        echo "Tinggal Kelas";
                    } elseif ($riwayat['status_kenaikan'] == 'Lulus') {
                        echo "Lulus / Tamat Belajar";
                    } else {
                        echo "-";
                    }
                    ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
