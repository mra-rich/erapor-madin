<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export Data Nilai</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 5px; text-align: left; }
        th { background-color: #059669; color: #ffffff; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Data Nilai Siswa</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Induk</th>
                <th>Nama Santri</th>
                <th>Kelas</th>
                <th>Semester</th>
                <?php foreach ($mapelList as $mapel): ?>
                    <th><?= htmlspecialchars($mapel['nama_mapel']) ?></th>
                <?php endforeach; ?>
                <th>Jumlah</th>
                <th>Rata-rata</th>
                <th>Sakit</th>
                <th>Izin</th>
                <th>Tanpa Keterangan</th>
                <th>Kelakuan</th>
                <th>Kerajinan</th>
                <th>Kerapian</th>
                <th>Catatan Wali Kelas</th>
            </tr>
        </thead>
        <tbody>
<?php if (count($reportData) > 0): ?>
    <?php $no = 1; foreach ($reportData as $row): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['nomor_santri']) ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
            <td><?= $row['semester'] ? 'Semester ' . $row['semester'] : '-' ?></td>
            
            <?php foreach ($mapelList as $mapel): ?>
                <?php $nilai = $row['nilai'][$mapel['id_mapel']] ?? 0; ?>
                <td><?= $nilai ?></td>
            <?php endforeach; ?>
            
            <td><?= $row['total_nilai'] ?></td>
            <td><?= $row['rata_rata'] ?></td>
            
            <td><?= htmlspecialchars($row['sakit']) ?></td>
            <td><?= htmlspecialchars($row['izin']) ?></td>
            <td><?= htmlspecialchars($row['tanpa_keterangan']) ?></td>
            <td><?= htmlspecialchars($row['kelakuan']) ?></td>
            <td><?= htmlspecialchars($row['kerajinan']) ?></td>
            <td><?= htmlspecialchars($row['kerapian']) ?></td>
            <td><?= htmlspecialchars($row['catatan']) ?></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan='100'>Tidak ada data</td></tr>
<?php endif; ?>
        </tbody>
    </table>
</body>
</html>
