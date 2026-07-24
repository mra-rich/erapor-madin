<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_VIEW_REPORTS);

// Jika Wali Kelas, ambil kelas sendiri untuk auto-select
$wali_kelas_id = null;
if ($_SESSION['peran'] === 'Wali Kelas') {
    $id_pengguna_wali = $_SESSION['id_pengguna'];
    $q_wali = mysqli_query($koneksi, "SELECT id_kelas FROM kelas WHERE id_wali_kelas = '$id_pengguna_wali' LIMIT 1");
    if ($q_wali && $row_wali = mysqli_fetch_assoc($q_wali)) {
        $wali_kelas_id = $row_wali['id_kelas'];
    }
}

// Ambil semester aktif dari pengaturan
$q_peng = mysqli_query($koneksi, "SELECT semester FROM pengaturan LIMIT 1");
$peng_aktif = mysqli_fetch_assoc($q_peng);
$semester_aktif_rapot = intval($peng_aktif['semester'] ?? 1);

include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="page-shell">
  <div class="page-inner">

    <!-- Page Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="page-title">Cetak Dokumen & Rapor</h1>
        <p class="page-subtitle">Kelola dan cetak sampul, biodata, rapor, serta leger nilai santri.</p>
      </div>
    </div>

    <!-- Filter Card -->
    <div class="ui-card ui-card-body mb-6">
      <form id="formPencarian" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
        <?php if ($_SESSION['peran'] !== 'Wali Kelas'): ?>
          <div class="sm:col-span-2">
            <?php 
            $no_autosubmit = true;
            $id_kelas_selected = isset($id_kelas) ? $id_kelas : (isset($kelas_aktif) ? $kelas_aktif : 0); 
            include 'include/filter_kelas.php'; 
            ?>
          </div>
        <?php else: ?>
          <input type="hidden" name="kelas" id="kelas" value="<?= $wali_kelas_id ?>">
          <div class="sm:col-span-2">
            <label class="ui-label">Wali Kelas</label>
            <input type="text" class="ui-input bg-slate-50 cursor-not-allowed font-medium text-slate-600" value="Kelas Anda" readonly>
          </div>
        <?php endif; ?>
        
        <div>
          <label class="ui-label">Semester</label>
          <select name="semester" id="semester" class="ui-select cursor-pointer font-semibold">
            <option value="1">Ganjil</option>
            <option value="2">Genap</option>
          </select>
        </div>
      </form>
    </div>

    <!-- Empty State -->
    <div id="empty-state" class="ui-empty-state">
      <div class="ui-empty-icon"><i class="ri-search-eye-line text-2xl"></i></div>
      <h3 class="text-lg font-bold text-slate-700 mb-1">Pilih Kelas &amp; Semester</h3>
      <p class="text-sm text-slate-400 max-w-sm">Silakan pilih kelas dan semester di atas untuk memuat daftar santri.</p>
    </div>

    <!-- Tabel & Card Container -->
    <div id="tabel-container" class="hidden space-y-4">
      
      <!-- Toolbar / Action Bar -->
      <div class="ui-card ui-card-body py-4 flex flex-col md:flex-row gap-4 items-center justify-between">
        <!-- Search -->
        <div class="relative w-full md:w-72">
          <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <input type="text" id="searchInput" class="ui-input pl-9 py-2" placeholder="Cari santri...">
        </div>

        <!-- Print All Actions -->
        <div class="flex flex-wrap items-center gap-1.5 w-full md:w-auto justify-end">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wide mr-1 select-none w-full md:w-auto mb-1 md:mb-0"><i class="ri-printer-line"></i> Cetak Kelas:</span>
          
          <button onclick="bukaCetakKelas('cetak_sampul_kelas.php')" class="btn btn-secondary btn-sm" title="Cetak Semua Sampul">
            <i class="ri-book-line"></i> Sampul
          </button>
          <button onclick="bukaCetakKelas('cetak_biodata_kelas.php')" class="btn btn-secondary btn-sm text-amber-600 border-amber-100 bg-amber-50 hover:bg-amber-100" title="Cetak Semua Identitas">
            <i class="ri-user-line"></i> Identitas
          </button>
          <button onclick="bukaCetakKelas('preview_rapot_kelas.php')" class="btn btn-secondary btn-sm text-emerald-600 border-emerald-100 bg-emerald-50 hover:bg-emerald-100" title="Cetak Semua Rapor">
            <i class="ri-file-text-line"></i> Rapor
          </button>
          <button onclick="bukaCetakKelas('preview_leger.php')" class="btn btn-secondary btn-sm" title="Preview Leger Nilai">
            <i class="ri-table-2"></i> Leger
          </button>
          <button onclick="bukaCetakKelas('cetak_semua_kelas.php')" class="btn btn-primary btn-sm" title="Cetak Seluruh Laporan Sekaligus">
            <i class="ri-printer-line"></i> Semua
          </button>
        </div>
      </div>

      <!-- DESKTOP VIEW (sm+) -->
      <div class="hidden sm:block table-scroll-wrap">
        <table class="ui-table">
          <thead>
            <tr>
              <th class="w-12 text-center">No</th>
              <th>NIS / NISN</th>
              <th>Nama Santri</th>
              <th>Tempat, Tgl Lahir</th>
              <?php if ($semester_aktif_rapot == 2): ?>
              <th class="w-36 text-center">Kenaikan</th>
              <?php endif; ?>
              <th class="text-center w-80">Aksi Dokumen</th>
            </tr>
          </thead>
          <tbody id="tbody-siswa">
            <!-- Rendered by JS -->
          </tbody>
        </table>
      </div>

      <!-- MOBILE VIEW (below sm) -->
      <div class="sm:hidden space-y-2" id="mobile-card-list">
        <!-- Rendered by JS -->
      </div>

    </div>

  </div>
</div>

<script>
    async function tampilkanSiswa() {
        const selectKelas = document.querySelector('select[name="kelas"]');
        const hiddenKelas = document.getElementById('kelas');
        const kelas = selectKelas ? selectKelas.value : (hiddenKelas ? hiddenKelas.value : '');
        const semester = document.getElementById('semester').value;

        if (!kelas) {
            alert('Silakan pilih kelas terlebih dahulu!');
            return;
        }

        const tbody = document.getElementById('tbody-siswa');
        const mobList = document.getElementById('mobile-card-list');
        
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-12 text-slate-400"><i class="ri-loader-4-line animate-spin text-3xl block mb-2 text-emerald-500"></i>Memuat data santri...</td></tr>';
        mobList.innerHTML = '<div class="text-center py-12 text-slate-400"><i class="ri-loader-4-line animate-spin text-3xl block mb-2 text-emerald-500"></i>Memuat data santri...</div>';

        document.getElementById('empty-state').classList.add('hidden');
        document.getElementById('tabel-container').classList.remove('hidden');

        try {
            const response = await fetch('get_siswa_rapot.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'kelas=' + encodeURIComponent(kelas)
            });

            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            
            if (data.status === 'success') {
                tbody.innerHTML = '';
                mobList.innerHTML = '';
                
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-12 text-slate-400"><i class="ri-team-line text-2xl block mb-1"></i>Tidak ada santri di kelas ini.</td></tr>';
                    mobList.innerHTML = '<div class="ui-empty-state"><i class="ri-team-line text-2xl mb-1 text-slate-300"></i><p class="text-slate-400 text-sm">Tidak ada santri di kelas ini.</p></div>';
                    return;
                }

                data.data.forEach((siswa, index) => {
                    // 1. Render Desktop Table Row
                    const tr = document.createElement('tr');
                    const hasKenaikanHeader = <?= $semester_aktif_rapot == 2 ? 'true' : 'false' ?>;
                    
                    let kenaikanCol = '';
                    if (hasKenaikanHeader) {
                        const kStatus = siswa.status_kenaikan || 'Belum Diatur';
                        let badge = 'badge-neutral';
                        if (kStatus === 'Naik') badge = 'badge-success';
                        if (kStatus === 'Tidak') badge = 'badge-danger';
                        kenaikanCol = `<td class="text-center"><span class="badge ${badge}">${kStatus === 'Tidak' ? 'Tidak Naik' : kStatus}</span></td>`;
                    }

                    tr.innerHTML = `
                        <td class="text-center text-slate-400 text-xs">${index + 1}</td>
                        <td class="font-mono text-xs text-slate-500">${siswa.nis} <span class="text-slate-300">/</span> ${siswa.nisn}</td>
                        <td><p class="font-semibold text-slate-800 uppercase">${siswa.nama}</p></td>
                        <td class="text-slate-500 text-xs">${siswa.tempat_lahir}, ${siswa.tanggal_lahir}</td>
                        ${kenaikanCol}
                        <td>
                            <div class="flex justify-center gap-1.5">
                                <button onclick="bukaCetak('cetak_sampul.php', ${siswa.id_siswa})" class="btn btn-secondary btn-sm px-2.5 py-1.5 text-xs">
                                    <i class="ri-book-line"></i> Sampul
                                </button>
                                <button onclick="bukaCetak('cetak_biodata.php', ${siswa.id_siswa})" class="btn btn-secondary btn-sm px-2.5 py-1.5 text-xs text-amber-700 border-amber-200 bg-amber-50 hover:bg-amber-100">
                                    <i class="ri-user-line"></i> Identitas
                                </button>
                                <button onclick="bukaCetak('preview_rapot.php', ${siswa.id_siswa})" class="btn btn-secondary btn-sm px-2.5 py-1.5 text-xs text-emerald-700 border-emerald-200 bg-emerald-50 hover:bg-emerald-100">
                                    <i class="ri-file-text-line"></i> Rapor
                                </button>
                                <button onclick="bukaCetak('cetak_semua.php', ${siswa.id_siswa})" class="btn btn-primary btn-sm px-3 py-1.5 text-xs">
                                    <i class="ri-printer-line"></i> Semua
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);

                    // 2. Render Mobile Card
                    const card = document.createElement('div');
                    card.className = 'ui-card px-4 py-3 space-y-3';
                    
                    const inisial = siswa.nama.charAt(0).toUpperCase();
                    const colors = ['bg-emerald-100 text-emerald-700','bg-blue-100 text-blue-700','bg-violet-100 text-violet-700','bg-amber-100 text-amber-700','bg-rose-100 text-rose-700'];
                    const color = colors[inisial.charCodeAt(0) % 5];
                    
                    let kBadgeMobile = '';
                    if (hasKenaikanHeader && siswa.status_kenaikan) {
                        let badge = 'badge-neutral';
                        if (siswa.status_kenaikan === 'Naik') badge = 'badge-success';
                        if (siswa.status_kenaikan === 'Tidak') badge = 'badge-danger';
                        kBadgeMobile = `<span class="badge ${badge} text-[10px]">${siswa.status_kenaikan === 'Tidak' ? 'Tidak Naik' : siswa.status_kenaikan}</span>`;
                    }

                    card.innerHTML = `
                        <div class="flex items-center gap-3">
                            <div class="shrink-0 w-9 h-9 rounded-full ${color} flex items-center justify-center text-xs font-bold">${inisial}</div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-800 text-sm truncate uppercase">${siswa.nama}</p>
                                <p class="text-[10px] text-slate-400 font-mono mt-0.5">${siswa.nis} / ${siswa.nisn}</p>
                            </div>
                            ${kBadgeMobile}
                        </div>
                        <div class="pt-2 border-t border-slate-100 grid grid-cols-2 gap-2">
                            <button onclick="bukaCetak('cetak_sampul.php', ${siswa.id_siswa})" class="btn btn-secondary btn-sm py-2 text-xs flex justify-center"><i class="ri-book-line"></i> Sampul</button>
                            <button onclick="bukaCetak('cetak_biodata.php', ${siswa.id_siswa})" class="btn btn-secondary btn-sm py-2 text-xs text-amber-700 border-amber-200 bg-amber-50 hover:bg-amber-100 flex justify-center"><i class="ri-user-line"></i> Identitas</button>
                            <button onclick="bukaCetak('preview_rapot.php', ${siswa.id_siswa})" class="btn btn-secondary btn-sm py-2 text-xs text-emerald-700 border-emerald-200 bg-emerald-50 hover:bg-emerald-100 flex justify-center"><i class="ri-file-text-line"></i> Rapor</button>
                            <button onclick="bukaCetak('cetak_semua.php', ${siswa.id_siswa})" class="btn btn-primary btn-sm py-2 text-xs flex justify-center"><i class="ri-printer-line"></i> Semua</button>
                        </div>
                    `;
                    mobList.appendChild(card);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-6 text-red-500"><i class="ri-error-warning-line mr-2"></i> Error: ${data.error || 'Gagal memuat data'}</td></tr>`;
                mobList.innerHTML = `<div class="ui-card p-6 text-center text-red-500"><i class="ri-error-warning-line mr-2"></i> Error: ${data.error || 'Gagal memuat data'}</div>`;
            }
        } catch (error) {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-6 text-red-500"><i class="ri-error-warning-line mr-2"></i> Terjadi kesalahan jaringan.</td></tr>';
            mobList.innerHTML = '<div class="ui-card p-6 text-center text-red-500"><i class="ri-error-warning-line mr-2"></i> Terjadi kesalahan jaringan.</div>';
        }
    }

    function bukaCetak(url, idSiswa) {
        const semester = document.getElementById('semester').value;
        let fullUrl = url + '?id=' + idSiswa;
        if (semester) fullUrl += '&smt=' + semester;
        window.open(fullUrl, '_blank', 'width=900,height=600');
    }

    function bukaCetakKelas(url) {
        const selectKelas = document.querySelector('select[name="kelas"]');
        const hiddenKelas = document.getElementById('kelas');
        const kelas = selectKelas ? selectKelas.value : (hiddenKelas ? hiddenKelas.value : '');
        const semester = document.getElementById('semester').value;

        if (!kelas) {
            alert('Silakan pilih kelas terlebih dahulu!');
            return;
        }
        
        let fileAsli = url.replace('_kelas', '');
        let fullUrl = fileAsli + '?kelas=' + kelas;
        if (semester) fullUrl += '&smt=' + semester;
        window.open(fullUrl, '_blank', 'width=900,height=600');
    }

    // Live Search
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        
        // Filter Desktop Table Rows
        const rows = document.querySelectorAll('#tbody-siswa tr');
        rows.forEach(row => {
            if(row.querySelector('td').colSpan > 1) return;
            const nama = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
            const nis = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            row.style.display = (nama.includes(searchValue) || nis.includes(searchValue)) ? '' : 'none';
        });

        // Filter Mobile Cards
        const cards = document.querySelectorAll('#mobile-card-list > div');
        cards.forEach(card => {
            const nama = card.querySelector('p.font-semibold')?.textContent.toLowerCase() || '';
            const nis = card.querySelector('p.text-slate-400')?.textContent.toLowerCase() || '';
            card.style.display = (nama.includes(searchValue) || nis.includes(searchValue)) ? '' : 'none';
        });
    });

    // Auto-load & Event Listeners
    (function() {
        const selectKelas = document.querySelector('select[name="kelas"]');
        const hiddenKelas = document.getElementById('kelas');
        const selectSemester = document.getElementById('semester');
        const kelasVal = selectKelas ? selectKelas.value : (hiddenKelas ? hiddenKelas.value : '');
        
        if (kelasVal) setTimeout(tampilkanSiswa, 100);

        if (selectKelas) selectKelas.addEventListener('change', tampilkanSiswa);
        if (selectSemester) selectSemester.addEventListener('change', tampilkanSiswa);
    })();
</script>

<?php include 'include/footer.php'; ?>
