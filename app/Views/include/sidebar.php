<aside id="logo-sidebar" hx-indicator="#page-loader" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-slate-100 sm:translate-x-0 shadow-[4px_0_24px_rgba(0,0,0,0.02)]" aria-label="Sidebar">
  <div class="h-full px-4 pb-4 overflow-y-auto bg-white">
    <div class="mb-4 px-2 text-xs font-semibold text-slate-400 uppercase tracking-wider mt-4">Menu Utama</div>
    <ul class="space-y-1.5 font-medium">
      
      <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>

      <li>
        <a href="dashboard" class="flex items-center p-3 rounded-xl transition-all duration-200 group <?php echo ($currentPage == 'dashboard.php') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-600'; ?>">
          <i class="ri-dashboard-fill text-xl <?php echo ($currentPage == 'dashboard.php') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'; ?> transition duration-200"></i>
          <span class="flex-1 ms-3 whitespace-nowrap font-semibold">Dashboard</span>
        </a>
      </li>

      <?php if (in_array($_SESSION['peran'], ['Admin', 'Kepala Madrasah', 'Wali Kelas'])): ?>
      <li>
        <a href="data_santri" class="flex items-center p-3 rounded-xl transition-all duration-200 group <?php echo ($currentPage == 'data_santri.php') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-600'; ?>">
          <i class="ri-team-fill text-xl <?php echo ($currentPage == 'data_santri.php') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'; ?> transition duration-200"></i>
          <span class="flex-1 ms-3 whitespace-nowrap font-semibold">Data Santri</span>
        </a>
      </li>

      <?php if ($_SESSION['peran'] === 'Admin'): ?>
      <li>
        <a href="data_arsip_santri" class="flex items-center p-3 rounded-xl transition-all duration-200 group <?php echo ($currentPage == 'data_arsip_santri.php') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-600'; ?>">
          <i class="ri-book-read-fill text-xl <?php echo ($currentPage == 'data_arsip_santri.php') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'; ?> transition duration-200"></i>
          <span class="flex-1 ms-3 whitespace-nowrap font-semibold">Buku Induk & Arsip</span>
        </a>
      </li>
      <?php endif; ?>

      <li>
        <a href="data_nilai" class="flex items-center p-3 rounded-xl transition-all duration-200 group <?php echo ($currentPage == 'data_nilai.php') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-600'; ?>">
          <i class="ri-file-chart-fill text-xl <?php echo ($currentPage == 'data_nilai.php') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'; ?> transition duration-200"></i>
          <span class="flex-1 ms-3 whitespace-nowrap font-semibold">Data Nilai</span>
        </a>
      </li>
      <?php endif; ?>

      <li>
        <a href="penilaian_mapel" class="flex items-center p-3 rounded-xl transition-all duration-200 group <?php echo ($currentPage == 'penilaian_mapel.php') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-600'; ?>">
          <i class="ri-edit-box-fill text-xl <?php echo ($currentPage == 'penilaian_mapel.php') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'; ?> transition duration-200"></i>
          <span class="flex-1 ms-3 whitespace-nowrap font-semibold">Penilaian Mapel</span>
        </a>
      </li>

      <?php if (in_array($_SESSION['peran'], ['Admin', 'Wali Kelas'])): ?>
      <li>
        <a href="evaluasi_wali" class="flex items-center p-3 rounded-xl transition-all duration-200 group <?php echo ($currentPage == 'evaluasi_wali.php') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-600'; ?>">
          <i class="ri-survey-fill text-xl <?php echo ($currentPage == 'evaluasi_wali.php') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'; ?> transition duration-200"></i>
          <span class="flex-1 ms-3 whitespace-nowrap font-semibold">Evaluasi Kelas</span>
        </a>
      </li>
      <?php endif; ?>

      <div class="my-4 border-t border-slate-100"></div>

      <?php if (in_array($_SESSION['peran'], ['Admin', 'Kepala Madrasah'])): ?>
      <div class="mb-4 px-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Master Data</div>

      <li>
        <a href="identitas_madrasah" class="flex items-center p-3 rounded-xl transition-all duration-200 group <?php echo ($currentPage == 'identitas_madrasah.php') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-600'; ?>">
          <i class="ri-bank-fill text-xl <?php echo ($currentPage == 'identitas_madrasah.php') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'; ?> transition duration-200"></i>
          <span class="flex-1 ms-3 whitespace-nowrap font-semibold">Identitas Lembaga</span>
        </a>
      </li>

      <li>
        <a href="data_guru" class="flex items-center p-3 rounded-xl transition-all duration-200 group <?php echo ($currentPage == 'data_guru.php') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-600'; ?>">
          <i class="ri-user-2-fill text-xl <?php echo ($currentPage == 'data_guru.php') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'; ?> transition duration-200"></i>
          <span class="flex-1 ms-3 whitespace-nowrap font-semibold">Data Guru</span>
        </a>
      </li>

      <li>
        <a href="data_kelas" class="flex items-center p-3 rounded-xl transition-all duration-200 group <?php echo ($currentPage == 'data_kelas.php') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-600'; ?>">
          <i class="ri-community-fill text-xl <?php echo ($currentPage == 'data_kelas.php') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'; ?> transition duration-200"></i>
          <span class="flex-1 ms-3 whitespace-nowrap font-semibold">Master Kelas</span>
        </a>
      </li>






      <li>
        <a href="data_mata_pelajaran" class="flex items-center p-3 rounded-xl transition-all duration-200 group <?php echo ($currentPage == 'data_mata_pelajaran.php') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-600'; ?>">
          <i class="ri-book-open-fill text-xl <?php echo ($currentPage == 'data_mata_pelajaran.php') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'; ?> transition duration-200"></i>
          <span class="flex-1 ms-3 whitespace-nowrap font-semibold">Mata Pelajaran</span>
        </a>
      </li>

      <?php endif; ?>

      <?php if (in_array($_SESSION['peran'], ['Admin', 'Kepala Madrasah', 'Wali Kelas'])): ?>
      <div class="my-4 border-t border-slate-100"></div>
      <div class="mb-4 px-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Akademik & Laporan</div>



      <li>
        <a href="kenaikan_kelas" class="flex items-center p-3 rounded-xl transition-all duration-200 group <?php echo ($currentPage == 'kenaikan_kelas.php') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-600'; ?>">
          <i class="ri-arrow-up-circle-fill text-xl <?php echo ($currentPage == 'kenaikan_kelas.php') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'; ?> transition duration-200"></i>
          <span class="flex-1 ms-3 whitespace-nowrap font-semibold">Kenaikan Kelas</span>
        </a>
      </li>

      <li>
        <a href="cetak_rapot" class="flex items-center p-3 rounded-xl transition-all duration-200 group <?php echo ($currentPage == 'cetak_rapot.php') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-600'; ?>">
          <i class="ri-printer-fill text-xl <?php echo ($currentPage == 'cetak_rapot.php') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'; ?> transition duration-200"></i>
          <span class="flex-1 ms-3 whitespace-nowrap font-semibold">Cetak Rapot</span>
        </a>
      </li>

      <?php endif; ?>

      <?php if ($_SESSION['peran'] === 'Admin'): ?>
      <li>
        <a href="log_aktivitas" class="flex items-center p-3 rounded-xl transition-all duration-200 group <?php echo ($currentPage == 'log_aktivitas.php') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-600'; ?>">
          <i class="ri-history-fill text-xl <?php echo ($currentPage == 'log_aktivitas.php') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'; ?> transition duration-200"></i>
          <span class="flex-1 ms-3 whitespace-nowrap font-semibold">Log Aktivitas</span>
        </a>
      </li>
      <?php endif; ?>
    </ul>

    <!-- Quick Help Banner -->
    <div class="mt-8 p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border border-blue-100 relative overflow-hidden group">
        <div class="absolute -right-4 -bottom-4 opacity-10">
            <i class="ri-customer-service-2-fill text-8xl text-blue-600"></i>
        </div>
        <div class="relative z-10">
            <h4 class="text-sm font-bold text-blue-900 mb-1">Butuh Bantuan?</h4>
            <p class="text-xs text-blue-700 mb-3">Hubungi admin jika Anda mengalami kendala.</p>
            <a href="#" class="inline-flex items-center justify-center w-full px-3 py-2 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                Kontak Admin
            </a>
        </div>
    </div>
  </div>
</aside>