<?php
/**
 * Dashboard Content View (Pure Template)
 * Variabel disediakan oleh DashboardController:
 * $siswa, $kelas, $rapotTahun, $rapotSemua, $waliKelas, $total_alerts, $koneksi
 *
 * Variabel ini juga kompatibel dengan dashboard.php legacy yang menggunakan
 * nama variabel berbeda — kita alias di sini.
 */
$rapot_tahun = $rapotTahun ?? $rapot_tahun ?? 0;
$rapot_semua = $rapotSemua ?? $rapot_semua ?? 0;
$wali_kelas = $waliKelas ?? $wali_kelas ?? 0;
?>

<div class="p-4 sm:ml-64 bg-slate-50 min-h-screen">
  <div class="p-4 mt-16 max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 bg-white p-6 rounded-2xl shadow-sm border border-slate-100 premium-card">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 brand-font">Ringkasan Dashboard</h1>
        <p class="text-sm font-medium text-slate-500 mt-1">Selamat datang kembali, <?php echo $_SESSION['nama']; ?>!</p>
      </div>
      <div class="mt-4 md:mt-0 flex items-center bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-100">
        <i class="ri-calendar-line text-emerald-600 mr-2 text-lg"></i>
        <div class="text-sm font-semibold text-emerald-700">
          <?php
          $dayList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
          $monthList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
          echo $dayList[date('w')] . ', ' . date('j') . ' ' . $monthList[date('n') - 1] . ' ' . date('Y');
          ?>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
      
      <!-- Card Siswa -->
      <a href="data_santri.php" class="block bg-blue-500 rounded-2xl p-6 shadow-lg shadow-blue-500/30 premium-card relative overflow-hidden group cursor-pointer hover:-translate-y-1 transition-all duration-300">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="flex justify-between items-start relative z-10">
          <div>
            <p class="text-blue-100 text-sm font-medium mb-1">Total Siswa</p>
            <h3 class="text-4xl font-bold text-white brand-font"><?= $siswa ?></h3>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
            <i class="ri-group-line text-2xl text-white"></i>
          </div>
        </div>
      </a>

      <!-- Card Kelas -->
      <a href="data_kelas.php" class="block bg-emerald-500 rounded-2xl p-6 shadow-lg shadow-emerald-500/30 premium-card relative overflow-hidden group cursor-pointer hover:-translate-y-1 transition-all duration-300">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="flex justify-between items-start relative z-10">
          <div>
            <p class="text-green-100 text-sm font-medium mb-1">Total Kelas</p>
            <h3 class="text-4xl font-bold text-white brand-font"><?= $kelas ?></h3>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
            <i class="ri-building-4-line text-2xl text-white"></i>
          </div>
        </div>
      </a>

      <!-- Card Rapot Tahun Ini -->
      <div class="bg-orange-400 rounded-2xl p-6 shadow-lg shadow-orange-500/30 premium-card relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="flex justify-between items-start relative z-10">
          <div>
            <p class="text-yellow-100 text-sm font-medium mb-1">Rapor (Tahun Ini)</p>
            <h3 class="text-4xl font-bold text-white brand-font"><?= $rapot_tahun ?></h3>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
            <i class="ri-file-list-3-line text-2xl text-white"></i>
          </div>
        </div>
      </div>

      <!-- Card Semua Rapot -->
      <div class="bg-rose-500 rounded-2xl p-6 shadow-lg shadow-rose-500/30 premium-card relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="flex justify-between items-start relative z-10">
          <div>
            <p class="text-red-100 text-sm font-medium mb-1">Total Rapor</p>
            <h3 class="text-4xl font-bold text-white brand-font"><?= $rapot_semua ?></h3>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
            <i class="ri-book-read-line text-2xl text-white"></i>
          </div>
        </div>
      </div>

      <!-- Card Wali Kelas -->
      <div class="bg-violet-500 rounded-2xl p-6 shadow-lg shadow-violet-500/30 premium-card relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="flex justify-between items-start relative z-10">
          <div>
            <p class="text-purple-100 text-sm font-medium mb-1">Wali Kelas</p>
            <h3 class="text-4xl font-bold text-white brand-font"><?= $wali_kelas ?></h3>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
            <i class="ri-user-star-line text-2xl text-white"></i>
          </div>
        </div>
      </div>

    </div>

    <!-- Recent Activities Section -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 premium-card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-slate-800 brand-font">Aktivitas Terbaru</h2>
            <a href="log_aktivitas.php" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Lihat Semua &rarr;</a>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600 border-collapse border border-slate-300">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                        <tr>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300">Waktu</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300">Pengguna</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300">Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_log = "SELECT l.*, p.nama, p.peran 
                                      FROM log_aktivitas l 
                                      LEFT JOIN pengguna p ON l.id_pengguna = p.id_pengguna 
                                      ORDER BY l.timestamp DESC LIMIT 5";
                        $res_log = mysqli_query($koneksi, $query_log);
                        if ($res_log && mysqli_num_rows($res_log) > 0) {
                            while ($log = mysqli_fetch_assoc($res_log)) {
                                echo '<tr class="hover:bg-slate-50 transition-colors group">';
                                echo '<td class="py-2 px-6 border border-slate-300 whitespace-nowrap">' . date('d M Y H:i', strtotime($log['timestamp'])) . '</td>';
                                $aktor = $log['nama'] ? htmlspecialchars($log['nama']) . ' (' . htmlspecialchars($log['peran']) . ')' : 'Sistem';
                                echo '<td class="py-2 px-6 border border-slate-300">' . $aktor . '</td>';
                                echo '<td class="py-2 px-6 border border-slate-300"><span class="font-medium text-slate-700">' . htmlspecialchars($log['aksi']) . '</span>: ' . htmlspecialchars($log['detail']) . '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="3" class="py-2 px-6 text-center text-slate-500 border border-slate-300">Belum ada aktivitas.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

  </div>
</div>

<?php if (isset($total_alerts) && $total_alerts > 0): ?>
<div id="toast-alert" class="fixed top-24 right-5 z-50 flex items-center w-full max-w-xs p-4 space-x-3 text-slate-500 bg-white rounded-2xl shadow-2xl border-l-4 border-orange-500 transform translate-x-full transition-transform duration-500 ease-out" role="alert">
    <div class="inline-flex items-center justify-center flex-shrink-0 w-10 h-10 text-orange-500 bg-orange-50 rounded-xl">
        <i class="ri-error-warning-fill text-2xl"></i>
    </div>
    <div class="ms-3 text-sm font-normal text-slate-600">
        <span class="font-bold text-slate-800 text-base">Perhatian!</span><br>
        Terdapat <b><?= $total_alerts ?> kelas</b> yang belum lengkap nilainya.
    </div>
    <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-slate-400 hover:text-slate-900 rounded-lg focus:ring-2 focus:ring-slate-300 p-1.5 hover:bg-slate-100 inline-flex items-center justify-center h-8 w-8 transition-colors" onclick="closeToast()" aria-label="Close">
        <i class="ri-close-line text-xl"></i>
    </button>
</div>
<script>
    setTimeout(function() {
        const toast = document.getElementById('toast-alert');
        if (toast) toast.classList.remove('translate-x-full');
    }, 500);
    setTimeout(closeToast, 6000);
    function closeToast() {
        const toast = document.getElementById('toast-alert');
        if (toast) {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 500);
        }
    }
</script>
<?php endif; ?>
