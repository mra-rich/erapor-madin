<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_MANAGE_GRADES);
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

$id_pengguna = $_SESSION['id_pengguna'] ?? 0;
$peran = $_SESSION['peran'] ?? '';

// Ambil tahun ajaran & semester aktif
$q_pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
$data_pengaturan = mysqli_fetch_assoc($q_pengaturan);
$tahun_aktif = $data_pengaturan['tahun_ajaran'] ?? '2024/2025';
$semester_aktif = $data_pengaturan['semester'] ?? 1;

// Jika admin, ambil semua. Jika guru, ambil mapel yang diampunya saja.
if ($peran == 'Admin' || $peran == 'Kepala Madrasah') {
    $query_mapel = "SELECT pm.*, m.nama_mapel, k.nama_kelas, k.nama_rombel, t.nama_tingkat, p.nama as nama_guru,
                    (SELECT COUNT(n.id_nilai) 
                     FROM nilai n 
                     JOIN transaksi_raport tr ON n.id_transaksi = tr.id_transaksi 
                     JOIN riwayat_kelas r ON tr.id_siswa = r.id_siswa 
                     WHERE r.id_kelas = pm.id_kelas 
                       AND n.id_mapel = pm.id_mapel 
                       AND tr.tahun_ajaran = '$tahun_aktif' 
                       AND tr.semester = $semester_aktif) as jumlah_nilai
                    FROM pengampu_mapel pm 
                    JOIN mata_pelajaran m ON pm.id_mapel = m.id_mapel 
                    JOIN kelas k ON pm.id_kelas = k.id_kelas
                    LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat
                    JOIN pengguna p ON pm.id_guru = p.id_pengguna
                    WHERE pm.status = 'Aktif' AND m.status = 'Aktif'
                    ORDER BY t.nama_tingkat ASC, CAST(k.nama_kelas AS UNSIGNED) ASC, k.nama_rombel ASC, m.nama_mapel ASC";
} else {
    $query_mapel = "SELECT pm.*, m.nama_mapel, k.nama_kelas, k.nama_rombel, t.nama_tingkat, p.nama as nama_guru,
                    (SELECT COUNT(n.id_nilai) 
                     FROM nilai n 
                     JOIN transaksi_raport tr ON n.id_transaksi = tr.id_transaksi 
                     JOIN riwayat_kelas r ON tr.id_siswa = r.id_siswa 
                     WHERE r.id_kelas = pm.id_kelas 
                       AND n.id_mapel = pm.id_mapel 
                       AND tr.tahun_ajaran = '$tahun_aktif' 
                       AND tr.semester = $semester_aktif) as jumlah_nilai
                    FROM pengampu_mapel pm 
                    JOIN mata_pelajaran m ON pm.id_mapel = m.id_mapel 
                    JOIN kelas k ON pm.id_kelas = k.id_kelas
                    LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat
                    JOIN pengguna p ON pm.id_guru = p.id_pengguna
                    WHERE (pm.id_guru = '$id_pengguna' OR k.id_wali_kelas = '$id_pengguna') AND pm.status = 'Aktif' AND m.status = 'Aktif'
                    ORDER BY t.nama_tingkat ASC, CAST(k.nama_kelas AS UNSIGNED) ASC, k.nama_rombel ASC, m.nama_mapel ASC";
}

$result_mapel = mysqli_query($koneksi, $query_mapel);
$mapel_list = [];
if ($result_mapel) {
    while ($row = mysqli_fetch_assoc($result_mapel)) {
        $mapel_list[] = $row;
    }
}
?>

<div class="page-shell">
  <div class="page-inner">
    
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="page-title">Kelas &amp; Penilaian</h1>
      <p class="page-subtitle">
        Semester Aktif: <span class="font-bold text-emerald-600"><?= $semester_aktif == 1 ? 'Ganjil' : 'Genap' ?> (<?= htmlspecialchars($tahun_aktif) ?>)</span>
      </p>
    </div>

    <?php if (count($mapel_list) > 0): ?>

      <!-- ═══ DESKTOP VIEW (sm+) ═══ -->
      <div class="hidden sm:block table-scroll-wrap">
        <table class="ui-table">
          <thead>
            <tr>
              <th class="w-12 text-center">No</th>
              <th class="w-32">Kelas</th>
              <th>Mata Pelajaran / Kitab</th>
              <th>Nama Guru</th>
              <th class="w-36 text-center">Status</th>
              <th class="w-24 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $no = 1; 
            foreach ($mapel_list as $row): 
              $tingkatan_kategori = $row['nama_tingkat'] ?? '';
              $tingkatan_angka = $row['nama_kelas'];
              $rombel_display = (!isset($row['nama_rombel']) || $row['nama_rombel'] === '-') ? '' : $row['nama_rombel'] . ' ';
              $singkatan_map = ['Ibtida\'iyah' => 'MIF', 'Tsanawiyah' => 'MTsF', 'Aliyah' => 'MAF'];
              $singkatan = $singkatan_map[$tingkatan_kategori] ?? $tingkatan_kategori;
              $nama_kelas_lengkap = trim($tingkatan_angka . ' ' . $rombel_display . $singkatan);
            ?>
            <tr>
              <td class="text-center text-slate-400 text-xs"><?= $no++ ?></td>
              <td>
                <span class="badge badge-success font-bold text-xs"><?= htmlspecialchars($nama_kelas_lengkap) ?></span>
              </td>
              <td>
                <p class="font-semibold text-slate-800"><?= htmlspecialchars($row['nama_mapel']) ?></p>
                <?php if (!empty($row['nama_kitab'])): ?>
                <p class="text-xs text-slate-400">Kitab: <?= htmlspecialchars($row['nama_kitab']) ?></p>
                <?php endif; ?>
              </td>
              <td class="text-slate-600 font-medium"><?= htmlspecialchars($row['nama_guru']) ?></td>
              <td class="text-center">
                <?php if ($row['jumlah_nilai'] > 0): ?>
                  <span class="badge badge-success"><i class="ri-checkbox-circle-line"></i> Sudah Dinilai</span>
                <?php else: ?>
                  <span class="badge badge-warning"><i class="ri-time-line"></i> Belum Dinilai</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <a href="input_nilai_massal.php?id_mapel=<?= $row['id_mapel'] ?>&id_kelas=<?= $row['id_kelas'] ?>" class="btn btn-primary btn-sm py-1.5 px-3">
                  <i class="ri-pencil-line"></i> Input
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- ═══ MOBILE VIEW (below sm) ═══ -->
      <div class="sm:hidden space-y-3">
        <?php 
        $no = 1; 
        foreach ($mapel_list as $row): 
          $tingkatan_kategori = $row['nama_tingkat'] ?? '';
          $tingkatan_angka = $row['nama_kelas'];
          $rombel_display = (!isset($row['nama_rombel']) || $row['nama_rombel'] === '-') ? '' : $row['nama_rombel'] . ' ';
          $singkatan_map = ['Ibtida\'iyah' => 'MIF', 'Tsanawiyah' => 'MTsF', 'Aliyah' => 'MAF'];
          $singkatan = $singkatan_map[$tingkatan_kategori] ?? $tingkatan_kategori;
          $nama_kelas_lengkap = trim($tingkatan_angka . ' ' . $rombel_display . $singkatan);
        ?>
        <div class="ui-card p-4 space-y-3">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="font-bold text-slate-800 text-base leading-tight"><?= htmlspecialchars($row['nama_mapel']) ?></p>
              <?php if (!empty($row['nama_kitab'])): ?>
              <p class="text-xs text-slate-500 mt-1"><i class="ri-book-3-line"></i> <?= htmlspecialchars($row['nama_kitab']) ?></p>
              <?php endif; ?>
            </div>
            <span class="badge badge-success shrink-0 font-bold text-xs"><?= htmlspecialchars($nama_kelas_lengkap) ?></span>
          </div>

          <div class="flex flex-col gap-1 text-xs text-slate-500 pt-2 border-t border-slate-100">
            <p><span class="font-semibold text-slate-400">Guru:</span> <?= htmlspecialchars($row['nama_guru']) ?></p>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-50">
              <div>
                <?php if ($row['jumlah_nilai'] > 0): ?>
                  <span class="badge badge-success text-[10px]"><i class="ri-checkbox-circle-line"></i> Sudah Dinilai</span>
                <?php else: ?>
                  <span class="badge badge-warning text-[10px]"><i class="ri-time-line"></i> Belum Dinilai</span>
                <?php endif; ?>
              </div>
              <a href="input_nilai_massal.php?id_mapel=<?= $row['id_mapel'] ?>&id_kelas=<?= $row['id_kelas'] ?>" class="btn btn-primary btn-sm py-1.5 px-4 text-xs">
                <i class="ri-pencil-line"></i> Input Nilai
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    <?php else: ?>
      <div class="ui-empty-state">
        <div class="ui-empty-icon"><i class="ri-folder-info-line text-2xl"></i></div>
        <h3 class="text-lg font-bold text-slate-700 mb-1">Belum Ada Kelas Diampu</h3>
        <p class="text-sm text-slate-400 max-w-sm">Anda belum ditugaskan sebagai pengampu mata pelajaran kelas mana pun.</p>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'include/footer.php'; ?>
