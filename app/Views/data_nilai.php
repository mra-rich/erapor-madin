<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_VIEW_ALL);
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// Ambil semua kelas
if ($_SESSION['peran'] === 'Wali Kelas') {
    $id_pengguna = $_SESSION['id_pengguna'];
    $queryKelas = "SELECT k.id_kelas, k.nama_kelas, k.nama_rombel, t.nama_tingkat 
                   FROM kelas k 
                   LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat 
                   WHERE k.id_wali_kelas = '$id_pengguna' 
                   ORDER BY t.nama_tingkat ASC, CAST(k.nama_kelas AS UNSIGNED) ASC, k.nama_rombel ASC";
} else {
    $queryKelas = "SELECT k.id_kelas, k.nama_kelas, k.nama_rombel, t.nama_tingkat 
                   FROM kelas k 
                   LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat 
                   ORDER BY t.nama_tingkat ASC, CAST(k.nama_kelas AS UNSIGNED) ASC, k.nama_rombel ASC";
}
$resultKelas = mysqli_query($koneksi, $queryKelas);
$kelasList = [];
while ($kelas = mysqli_fetch_assoc($resultKelas)) {
    $tingkatan_kategori = $kelas['nama_tingkat'] ?? '';
    $tingkatan_angka = $kelas['nama_kelas'] ?? '';
    $rombel_display = (!isset($kelas['nama_rombel']) || $kelas['nama_rombel'] === '-') ? '' : $kelas['nama_rombel'] . ' ';
    $singkatan_map = ['Ibtida\'iyah' => 'MIF', 'Tsanawiyah' => 'MTsF', 'Aliyah' => 'MAF'];
    $singkatan = $singkatan_map[$tingkatan_kategori] ?? $tingkatan_kategori;
    $kelas['nama_kelas_lengkap'] = trim($tingkatan_angka . ' ' . $rombel_display . $singkatan);
    if(empty($kelas['nama_kelas_lengkap'])) $kelas['nama_kelas_lengkap'] = 'Unknown';
    $kelasList[] = $kelas;
}

// Jika ada filter kelas yang dipilih
$selectedKelas = isset($_GET['kelas']) && $_GET['kelas'] !== "" ? $_GET['kelas'] : null;

if ($_SESSION['peran'] === 'Wali Kelas' && count($kelasList) > 0) {
    $selectedKelas = $kelasList[0]['id_kelas'];
}

// Jika ada filter semester yang dipilih
$selectedSemester = isset($_GET['semester']) && $_GET['semester'] !== "" ? $_GET['semester'] : null;

// Tampilkan notifikasi jika ada
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'sukses') {
        echo "<div class='p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50' role='alert'>
                <span class='font-medium'>Berhasil!</span> Data berhasil diperbarui.
              </div>";
    } else if ($_GET['status'] == 'gagal') {
        echo "<div class='p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50' role='alert'>
                <span class='font-medium'>Gagal!</span> Terjadi kesalahan saat memperbarui data.
              </div>";
    }
}
?>

<div class="page-shell">
  <div class="page-inner">
    
    <!-- Page Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="page-title">Data Nilai Santri</h1>
        <p class="page-subtitle">Kelola dan filter data nilai, absensi, serta kepribadian santri</p>
      </div>
      
      <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
        <?php if ($_SESSION['peran'] !== 'Kepala Madrasah'): ?>
          <a href="penilaian_mapel.php" class="btn btn-primary btn-sm flex-1 sm:flex-none">
            <i class="ri-add-line"></i> Input Nilai
          </a>
          <a href="import_nilai.php" class="btn btn-secondary btn-sm text-emerald-700 border-emerald-200 bg-emerald-50 hover:bg-emerald-100 flex-1 sm:flex-none">
            <i class="ri-file-excel-2-line"></i> Import
          </a>
        <?php endif; ?>
        <a href="export_nilai.php?kelas=<?= urlencode($selectedKelas ?? ''); ?>&semester=<?= urlencode($selectedSemester ?? ''); ?>" 
           class="btn btn-secondary btn-sm text-indigo-700 border-indigo-200 bg-indigo-50 hover:bg-indigo-100 flex-1 sm:flex-none">
          <i class="ri-file-download-line"></i> Export
        </a>
      </div>
    </div>

    <!-- Filter Card -->
    <div class="ui-card ui-card-body mb-6">
      <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
        <?php if ($_SESSION['peran'] !== 'Wali Kelas'): ?>
          <div class="sm:col-span-2">
            <?php 
            $no_autosubmit = true;
            $id_kelas_selected = isset($id_kelas) ? $id_kelas : (isset($kelas_aktif) ? $kelas_aktif : 0); 
            include 'include/filter_kelas.php'; 
            ?>
          </div>
        <?php else: ?>
          <div class="sm:col-span-2">
            <label class="ui-label">Wali Kelas</label>
            <input type="text" class="ui-input bg-slate-50 cursor-not-allowed font-medium text-slate-600" value="Kelas Anda" readonly>
          </div>
        <?php endif; ?>

        <div>
          <label class="ui-label">Semester</label>
          <select name="semester" id="semester" class="ui-select cursor-pointer font-semibold">
            <option value="">Semua Semester</option>
            <option value="1" <?= ($selectedSemester == '1') ? 'selected' : ''; ?>>Semester 1 (Ganjil)</option>
            <option value="2" <?= ($selectedSemester == '2') ? 'selected' : ''; ?>>Semester 2 (Genap)</option>
          </select>
        </div>

        <div class="sm:col-span-3 flex justify-end gap-2 mt-2">
          <button type="submit" class="btn btn-primary px-6">
            <i class="ri-search-line"></i> Tampilkan
          </button>
          <?php if($selectedKelas || $selectedSemester): ?>
            <a href="data_nilai.php" class="btn btn-secondary px-6">
              <i class="ri-refresh-line"></i> Reset
            </a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <?php
    if ($selectedKelas) {
        $kelasToShow = array_filter($kelasList, function ($kelas) use ($selectedKelas) {
            return $kelas['id_kelas'] == $selectedKelas;
        });
    } else {
        $kelasToShow = [];
        echo '<div class="ui-empty-state">';
        echo '  <div class="ui-empty-icon"><i class="ri-filter-3-line text-2xl"></i></div>';
        echo '  <h3 class="text-lg font-bold text-slate-700 mb-1">Pilih Kelas Terlebih Dahulu</h3>';
        echo '  <p class="text-sm text-slate-400 max-w-sm">Gunakan filter kelas di atas untuk menampilkan rincian nilai santri.</p>';
        echo '</div>';
    }

    foreach ($kelasToShow as $kelas):
        $id_kelas = $kelas['id_kelas'];
        $nama_kelas_lengkap = $kelas['nama_kelas_lengkap'];

        $q_ta = mysqli_query($koneksi, "SELECT tahun_ajaran FROM pengaturan LIMIT 1");
        $ta_aktif = mysqli_fetch_assoc($q_ta)['tahun_ajaran'];

        $whereClause = "WHERE r.id_kelas = '$id_kelas' AND r.tahun_ajaran = '$ta_aktif'";
        if ($selectedSemester) {
            $whereClause .= " AND t.semester = '$selectedSemester'";
        }

        $query = "SELECT s.id_siswa, s.nama, s.nomor_santri, r.id_kelas, t.id_transaksi, t.tahun_ajaran, t.semester,
                         a.izin, a.sakit, a.tanpa_keterangan, k.kelakuan, k.kerajinan, k.kerapian, c.catatan
                  FROM riwayat_kelas r
                  JOIN siswa s ON r.id_siswa = s.id_siswa
                  LEFT JOIN transaksi_raport t ON s.id_siswa = t.id_siswa AND t.tahun_ajaran = r.tahun_ajaran
                  LEFT JOIN absensi a ON t.id_transaksi = a.id_transaksi
                  LEFT JOIN kepribadian k ON t.id_transaksi = k.id_transaksi
                  LEFT JOIN catatan_wali_kelas c ON t.id_transaksi = c.id_transaksi
                  $whereClause
                  ORDER BY s.nama ASC";

        $result = mysqli_query($koneksi, $query);

        if (mysqli_num_rows($result) > 0):
            // Ambil mapel kelas
            $queryMapel = "SELECT DISTINCT m.id_mapel, m.nama_mapel FROM mata_pelajaran m JOIN pengampu_mapel pm ON m.id_mapel = pm.id_mapel WHERE pm.id_kelas = '$id_kelas' AND pm.status = 'Aktif' ORDER BY m.id_mapel";
            $resultMapel = mysqli_query($koneksi, $queryMapel);
            $mapelList = [];
            while ($mapel = mysqli_fetch_assoc($resultMapel)) {
                $mapelList[] = $mapel;
            }
    ?>
            <div class="mb-8">
              <h2 class="text-lg font-bold text-slate-800 mb-3 flex items-center gap-2">
                <i class="ri-folder-open-line text-indigo-500"></i> Kelas: <?= htmlspecialchars($nama_kelas_lengkap); ?>
              </h2>

              <!-- ═══ DESKTOP VIEW (sm+) ═══ -->
              <div class="hidden sm:block table-scroll-wrap">
                <table class="ui-table text-xs">
                  <thead>
                    <tr>
                      <th rowspan="2" class="w-12 text-center border-r border-slate-200">No</th>
                      <th rowspan="2" class="w-24 border-r border-slate-200">No. Induk</th>
                      <th rowspan="2" class="min-w-[150px] border-r border-slate-200">Nama Santri</th>
                      <th rowspan="2" class="w-20 text-center border-r border-slate-200">Sem.</th>
                      
                      <!-- Mapel header -->
                      <th colspan="<?= count($mapelList); ?>" class="text-center border-b border-r border-slate-200">Mata Pelajaran</th>

                      <th rowspan="2" class="w-16 text-center border-r border-slate-200">Jml</th>
                      <th rowspan="2" class="w-16 text-center border-r border-slate-200">Rata2</th>
                      <th colspan="3" class="text-center border-b border-r border-slate-200">Kepribadian</th>
                      <th rowspan="2" class="min-w-[150px]">Catatan Wali Kelas</th>
                      <?php if ($_SESSION['peran'] !== 'Kepala Madrasah' && $_SESSION['peran'] !== 'Wali Kelas'): ?>
                      <th rowspan="2" class="w-20 text-center">Aksi</th>
                      <?php endif; ?>
                    </tr>
                    <tr>
                      <?php foreach ($mapelList as $mapel): ?>
                        <th class="font-semibold text-center border-r border-slate-100"><?= htmlspecialchars($mapel['nama_mapel']); ?></th>
                      <?php endforeach; ?>
                      <th class="w-12 text-center border-r border-slate-100">Kel</th>
                      <th class="w-12 text-center border-r border-slate-100">Ker</th>
                      <th class="w-12 text-center border-r border-slate-200">Rap</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)):
                        $idSiswa = $row['id_siswa'];
                        $idTransaksi = $row['id_transaksi'];

                        $queryNilai = "SELECT id_mapel, nilai_angka FROM nilai WHERE id_transaksi IN (SELECT id_transaksi FROM transaksi_raport WHERE id_siswa = $idSiswa)";
                        $resultNilai = mysqli_query($koneksi, $queryNilai);
                        $nilaiMapel = [];
                        $totalNilai = 0;
                        $jumlahMapel = 0;
                        while ($nilai = mysqli_fetch_assoc($resultNilai)) {
                            $nilaiMapel[$nilai['id_mapel']] = $nilai['nilai_angka'];
                            $totalNilai += (int)$nilai['nilai_angka'];
                            $jumlahMapel++;
                        }
                        $rataRata = $jumlahMapel > 0 ? $totalNilai / $jumlahMapel : 0;
                    ?>
                    <tr>
                      <td class="text-center text-slate-400 text-xs border-r border-slate-200"><?= $no++; ?></td>
                      <td class="font-mono text-xs border-r border-slate-200"><?= htmlspecialchars($row['nomor_santri']); ?></td>
                      <td class="font-semibold text-slate-800 border-r border-slate-200"><?= htmlspecialchars($row['nama']); ?></td>
                      <td class="text-center text-slate-500 border-r border-slate-200"><?= $row['semester'] ? $row['semester'] : '-'; ?></td>
                      
                      <!-- Nilai mapel cells -->
                      <?php foreach ($mapelList as $mapel): ?>
                        <td class="text-center font-bold border-r border-slate-100 text-slate-700">
                          <?= isset($nilaiMapel[$mapel['id_mapel']]) ? $nilaiMapel[$mapel['id_mapel']] : '-'; ?>
                        </td>
                      <?php endforeach; ?>

                      <td class="text-center font-extrabold text-blue-600 border-r border-slate-200"><?= $totalNilai; ?></td>
                      <td class="text-center font-extrabold text-emerald-600 border-r border-slate-200"><?= round($rataRata, 1); ?></td>

                      <td class="text-center font-bold border-r border-slate-100"><?= htmlspecialchars($row['kelakuan'] ?? '-'); ?></td>
                      <td class="text-center font-bold border-r border-slate-100"><?= htmlspecialchars($row['kerajinan'] ?? '-'); ?></td>
                      <td class="text-center font-bold border-r border-slate-200"><?= htmlspecialchars($row['kerapian'] ?? '-'); ?></td>

                      <td class="text-slate-500 italic max-w-[150px] truncate" title="<?= htmlspecialchars($row['catatan'] ?? ''); ?>"><?= htmlspecialchars($row['catatan'] ?? '-'); ?></td>
                      
                      <?php if ($_SESSION['peran'] !== 'Kepala Madrasah' && $_SESSION['peran'] !== 'Wali Kelas'): ?>
                      <td class="text-center">
                        <div class="flex justify-center gap-0.5">
                          <?php if ($idTransaksi): ?>
                            <a href="edit_nilai.php?id=<?= $idTransaksi; ?>" class="w-7 h-7 flex items-center justify-center rounded-full text-slate-400 hover:text-blue-600 hover:bg-blue-50" title="Edit">
                              <i class="ri-edit-line"></i>
                            </a>
                            <a href="hapus_nilai.php?hapus=<?= (int) $idTransaksi; ?>&csrf_token=<?= generate_csrf_token(); ?>" class="w-7 h-7 flex items-center justify-center rounded-full text-slate-400 hover:text-red-600 hover:bg-red-50" onclick="return sweetConfirm(event, this, 'Hapus data nilai?');" title="Hapus">
                              <i class="ri-delete-bin-line"></i>
                            </a>
                          <?php else: ?>
                            <a href="penilaian_mapel.php" class="w-7 h-7 flex items-center justify-center rounded-full text-slate-400 hover:text-emerald-600 hover:bg-emerald-50" title="Input Nilai">
                              <i class="ri-add-line"></i>
                            </a>
                          <?php endif; ?>
                        </div>
                      </td>
                      <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>

              <!-- ═══ MOBILE VIEW (below sm) ═══ -->
              <div class="sm:hidden space-y-3">
                <?php
                // Reset pointer & loop again for mobile cards
                mysqli_data_seek($result, 0);
                $no_m = 1;
                while ($row = mysqli_fetch_assoc($result)):
                    $idSiswa = $row['id_siswa'];
                    $idTransaksi = $row['id_transaksi'];

                    // fetch nilai
                    $resultNilai = mysqli_query($koneksi, "SELECT id_mapel, nilai_angka FROM nilai WHERE id_transaksi IN (SELECT id_transaksi FROM transaksi_raport WHERE id_siswa = $idSiswa)");
                    $nilaiMapel = [];
                    $totalNilai = 0;
                    $jumlahMapel = 0;
                    while ($nilai = mysqli_fetch_assoc($resultNilai)) {
                        $nilaiMapel[$nilai['id_mapel']] = $nilai['nilai_angka'];
                        $totalNilai += (int)$nilai['nilai_angka'];
                        $jumlahMapel++;
                    }
                    $rataRata = $jumlahMapel > 0 ? $totalNilai / $jumlahMapel : 0;
                    
                    $inisial = mb_strtoupper(mb_substr($row['nama'], 0, 1, 'UTF-8'));
                    $colors = ['bg-emerald-100 text-emerald-700','bg-blue-100 text-blue-700','bg-violet-100 text-violet-700','bg-amber-100 text-amber-700','bg-rose-100 text-rose-700'];
                    $color = $colors[ord($inisial) % 5];
                ?>
                <div class="ui-card">
                  <!-- Card Header -->
                  <div class="flex items-center justify-between p-4 bg-slate-50 rounded-t-xl border-b border-slate-200">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full <?= $color ?> flex items-center justify-center text-xs font-bold"><?= $inisial ?></div>
                      <div class="min-w-0">
                        <p class="font-bold text-slate-800 text-sm truncate uppercase"><?= htmlspecialchars($row['nama']) ?></p>
                        <p class="text-[9px] text-slate-400 font-mono">No. Induk: <?= htmlspecialchars($row['nomor_santri']) ?></p>
                      </div>
                    </div>
                    <!-- Sem. & Rata2 badge -->
                    <div class="flex flex-col items-end shrink-0">
                      <span class="badge badge-info text-[9px]">Sem. <?= $row['semester'] ?: '-' ?></span>
                      <span class="text-xs font-extrabold text-emerald-600 mt-1">Rata2: <?= round($rataRata, 1) ?></span>
                    </div>
                  </div>

                  <!-- Card Body -->
                  <div class="p-4 space-y-3">
                    <!-- Mapel List Grid -->
                    <div>
                      <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-2">Nilai Pelajaran</p>
                      <div class="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <?php foreach ($mapelList as $mapel): 
                          $valNilai = $nilaiMapel[$mapel['id_mapel']] ?? '-';
                          $valInt = (int)$valNilai;
                          $valColor = $valNilai === '-' ? 'text-slate-400' : ($valInt >= 70 ? 'text-emerald-700' : 'text-amber-700');
                        ?>
                          <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-500 truncate mr-2"><?= htmlspecialchars($mapel['nama_mapel']) ?></span>
                            <span class="font-bold <?= $valColor ?>"><?= $valNilai ?></span>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>

                    <!-- Kepribadian & Catatan -->
                    <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-100 text-center">
                      <div class="bg-slate-50 p-1.5 rounded-lg border border-slate-100">
                        <p class="text-[9px] text-slate-400 font-medium">Kelakuan</p>
                        <p class="font-bold text-slate-700 text-xs mt-0.5"><?= htmlspecialchars($row['kelakuan'] ?? '-') ?></p>
                      </div>
                      <div class="bg-slate-50 p-1.5 rounded-lg border border-slate-100">
                        <p class="text-[9px] text-slate-400 font-medium">Kerajinan</p>
                        <p class="font-bold text-slate-700 text-xs mt-0.5"><?= htmlspecialchars($row['kerajinan'] ?? '-') ?></p>
                      </div>
                      <div class="bg-slate-50 p-1.5 rounded-lg border border-slate-100">
                        <p class="text-[9px] text-slate-400 font-medium">Kerapian</p>
                        <p class="font-bold text-slate-700 text-xs mt-0.5"><?= htmlspecialchars($row['kerapian'] ?? '-') ?></p>
                      </div>
                    </div>

                    <!-- Catatan display -->
                    <div class="pt-2.5 border-t border-slate-100">
                      <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Catatan Wali</p>
                      <p class="text-xs text-slate-600 italic bg-slate-50 p-2 rounded-lg border border-slate-100"><?= htmlspecialchars($row['catatan'] ?? '-') ?></p>
                    </div>

                    <!-- Actions for Mobile (only for role != Kepala Madrasah / Wali Kelas) -->
                    <?php if ($_SESSION['peran'] !== 'Kepala Madrasah' && $_SESSION['peran'] !== 'Wali Kelas'): ?>
                    <div class="pt-3 border-t border-slate-100 flex gap-2">
                      <?php if ($idTransaksi): ?>
                        <a href="edit_nilai.php?id=<?= $idTransaksi; ?>" class="btn btn-secondary btn-sm py-2 flex-1 text-xs justify-center"><i class="ri-edit-line"></i> Edit Nilai</a>
                        <a href="hapus_nilai.php?hapus=<?= (int) $idTransaksi; ?>&csrf_token=<?= generate_csrf_token(); ?>" class="btn btn-secondary btn-sm py-2 flex-1 text-xs text-red-600 border-red-200 bg-red-50 hover:bg-red-100 justify-center" onclick="return sweetConfirm(event, this, 'Hapus data nilai?');"><i class="ri-delete-bin-line"></i> Hapus</a>
                      <?php else: ?>
                        <a href="penilaian_mapel.php" class="btn btn-primary btn-sm py-2 flex-1 text-xs justify-center"><i class="ri-add-line"></i> Input Nilai</a>
                      <?php endif; ?>
                    </div>
                    <?php endif; ?>

                  </div>
                </div>
                <?php endwhile; ?>
              </div>

            </div>
    <?php
        endif;
    endforeach;
    ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function sweetConfirm(event, element, message) {
    event.preventDefault();
    Swal.fire({
        title: 'Konfirmasi',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, lakukan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = element.href;
        }
    });
    return false;
}
</script>

<?php include 'include/footer.php'; ?>
