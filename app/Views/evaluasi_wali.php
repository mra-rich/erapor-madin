<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_GRADES);

$id_pengguna = $_SESSION['id_pengguna'] ?? 0;
$peran = $_SESSION['peran'] ?? '';

if (!in_array($peran, ['Wali Kelas', 'Admin'])) {
    die("Akses ditolak. Halaman ini khusus Wali Kelas atau Admin.");
}

// Ambil tahun ajaran & semester aktif
$q_pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
$data_pengaturan = mysqli_fetch_assoc($q_pengaturan);
$tahun_aktif = $data_pengaturan['tahun_ajaran'] ?? '2024/2025';
$semester_aktif = $data_pengaturan['semester'] ?? 1;

// Cari kelas binaan
$query_kelas = "SELECT k.*, t.nama_tingkat FROM kelas k LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat WHERE id_wali_kelas = $id_pengguna LIMIT 1";
if ($peran == 'Admin') {
    $query_kelas = "SELECT k.*, t.nama_tingkat FROM kelas k LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat LIMIT 1";
}
$result_kelas = mysqli_query($koneksi, $query_kelas);
$kelas_binaan = mysqli_fetch_assoc($result_kelas);

if (!$kelas_binaan) {
    include 'include/header.php';
    include 'include/navbar.php';
    include 'include/sidebar.php';
    echo '<div class="page-shell"><div class="page-inner"><div class="ui-empty-state"><div class="ui-empty-icon"><i class="ri-error-warning-line text-2xl text-red-500"></i></div><h3 class="text-lg font-bold text-slate-700">Akses Dibatasi</h3><p class="text-sm text-slate-400">Anda belum ditetapkan sebagai Wali Kelas di kelas binaan mana pun.</p></div></div></div>';
    include 'include/footer.php';
    exit;
}

$id_kelas = $kelas_binaan['id_kelas'];
$tingkatan_kategori = $kelas_binaan['nama_tingkat'] ?? '';
$tingkatan_angka = $kelas_binaan['nama_kelas'];
$rombel_display = (!isset($kelas_binaan['nama_rombel']) || $kelas_binaan['nama_rombel'] === '-') ? '' : $kelas_binaan['nama_rombel'] . ' ';
$singkatan_map = ['Ibtida\'iyah' => 'MIF', 'Tsanawiyah' => 'MTsF', 'Aliyah' => 'MAF'];
$singkatan = $singkatan_map[$tingkatan_kategori] ?? $tingkatan_kategori;
$nama_kelas_lengkap = trim($tingkatan_angka . ' ' . $rombel_display . $singkatan);

// Ambil daftar santri
$query_siswa = "
    SELECT 
        s.id_siswa, s.nama, s.nisn, 
        tr.id_transaksi,
        kp.kelakuan, kp.kerajinan, kp.kerapian, kp.kedisiplinan,
        ex.baca_quran, ex.baca_kitab, ex.muhafadhoh, ex.kaligrafi,
        cw.catatan,
        ab.sakit, ab.izin, ab.tanpa_keterangan
    FROM riwayat_kelas r
    JOIN siswa s ON r.id_siswa = s.id_siswa
    LEFT JOIN transaksi_raport tr ON tr.id_siswa = s.id_siswa AND tr.tahun_ajaran = '$tahun_aktif' AND tr.semester = $semester_aktif
    LEFT JOIN kepribadian kp ON kp.id_transaksi = tr.id_transaksi
    LEFT JOIN ekstrakurikuler ex ON ex.id_transaksi = tr.id_transaksi
    LEFT JOIN catatan_wali_kelas cw ON cw.id_transaksi = tr.id_transaksi
    LEFT JOIN absensi ab ON ab.id_transaksi = tr.id_transaksi
    WHERE r.id_kelas = $id_kelas AND r.tahun_ajaran = '$tahun_aktif'
    ORDER BY s.nama ASC
";
$result_siswa = mysqli_query($koneksi, $query_siswa);
$siswa_list = [];
while ($row = mysqli_fetch_assoc($result_siswa)) {
    $siswa_list[] = $row;
}

include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="page-shell">
  <div class="page-inner">

    <!-- Page Header -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="page-title">Evaluasi Kelas Binaan</h1>
        <p class="page-subtitle">
          Kelas <span class="font-bold text-indigo-600"><?= htmlspecialchars($nama_kelas_lengkap) ?></span>
          &bull; Sem. <?= $semester_aktif == 1 ? 'Ganjil' : 'Genap' ?> (<?= htmlspecialchars($tahun_aktif) ?>)
        </p>
      </div>
      <div class="bg-emerald-50 text-emerald-700 px-3 py-2 rounded-xl border border-emerald-100 text-xs font-semibold flex items-center gap-1.5 shrink-0 self-stretch sm:self-auto justify-center">
        <i class="ri-shield-check-line"></i> Auto-save aktif (Perubahan disimpan otomatis)
      </div>
    </div>

    <form action="proses_evaluasi_wali" method="POST" id="formEvaluasi">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
      <input type="hidden" name="id_kelas" value="<?= $id_kelas ?>">

      <?php if (count($siswa_list) > 0): ?>

        <!-- ═══ DESKTOP VIEW: FULL TABLE (sm+) ═══ -->
        <div class="hidden sm:block table-scroll-wrap">
          <table class="ui-table">
            <thead>
              <tr>
                <th rowspan="2" class="w-10 text-center border-r border-slate-200">No</th>
                <th rowspan="2" class="border-r border-slate-200">Nama Santri</th>
                <th colspan="4" class="text-center bg-blue-50/50 border-r border-b border-slate-200">Kepribadian</th>
                <th colspan="4" class="text-center bg-emerald-50/50 border-r border-b border-slate-200">Ekstrakurikuler</th>
                <th colspan="3" class="text-center bg-amber-50/50 border-r border-b border-slate-200">Absensi</th>
                <th rowspan="2" class="text-center">Catatan</th>
              </tr>
              <tr>
                <!-- Kepribadian -->
                <th class="bg-blue-50/20 text-center border-r border-slate-100">Kelakuan</th>
                <th class="bg-blue-50/20 text-center border-r border-slate-100">Kerajinan</th>
                <th class="bg-blue-50/20 text-center border-r border-slate-100">Kerapian</th>
                <th class="bg-blue-50/20 text-center border-r border-slate-200">Kedisiplinan</th>
                <!-- Ekskul -->
                <th class="bg-emerald-50/20 text-center border-r border-slate-100">Al-Qur'an</th>
                <th class="bg-emerald-50/20 text-center border-r border-slate-100">Kitab</th>
                <th class="bg-emerald-50/20 text-center border-r border-slate-100">Muhafadhoh</th>
                <th class="bg-emerald-50/20 text-center border-r border-slate-200">Kaligrafi</th>
                <!-- Absen -->
                <th class="bg-amber-50/20 text-center border-r border-slate-100" title="Sakit">S</th>
                <th class="bg-amber-50/20 text-center border-r border-slate-100" title="Izin">I</th>
                <th class="bg-amber-50/20 text-center border-r border-slate-200" title="Alpha">A</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $no = 1;
              $options_kepribadian = ['A' => 'Amat Baik', 'B' => 'Baik', 'C' => 'Cukup', 'D' => 'Kurang'];
              
              function getColorClass($val) {
                  switch($val) {
                      case 'C': return 'bg-yellow-50 text-yellow-700 border-yellow-200';
                      case 'D': return 'bg-red-50 text-red-700 border-red-200';
                      default: return 'bg-white text-slate-700 border-slate-200';
                  }
              }

              foreach ($siswa_list as $row): 
                $id_s = $row['id_siswa'];
                $kelakuan = $row['kelakuan'] ?: 'B';
                $kerajinan = $row['kerajinan'] ?: 'B';
                $kerapian = $row['kerapian'] ?: 'B';
                $kedisiplinan = $row['kedisiplinan'] ?: 'B';
                $baca_quran = $row['baca_quran'] ?: 'B';
                $baca_kitab = $row['baca_kitab'] ?: 'B';
                $muhafadhoh = $row['muhafadhoh'] ?: 'B';
                $kaligrafi = $row['kaligrafi'] ?: 'B';
                $sakit = $row['sakit'] ?? 0;
                $izin = $row['izin'] ?? 0;
                $tanpa_keterangan = $row['tanpa_keterangan'] ?? 0;
              ?>
              <tr>
                <td class="text-center text-slate-400 text-xs border-r border-slate-200"><?= $no++ ?></td>
                <td class="border-r border-slate-200 font-semibold text-slate-800 text-xs">
                  <p class="truncate max-w-[150px] uppercase"><?= htmlspecialchars($row['nama']) ?></p>
                  <p class="text-[9px] font-normal font-mono text-slate-400 mt-0.5"><?= htmlspecialchars($row['nisn']) ?></p>
                  <input type="hidden" name="id_siswa[]" value="<?= $id_s ?>">
                </td>
                <!-- Kepribadian -->
                <td class="p-1 border-r border-slate-100">
                  <select name="kelakuan[<?= $id_s ?>]" class="ui-select py-1 px-1.5 text-xs eval-select <?= getColorClass($kelakuan) ?>">
                    <?php foreach($options_kepribadian as $val => $label): ?>
                      <option value="<?= $val ?>" <?= ($kelakuan == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td class="p-1 border-r border-slate-100">
                  <select name="kerajinan[<?= $id_s ?>]" class="ui-select py-1 px-1.5 text-xs eval-select <?= getColorClass($kerajinan) ?>">
                    <?php foreach($options_kepribadian as $val => $label): ?>
                      <option value="<?= $val ?>" <?= ($kerajinan == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td class="p-1 border-r border-slate-100">
                  <select name="kerapian[<?= $id_s ?>]" class="ui-select py-1 px-1.5 text-xs eval-select <?= getColorClass($kerapian) ?>">
                    <?php foreach($options_kepribadian as $val => $label): ?>
                      <option value="<?= $val ?>" <?= ($kerapian == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td class="p-1 border-r border-slate-200">
                  <select name="kedisiplinan[<?= $id_s ?>]" class="ui-select py-1 px-1.5 text-xs eval-select <?= getColorClass($kedisiplinan) ?>">
                    <?php foreach($options_kepribadian as $val => $label): ?>
                      <option value="<?= $val ?>" <?= ($kedisiplinan == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <!-- Ekskul -->
                <td class="p-1 border-r border-slate-100">
                  <select name="baca_quran[<?= $id_s ?>]" class="ui-select py-1 px-1.5 text-xs eval-select <?= getColorClass($baca_quran) ?>">
                    <?php foreach($options_kepribadian as $val => $label): ?>
                      <option value="<?= $val ?>" <?= ($baca_quran == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td class="p-1 border-r border-slate-100">
                  <select name="baca_kitab[<?= $id_s ?>]" class="ui-select py-1 px-1.5 text-xs eval-select <?= getColorClass($baca_kitab) ?>">
                    <?php foreach($options_kepribadian as $val => $label): ?>
                      <option value="<?= $val ?>" <?= ($baca_kitab == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td class="p-1 border-r border-slate-100">
                  <select name="muhafadhoh[<?= $id_s ?>]" class="ui-select py-1 px-1.5 text-xs eval-select <?= getColorClass($muhafadhoh) ?>">
                    <?php foreach($options_kepribadian as $val => $label): ?>
                      <option value="<?= $val ?>" <?= ($muhafadhoh == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td class="p-1 border-r border-slate-200">
                  <select name="kaligrafi[<?= $id_s ?>]" class="ui-select py-1 px-1.5 text-xs eval-select <?= getColorClass($kaligrafi) ?>">
                    <?php foreach($options_kepribadian as $val => $label): ?>
                      <option value="<?= $val ?>" <?= ($kaligrafi == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <!-- Absen -->
                <td class="p-1 border-r border-slate-100">
                  <input type="number" name="sakit[<?= $id_s ?>]" value="<?= $sakit ?>" min="0" class="ui-input py-1 px-0.5 text-center font-bold text-xs w-10">
                </td>
                <td class="p-1 border-r border-slate-100">
                  <input type="number" name="izin[<?= $id_s ?>]" value="<?= $izin ?>" min="0" class="ui-input py-1 px-0.5 text-center font-bold text-xs w-10">
                </td>
                <td class="p-1 border-r border-slate-200">
                  <input type="number" name="alpha[<?= $id_s ?>]" value="<?= $tanpa_keterangan ?>" min="0" class="ui-input py-1 px-0.5 text-center font-bold text-xs w-10">
                </td>
                <!-- Catatan -->
                <td class="p-1">
                  <textarea name="catatan[<?= $id_s ?>]" rows="1" class="ui-textarea min-h-8 py-1 text-xs" placeholder="Catatan..."><?= htmlspecialchars($row['catatan'] ?? '') ?></textarea>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- ═══ MOBILE VIEW: WITH SWITCHER & TAB MODE (below sm) ═══ -->
        <div class="sm:hidden mb-20 space-y-4">
          <!-- View Switcher -->
          <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between gap-3">
            <span class="text-xs font-bold text-slate-600 flex items-center gap-1.5">
              <i class="ri-layout-grid-line text-sm text-indigo-600"></i> Mode Tampilan
            </span>
            <div class="flex bg-slate-100 p-1 rounded-lg border border-slate-200">
              <button type="button" id="btnModeDetail" class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all duration-200 flex items-center gap-1">
                <i class="ri-slideshow-line"></i> Detail (Card)
              </button>
              <button type="button" id="btnModeTab" class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all duration-200 flex items-center gap-1">
                <i class="ri-list-check-3"></i> Ringkas (Tab)
              </button>
            </div>
          </div>

          <!-- 1. Mode Detail (Original Stack Layout) -->
          <div id="mobile-detail-view" class="space-y-2">
            <?php 
            $no = 1;
            foreach ($siswa_list as $row): 
              $id_s = $row['id_siswa'];
              $kelakuan = $row['kelakuan'] ?: 'B';
              $kerajinan = $row['kerajinan'] ?: 'B';
              $kerapian = $row['kerapian'] ?: 'B';
              $kedisiplinan = $row['kedisiplinan'] ?: 'B';
              $baca_quran = $row['baca_quran'] ?: 'B';
              $baca_kitab = $row['baca_kitab'] ?: 'B';
              $muhafadhoh = $row['muhafadhoh'] ?: 'B';
              $kaligrafi = $row['kaligrafi'] ?: 'B';
              $sakit = $row['sakit'] ?? 0;
              $izin = $row['izin'] ?? 0;
              $tanpa_keterangan = $row['tanpa_keterangan'] ?? 0;
            ?>
            <div class="ui-card">
              <!-- Header Card (Nama Santri) -->
              <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-t-xl border-b border-slate-200">
                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold"><?= $no++ ?></div>
                <div class="flex-1 min-w-0">
                  <p class="font-bold text-slate-800 text-sm truncate uppercase"><?= htmlspecialchars($row['nama']) ?></p>
                  <p class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($row['nisn']) ?></p>
                </div>
              </div>

              <!-- Body Tabs (Sub-forms) -->
              <div class="p-4 space-y-4">
                
                <!-- Tab 1: Kepribadian -->
                <div>
                  <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="ri-user-heart-line text-xs"></i> Kepribadian</p>
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Kelakuan</label>
                      <select name="kelakuan[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($kelakuan) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($kelakuan == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Kerajinan</label>
                      <select name="kerajinan[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($kerajinan) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($kerajinan == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Kerapian</label>
                      <select name="kerapian[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($kerapian) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($kerapian == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Kedisiplinan</label>
                      <select name="kedisiplinan[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($kedisiplinan) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($kedisiplinan == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Tab 2: Ekstrakurikuler -->
                <div class="pt-3 border-t border-slate-100">
                  <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="ri-book-read-line text-xs"></i> Ekstrakurikuler</p>
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Al-Qur'an</label>
                      <select name="baca_quran[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($baca_quran) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($baca_quran == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Baca Kitab</label>
                      <select name="baca_kitab[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($baca_kitab) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($baca_kitab == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Muhafadhoh</label>
                      <select name="muhafadhoh[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($muhafadhoh) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($muhafadhoh == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Kaligrafi</label>
                      <select name="kaligrafi[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($kaligrafi) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($kaligrafi == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Tab 3: Absensi -->
                <div class="pt-3 border-t border-slate-100">
                  <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="ri-calendar-todo-line text-xs"></i> Absensi (Hari)</p>
                  <div class="grid grid-cols-3 gap-2">
                    <div class="flex items-center justify-between border rounded-lg px-3 py-1 bg-slate-50">
                      <span class="text-xs text-slate-500 font-semibold">Sakit</span>
                      <input type="number" name="sakit[<?= $id_s ?>]" value="<?= $sakit ?>" min="0" class="w-8 h-8 text-center text-xs font-bold bg-transparent border-0 focus:ring-0 focus:outline-none p-0">
                    </div>
                    <div class="flex items-center justify-between border rounded-lg px-3 py-1 bg-slate-50">
                      <span class="text-xs text-slate-500 font-semibold">Izin</span>
                      <input type="number" name="izin[<?= $id_s ?>]" value="<?= $izin ?>" min="0" class="w-8 h-8 text-center text-xs font-bold bg-transparent border-0 focus:ring-0 focus:outline-none p-0">
                    </div>
                    <div class="flex items-center justify-between border rounded-lg px-3 py-1 bg-slate-50">
                      <span class="text-xs text-slate-500 font-semibold">Alpha</span>
                      <input type="number" name="alpha[<?= $id_s ?>]" value="<?= $tanpa_keterangan ?>" min="0" class="w-8 h-8 text-center text-xs font-bold bg-transparent border-0 focus:ring-0 focus:outline-none p-0">
                    </div>
                  </div>
                </div>

                <!-- Tab 4: Catatan -->
                <div class="pt-3 border-t border-slate-100">
                  <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 flex items-center gap-1.5"><i class="ri-chat-1-line text-xs"></i> Catatan Wali Kelas</p>
                  <textarea name="catatan[<?= $id_s ?>]" rows="2" class="ui-textarea text-xs py-2 px-3" placeholder="Tulis catatan perkembangan santri..."><?= htmlspecialchars($row['catatan'] ?? '') ?></textarea>
                </div>

              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- 2. Mode Tab Kategori (New Tabbed Layout) -->
          <div id="mobile-tab-view" class="hidden space-y-4">
            <!-- Tab buttons bar -->
            <div class="flex bg-slate-100 p-1 rounded-lg border border-slate-200 gap-1 overflow-x-auto">
              <button type="button" data-tab="kepribadian" class="mobile-tab-btn flex-1 py-1.5 px-2.5 text-center text-xs font-bold rounded-md transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-1">
                <i class="ri-user-heart-line text-xs"></i> Kepribadian
              </button>
              <button type="button" data-tab="ekskul" class="mobile-tab-btn flex-1 py-1.5 px-2.5 text-center text-xs font-bold rounded-md transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-1">
                <i class="ri-book-read-line text-xs"></i> Ekskul
              </button>
              <button type="button" data-tab="absensi" class="mobile-tab-btn flex-1 py-1.5 px-2.5 text-center text-xs font-bold rounded-md transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-1">
                <i class="ri-calendar-todo-line text-xs"></i> Absensi
              </button>
              <button type="button" data-tab="catatan" class="mobile-tab-btn flex-1 py-1.5 px-2.5 text-center text-xs font-bold rounded-md transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-1">
                <i class="ri-chat-1-line text-xs"></i> Catatan
              </button>
            </div>

            <!-- Student cards in tab mode -->
            <div class="space-y-3">
              <?php 
              $no_tab = 1;
              foreach ($siswa_list as $row): 
                $id_s = $row['id_siswa'];
                $kelakuan = $row['kelakuan'] ?: 'B';
                $kerajinan = $row['kerajinan'] ?: 'B';
                $kerapian = $row['kerapian'] ?: 'B';
                $kedisiplinan = $row['kedisiplinan'] ?: 'B';
                $baca_quran = $row['baca_quran'] ?: 'B';
                $baca_kitab = $row['baca_kitab'] ?: 'B';
                $muhafadhoh = $row['muhafadhoh'] ?: 'B';
                $kaligrafi = $row['kaligrafi'] ?: 'B';
                $sakit = $row['sakit'] ?? 0;
                $izin = $row['izin'] ?? 0;
                $tanpa_keterangan = $row['tanpa_keterangan'] ?? 0;
              ?>
              <div class="ui-card p-4">
                <!-- Student Name & NISN -->
                <div class="flex items-center gap-3 mb-3 border-b border-slate-100 pb-2">
                  <div class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px] font-bold"><?= $no_tab++ ?></div>
                  <div class="flex-1 min-w-0">
                    <p class="font-bold text-slate-800 text-xs truncate uppercase"><?= htmlspecialchars($row['nama']) ?></p>
                    <p class="text-[9px] text-slate-400 font-mono"><?= htmlspecialchars($row['nisn']) ?></p>
                  </div>
                </div>

                <!-- Content Kepribadian -->
                <div class="mobile-tab-content" data-tab-content="kepribadian">
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Kelakuan</label>
                      <select name="kelakuan[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($kelakuan) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($kelakuan == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Kerajinan</label>
                      <select name="kerajinan[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($kerajinan) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($kerajinan == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Kerapian</label>
                      <select name="kerapian[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($kerapian) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($kerapian == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Kedisiplinan</label>
                      <select name="kedisiplinan[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($kedisiplinan) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($kedisiplinan == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Content Ekskul -->
                <div class="mobile-tab-content hidden" data-tab-content="ekskul">
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Al-Qur'an</label>
                      <select name="baca_quran[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($baca_quran) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($baca_quran == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Baca Kitab</label>
                      <select name="baca_kitab[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($baca_kitab) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($baca_kitab == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Muhafadhoh</label>
                      <select name="muhafadhoh[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($muhafadhoh) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($muhafadhoh == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="text-[10px] font-semibold text-slate-500 mb-1 block">Kaligrafi</label>
                      <select name="kaligrafi[<?= $id_s ?>]" class="ui-select py-1.5 px-2 text-xs eval-select <?= getColorClass($kaligrafi) ?>">
                        <?php foreach($options_kepribadian as $val => $label): ?>
                          <option value="<?= $val ?>" <?= ($kaligrafi == $val) ? 'selected' : '' ?>><?= $val ?> <?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Content Absensi -->
                <div class="mobile-tab-content hidden" data-tab-content="absensi">
                  <div class="grid grid-cols-3 gap-2">
                    <div class="flex items-center justify-between border rounded-lg px-3 py-1 bg-slate-50">
                      <span class="text-xs text-slate-500 font-semibold">Sakit</span>
                      <input type="number" name="sakit[<?= $id_s ?>]" value="<?= $sakit ?>" min="0" class="w-8 h-8 text-center text-xs font-bold bg-transparent border-0 focus:ring-0 focus:outline-none p-0">
                    </div>
                    <div class="flex items-center justify-between border rounded-lg px-3 py-1 bg-slate-50">
                      <span class="text-xs text-slate-500 font-semibold">Izin</span>
                      <input type="number" name="izin[<?= $id_s ?>]" value="<?= $izin ?>" min="0" class="w-8 h-8 text-center text-xs font-bold bg-transparent border-0 focus:ring-0 focus:outline-none p-0">
                    </div>
                    <div class="flex items-center justify-between border rounded-lg px-3 py-1 bg-slate-50">
                      <span class="text-xs text-slate-500 font-semibold">Alpha</span>
                      <input type="number" name="alpha[<?= $id_s ?>]" value="<?= $tanpa_keterangan ?>" min="0" class="w-8 h-8 text-center text-xs font-bold bg-transparent border-0 focus:ring-0 focus:outline-none p-0">
                    </div>
                  </div>
                </div>

                <!-- Content Catatan -->
                <div class="mobile-tab-content hidden" data-tab-content="catatan">
                  <textarea name="catatan[<?= $id_s ?>]" rows="2" class="ui-textarea text-xs py-2 px-3" placeholder="Tulis catatan perkembangan santri..."><?= htmlspecialchars($row['catatan'] ?? '') ?></textarea>
                </div>

              </div>
              <?php endforeach; ?>
            </div>
          </div>

        </div>

      <?php else: ?>
        <div class="ui-empty-state">
          <div class="ui-empty-icon"><i class="ri-team-line text-2xl"></i></div>
          <h3 class="text-lg font-bold text-slate-700 mb-1">Belum Ada Santri</h3>
          <p class="text-sm text-slate-400">Belum ada santri terdaftar di kelas binaan Anda untuk tahun ajaran aktif.</p>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- Dynamic color changer for dropdown status selection -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
    const form = document.getElementById('formEvaluasi');
    if (!form) return;

    const selects = form.querySelectorAll('select.eval-select');
    
    function updateColor(selectElement) {
        const value = selectElement.value;
        selectElement.classList.remove('bg-yellow-50', 'text-yellow-700', 'bg-red-50', 'text-red-700', 'bg-white', 'text-slate-700');
        
        if (value === 'C') {
            selectElement.classList.add('bg-yellow-50', 'text-yellow-700');
        } else if (value === 'D') {
            selectElement.classList.add('bg-red-50', 'text-red-700');
        } else {
            selectElement.classList.add('bg-white', 'text-slate-700');
        }
    }

    // View Switcher & Tab selection logic
    const btnModeDetail = document.getElementById('btnModeDetail');
    const btnModeTab = document.getElementById('btnModeTab');
    const detailView = document.getElementById('mobile-detail-view');
    const tabView = document.getElementById('mobile-tab-view');
    const tabButtons = document.querySelectorAll('.mobile-tab-btn');
    const tabContents = document.querySelectorAll('.mobile-tab-content');

    function applyMode(mode) {
        if (mode === 'tab') {
            detailView.classList.add('hidden');
            tabView.classList.remove('hidden');
            btnModeTab.className = "px-3 py-1.5 text-xs font-semibold rounded-md transition-all duration-200 flex items-center gap-1 bg-white shadow-sm text-indigo-600 border border-slate-200";
            btnModeDetail.className = "px-3 py-1.5 text-xs font-semibold rounded-md transition-all duration-200 flex items-center gap-1 text-slate-500 hover:text-slate-700 bg-transparent";
        } else {
            tabView.classList.add('hidden');
            detailView.classList.remove('hidden');
            btnModeDetail.className = "px-3 py-1.5 text-xs font-semibold rounded-md transition-all duration-200 flex items-center gap-1 bg-white shadow-sm text-indigo-600 border border-slate-200";
            btnModeTab.className = "px-3 py-1.5 text-xs font-semibold rounded-md transition-all duration-200 flex items-center gap-1 text-slate-500 hover:text-slate-700 bg-transparent";
        }
        localStorage.setItem('eval_view_mode', mode);
    }

    function applyTab(tab) {
        tabButtons.forEach(btn => {
            if (btn.getAttribute('data-tab') === tab) {
                btn.className = "mobile-tab-btn flex-1 py-1.5 px-2.5 text-center text-xs font-bold rounded-md transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-1 bg-indigo-600 text-white shadow-sm";
            } else {
                btn.className = "mobile-tab-btn flex-1 py-1.5 px-2.5 text-center text-xs font-bold rounded-md transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-1 text-slate-500 hover:text-slate-700 bg-slate-50";
            }
        });
        tabContents.forEach(content => {
            if (content.getAttribute('data-tab-content') === tab) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });
        localStorage.setItem('eval_view_tab', tab);
    }

    if (btnModeDetail && btnModeTab) {
        btnModeDetail.addEventListener('click', () => applyMode('detail'));
        btnModeTab.addEventListener('click', () => applyMode('tab'));
        
        // Initial load for mode
        const savedMode = localStorage.getItem('eval_view_mode') || 'detail';
        applyMode(savedMode);
    }

    if (tabButtons.length > 0) {
        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                applyTab(btn.getAttribute('data-tab'));
            });
        });

        // Initial load for tab
        const savedTab = localStorage.getItem('eval_view_tab') || 'kepribadian';
        applyTab(savedTab);
    }

    // Sync input values across different views (Desktop, Mobile Detail, Mobile Tab)
    function syncValue(target) {
        if (target && target.name) {
            const val = target.value;
            const matches = form.querySelectorAll(`[name="${CSS.escape(target.name)}"]`);
            matches.forEach(el => {
                if (el !== target) {
                    if (el.value !== val) {
                        el.value = val;
                        if (el.tagName === 'SELECT' && el.classList.contains('eval-select')) {
                            updateColor(el);
                        }
                    }
                }
            });
        }
    }

    let saveTimeout = null;

    const autoSave = () => {
        saveTimeout = null;
        const formData = new FormData(form);
        formData.append('ajax', '1');

        fetch('proses_evaluasi_wali.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, timerProgressBar: true });
            if (data.status === 'success') {
                Toast.fire({ icon: 'success', title: 'Perubahan tersimpan' });
            } else {
                Toast.fire({ icon: 'error', title: 'Gagal menyimpan data' });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, timerProgressBar: true });
            Toast.fire({ icon: 'error', title: 'Koneksi terputus' });
        });
    };

    form.addEventListener('input', (e) => {
        syncValue(e.target);
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(autoSave, 1000);
    });

    selects.forEach(select => {
        updateColor(select);
        select.addEventListener("change", () => {
            syncValue(select);
            updateColor(select);
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(autoSave, 500);
        });
    });

    const flushAutoSave = () => {
        if (saveTimeout) {
            clearTimeout(saveTimeout);
            saveTimeout = null;
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            if (navigator.sendBeacon) {
                navigator.sendBeacon('proses_evaluasi_wali.php', formData);
            } else {
                fetch('proses_evaluasi_wali.php', { method: 'POST', body: formData, keepalive: true });
            }
        }
    };

    if (window.__eraporFlushAutoSave) {
        window.removeEventListener('beforeunload', window.__eraporFlushAutoSave);
        window.removeEventListener('pagehide', window.__eraporFlushAutoSave);
        document.body.removeEventListener('htmx:beforeRequest', window.__eraporFlushAutoSave);
    }
    
    window.__eraporFlushAutoSave = flushAutoSave;

    window.addEventListener('beforeunload', window.__eraporFlushAutoSave);
    window.addEventListener('pagehide', window.__eraporFlushAutoSave);
    document.body.addEventListener('htmx:beforeRequest', window.__eraporFlushAutoSave);
})();
</script>

<?php include 'include/footer.php'; ?>
