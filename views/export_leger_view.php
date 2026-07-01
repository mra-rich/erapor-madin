<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 5px; text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .bg-gray { background-color: #059669; color: #ffffff; }
    </style>
</head>
<body>

<h2 style="text-align:center;">LEGER NILAI SANTRI</h2>
<p>
    <b>Kelas:</b> <?= htmlspecialchars($nama_kelas) ?><br>
    <b>Semester:</b> <?= htmlspecialchars($selectedSemester) ?><br>
    <b>Tahun Ajaran:</b> <?= htmlspecialchars($tahun_ajaran) ?>
</p>

<table>
    <thead>
        <tr class="bg-gray">
            <th rowspan="2">No</th>
            <th rowspan="2">NIS</th>
            <th rowspan="2">Nama Santri</th>
            <?php if (count($mapelList) > 0): ?>
            <th colspan="<?= count($mapelList) ?>">Mata Pelajaran</th>
            <?php endif; ?>
            <th colspan="3">Absensi</th>
            <th colspan="3">Kepribadian</th>
            <th rowspan="2">Total Nilai</th>
            <th rowspan="2">Rata-Rata</th>
            <th rowspan="2">Ranking</th>
        </tr>
        <tr class="bg-gray">
            <?php foreach ($mapelList as $mapel): ?>
                <th><?= htmlspecialchars($mapel['nama_mapel']) ?></th>
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
        <?php $no = 1; foreach ($siswaData as $siswa): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($siswa['nomor_santri']) ?></td>
            <td class="text-left font-bold"><?= htmlspecialchars($siswa['nama']) ?></td>
            
            <?php foreach ($mapelList as $mapel): 
                $nilai = $siswa['nilai'][$mapel['id_mapel']] ?? 0;
            ?>
                <td><?= $nilai > 0 ? $nilai : '-' ?></td>
            <?php endforeach; ?>
            
            <td><?= htmlspecialchars($siswa['sakit'] ?? 0) ?></td>
            <td><?= htmlspecialchars($siswa['izin'] ?? 0) ?></td>
            <td><?= htmlspecialchars($siswa['tanpa_keterangan'] ?? 0) ?></td>
            
            <td><?= htmlspecialchars($siswa['kelakuan'] ?? '-') ?></td>
            <td><?= htmlspecialchars($siswa['kerajinan'] ?? '-') ?></td>
            <td><?= htmlspecialchars($siswa['kerapian'] ?? '-') ?></td>
            
            <td class="font-bold"><?= htmlspecialchars($siswa['total_nilai']) ?></td>
            <td class="font-bold"><?= number_format($siswa['rata_rata'], 2) ?></td>
            <td class="font-bold"><?= htmlspecialchars($siswa['ranking']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
