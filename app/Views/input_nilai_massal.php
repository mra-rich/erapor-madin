<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_GRADES);

$id_mapel = isset($_GET['id_mapel']) ? (int)$_GET['id_mapel'] : 0;
$id_kelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : 0;

if ($id_mapel == 0 || $id_kelas == 0) {
    die("Parameter tidak valid.");
}

$id_pengguna = $_SESSION['id_pengguna'] ?? 0;
$peran = $_SESSION['peran'] ?? '';

// Cek hak akses guru terhadap mapel ini
if ($peran == 'Guru' || $peran == 'Wali Kelas') {
    $cek_akses = mysqli_query($koneksi, "
        SELECT pm.id 
        FROM pengampu_mapel pm 
        JOIN kelas k ON pm.id_kelas = k.id_kelas 
        WHERE pm.id_mapel = $id_mapel 
          AND pm.id_kelas = $id_kelas 
          AND (pm.id_guru = $id_pengguna OR k.id_wali_kelas = $id_pengguna) 
          AND pm.status = 'Aktif'
    ");
    if (mysqli_num_rows($cek_akses) == 0) {
        die("Anda tidak memiliki akses ke mata pelajaran ini di kelas tersebut.");
    }
}

// Ambil tahun ajaran & semester aktif
$q_pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
$data_pengaturan = mysqli_fetch_assoc($q_pengaturan);
$tahun_aktif = $data_pengaturan['tahun_ajaran'] ?? '2024/2025';
$semester_aktif = $data_pengaturan['semester'] ?? 1;

// Ambil detail mapel & kelas
$q_detail = mysqli_query($koneksi, "
    SELECT m.nama_mapel, k.nama_kelas, k.nama_rombel, t.nama_tingkat 
    FROM mata_pelajaran m 
    JOIN kelas k ON k.id_kelas = $id_kelas
    LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat
    WHERE m.id_mapel = $id_mapel
");
$detail = mysqli_fetch_assoc($q_detail);
$nama_mapel = $detail['nama_mapel'] ?? 'Unknown';

$tingkatan_kategori = $detail['nama_tingkat'] ?? '';
$tingkatan_angka = $detail['nama_kelas'] ?? '';
$rombel_display = (!isset($detail['nama_rombel']) || $detail['nama_rombel'] === '-') ? '' : $detail['nama_rombel'] . ' ';
$singkatan_map = ['Ibtida\'iyah' => 'MIF', 'Tsanawiyah' => 'MTsF', 'Aliyah' => 'MAF'];
$singkatan = $singkatan_map[$tingkatan_kategori] ?? $tingkatan_kategori;
$nama_kelas_lengkap = trim($tingkatan_angka . ' ' . $rombel_display . $singkatan);
if(empty($nama_kelas_lengkap)) $nama_kelas_lengkap = 'Unknown';

// Ambil daftar siswa di kelas ini beserta nilainya (jika sudah ada)
$query_siswa = "
    SELECT s.id_siswa, s.nama, s.nisn,
           (SELECT n.nilai_angka 
            FROM nilai n 
            JOIN transaksi_raport tr ON n.id_transaksi = tr.id_transaksi 
            WHERE tr.id_siswa = s.id_siswa 
              AND tr.tahun_ajaran = '$tahun_aktif' 
              AND tr.semester = $semester_aktif 
              AND n.id_mapel = $id_mapel LIMIT 1) as nilai_angka
    FROM riwayat_kelas r
    JOIN siswa s ON r.id_siswa = s.id_siswa
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
    <div class="mb-6 flex items-start gap-3">
      <a href="penilaian_mapel" class="btn btn-ghost btn-sm mt-0.5 shrink-0 rounded-xl h-9 w-9 p-0">
        <i class="ri-arrow-left-line text-base"></i>
      </a>
      <div class="flex-1 min-w-0">
        <h1 class="page-title truncate">Input Nilai: <?= htmlspecialchars($nama_mapel) ?></h1>
        <p class="page-subtitle">Kelas <?= htmlspecialchars($nama_kelas_lengkap) ?> &bull; Sem. <?= $semester_aktif == 1 ? 'Ganjil' : 'Genap' ?> &bull; <?= htmlspecialchars($tahun_aktif) ?></p>
      </div>
      <div class="hidden sm:flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-2 rounded-xl border border-emerald-100 text-xs font-semibold shrink-0">
        <i class="ri-shield-check-line"></i> Tersimpan ke Semester Aktif
      </div>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <div class="flex items-center gap-3 p-4 mb-5 text-sm text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-200" role="alert">
      <i class="ri-checkbox-circle-fill text-xl shrink-0"></i>
      <div><span class="font-bold">Berhasil!</span> Semua nilai santri berhasil disimpan.</div>
    </div>
    <?php endif; ?>

    <?php if (count($siswa_list) > 0):
      $sudah_isi = count(array_filter($siswa_list, fn($s) => $s['nilai_angka'] !== null && $s['nilai_angka'] !== ''));
      $belum_isi = count($siswa_list) - $sudah_isi;
    ?>
    <div class="grid grid-cols-3 gap-3 mb-5">
      <div class="ui-card ui-card-body py-3 text-center">
        <p class="text-2xl font-extrabold text-slate-800"><?= count($siswa_list) ?></p>
        <p class="text-xs text-slate-500 mt-0.5">Total Santri</p>
      </div>
      <div class="ui-card ui-card-body py-3 text-center">
        <p class="text-2xl font-extrabold text-emerald-600"><?= $sudah_isi ?></p>
        <p class="text-xs text-slate-500 mt-0.5">Sudah Dinilai</p>
      </div>
      <div class="ui-card ui-card-body py-3 text-center">
        <p class="text-2xl font-extrabold text-amber-500"><?= $belum_isi ?></p>
        <p class="text-xs text-slate-500 mt-0.5">Belum Dinilai</p>
      </div>
    </div>
    <?php endif; ?>

    <form action="proses_nilai_massal" method="POST" id="formNilai">
      <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
      <input type="hidden" name="id_mapel" value="<?= $id_mapel ?>">
      <input type="hidden" name="id_kelas" value="<?= $id_kelas ?>">

      <?php if (count($siswa_list) > 0): ?>

        <!-- DESKTOP TABLE (sm+) -->
        <div class="hidden sm:block table-scroll-wrap mb-4">
          <table class="ui-table">
            <thead>
              <tr>
                <th class="w-12 text-center">No</th>
                <th>Nama Santri</th>
                <th class="w-28 text-center">Nilai (0-100)</th>
                <th class="w-36 text-center">Predikat</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; foreach ($siswa_list as $siswa): ?>
              <tr>
                <td class="text-center text-slate-400 text-xs"><?= $no++ ?></td>
                <td>
                  <p class="font-semibold text-slate-800"><?= htmlspecialchars($siswa['nama']) ?></p>
                  <?php if (!empty($siswa['nisn'])): ?><p class="text-xs text-slate-400"><?= htmlspecialchars($siswa['nisn']) ?></p><?php endif; ?>
                </td>
                <td class="text-center">
                  <input type="number" name="nilai[<?= $siswa['id_siswa'] ?>]"
                         value="<?= htmlspecialchars($siswa['nilai_angka'] ?? '') ?>"
                         class="w-20 rounded-lg border border-slate-300 bg-white px-2 py-2 text-sm text-center font-bold text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition-colors input-nilai"
                         min="0" max="100" oninput="convertNilai(this,'huruf_<?= $siswa['id_siswa'] ?>')" placeholder="">
                </td>
                <td class="text-center">
                  <input type="text" id="huruf_<?= $siswa['id_siswa'] ?>"
                         class="w-28 rounded-lg bg-slate-100 px-2 py-2 text-xs text-center font-semibold text-slate-500 border-none pointer-events-none" readonly>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- MOBILE CARD LIST -->
        <div class="sm:hidden space-y-2 mb-24">
          <?php $no = 1; foreach ($siswa_list as $siswa): ?>
          <div class="ui-card flex items-center gap-3 px-4 py-3">
            <div class="shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold"><?= $no++ ?></div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-slate-800 text-sm truncate"><?= htmlspecialchars($siswa['nama']) ?></p>
              <p id="m-huruf-<?= $siswa['id_siswa'] ?>" class="text-xs text-slate-400 mt-0.5 h-4"><?php
                $v = $siswa['nilai_angka'] ?? '';
                if ($v !== '' && $v !== null) {
                  $vi = (int)$v;
                  if ($vi >= 90) echo 'Amat Baik';
                  elseif ($vi >= 80) echo 'Baik';
                  elseif ($vi >= 70) echo 'Cukup';
                  elseif ($vi >= 60) echo 'Kurang';
                  else echo 'Sangat Kurang';
                }
              ?></p>
            </div>
	            <div class="shrink-0 flex items-center">
	              <input type="number" id="m-nilai-<?= $siswa['id_siswa'] ?>" name="nilai[<?= $siswa['id_siswa'] ?>]"
	                     value="<?= htmlspecialchars($siswa['nilai_angka'] ?? '') ?>"
	                     class="w-20 h-10 rounded-xl border border-slate-300 bg-white text-base text-center font-bold text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition-colors input-nilai-mobile shadow-sm"
	                     min="0" max="100" oninput="convertNilaiMobile(this,'m-huruf-<?= $siswa['id_siswa'] ?>')" placeholder="">
	            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Sticky Save Bar -->
        <div class="fixed sm:relative bottom-0 left-0 right-0 sm:bottom-auto z-20 bg-white/95 sm:bg-white backdrop-blur-sm border-t border-slate-200 px-4 py-3 sm:rounded-xl sm:mt-4 sm:shadow-sm">
          <div class="flex items-center justify-between gap-3 max-w-7xl mx-auto flex-wrap">
            <!-- Auto-jump toggle -->
            <label class="flex items-center gap-2 cursor-pointer select-none" title="Kursor otomatis pindah ke santri berikutnya setelah Enter atau 3 digit">
              <input type="checkbox" id="chk-auto-jump" class="sr-only">
              <div id="auto-jump-track" class="w-10 h-6 rounded-full border-2 border-slate-300 bg-slate-200 flex items-center transition-colors duration-200 px-0.5">
                <div id="auto-jump-thumb" class="w-4 h-4 rounded-full bg-white shadow transition-transform duration-200 translate-x-0"></div>
              </div>
              <span id="auto-jump-label" class="text-xs font-semibold text-slate-500">Auto-lompat baris</span>
            </label>
            <div class="flex gap-2 w-full sm:w-auto">
              <button type="button" id="btn-fill-all" class="btn btn-secondary btn-sm flex-1 sm:flex-none">
                <i class="ri-magic-line"></i> Isi Semua
              </button>
              <button type="submit" class="btn btn-primary btn-sm flex-1 sm:flex-none">
                <i class="ri-save-3-fill"></i> Simpan Nilai
              </button>
            </div>
          </div>
        </div>

      <?php else: ?>
        <div class="ui-empty-state">
          <div class="ui-empty-icon"><i class="ri-team-line text-2xl"></i></div>
          <h3 class="text-lg font-bold text-slate-700 mb-1">Belum Ada Santri</h3>
          <p class="text-sm text-slate-400">Belum ada santri terdaftar di kelas ini untuk tahun ajaran aktif.</p>
          <a href="penilaian_mapel" class="btn btn-secondary mt-4 btn-sm"><i class="ri-arrow-left-line"></i> Kembali</a>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<script>
function getNilaiLabel(v) {
  if (v === '' || isNaN(v)) return '';
  v = Math.min(100, Math.max(0, parseInt(v)));
  if (v >= 90) return 'Amat Baik';
  if (v >= 80) return 'Baik';
  if (v >= 70) return 'Cukup';
  if (v >= 60) return 'Kurang';
  return 'Sangat Kurang';
}
function convertNilai(inp, id) {
  if (inp.value !== '') inp.value = Math.min(100, Math.max(0, parseInt(inp.value)));
  const el = document.getElementById(id);
  if (el) el.value = getNilaiLabel(inp.value);
}
function convertNilaiMobile(inp, id) {
  if (inp.value !== '') inp.value = Math.min(100, Math.max(0, parseInt(inp.value)));
  const el = document.getElementById(id);
  if (el) el.textContent = getNilaiLabel(inp.value);
}
document.getElementById('btn-fill-all')?.addEventListener('click', function() {
  const val = prompt('Isi semua nilai kosong dengan angka (0-100):', '80');
  if (val === null || val === '') return;
  const n = Math.min(100, Math.max(0, parseInt(val)));
  if (isNaN(n)) return;
  document.querySelectorAll('.input-nilai, .input-nilai-mobile').forEach(function(inp) {
    if (inp.value === '') { inp.value = n; inp.dispatchEvent(new Event('input')); }
  });
});

// ── Auto-Jump Feature ───────────────────────────────────────────────────────
(function() {
  const chk    = document.getElementById('chk-auto-jump');
  const track  = document.getElementById('auto-jump-track');
  const thumb  = document.getElementById('auto-jump-thumb');
  const label  = document.getElementById('auto-jump-label');
  if (!chk) return;

  // Restore + render toggle state
  function setToggle(on) {
    chk.checked = on;
    if (on) {
      track.style.backgroundColor = '#6366f1'; // indigo-500
      track.style.borderColor     = '#6366f1';
      thumb.style.transform       = 'translateX(16px)';
      label.style.color           = '#4338ca'; // indigo-700
    } else {
      track.style.backgroundColor = '#e2e8f0'; // slate-200
      track.style.borderColor     = '#cbd5e1'; // slate-300
      thumb.style.transform       = 'translateX(0)';
      label.style.color           = '#64748b'; // slate-500
    }
    localStorage.setItem('nilai_auto_jump', on ? '1' : '0');
  }

  const saved = localStorage.getItem('nilai_auto_jump');
  setToggle(saved === null ? true : saved === '1'); // default ON

  // clicking label triggers checkbox change — intercept here
  chk.addEventListener('change', function() { setToggle(this.checked); });

  function isEnabled() { return chk.checked; }

  // Collect visible input fields in DOM order
  function getInputs() {
    const isMobile = window.matchMedia('(max-width: 639px)').matches;
    return Array.from(document.querySelectorAll(isMobile ? '.input-nilai-mobile' : '.input-nilai'));
  }

  function jumpToNext(current) {
    const inputs = getInputs();
    const idx = inputs.indexOf(current);
    if (idx >= 0 && idx < inputs.length - 1) {
      const next = inputs[idx + 1];
      next.focus();
      // select all text so next keystroke overwrites existing value
      next.select();
      // scroll into view for mobile
      next.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  let jumpTimer = null;

  // Enter key → jump (desktop & some mobile keyboards)
  document.addEventListener('keydown', function(e) {
    if (!isEnabled()) return;
    const t = e.target;
    if (!t.classList.contains('input-nilai') && !t.classList.contains('input-nilai-mobile')) return;
    if (e.key === 'Enter') {
      e.preventDefault();
      clearTimeout(jumpTimer);
      jumpToNext(t);
    }
  }, true);

  // Auto-jump after 2+ digit input with short delay
  // Works on HP keyboard where Enter may not fire reliably
  document.addEventListener('input', function(e) {
    if (!isEnabled()) return;
    const t = e.target;
    if (!t.classList.contains('input-nilai') && !t.classList.contains('input-nilai-mobile')) return;
    const v = t.value.replace(/\D/g, ''); // digits only
    const n = parseInt(v);
    // Jump when: value has 3 chars, OR value = 100, OR value 10-99 after 800ms idle
    clearTimeout(jumpTimer);
    if (v.length === 3 || n === 100) {
      jumpTimer = setTimeout(function() { jumpToNext(t); }, 300);
    } else if (v.length === 2 && n >= 10 && n <= 99) {
      jumpTimer = setTimeout(function() { jumpToNext(t); }, 900);
    }
  });
})();
// ────────────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.input-nilai').forEach(function(inp) { if (inp.value) inp.dispatchEvent(new Event('input')); });
  document.querySelectorAll('.input-nilai-mobile').forEach(function(inp) { if (inp.value) inp.dispatchEvent(new Event('input')); });
});
</script>

<?php include 'include/footer.php'; ?>
