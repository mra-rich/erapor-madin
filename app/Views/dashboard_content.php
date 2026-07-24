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

<div class="page-shell">
  <div class="page-inner">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8">
      <div>
        <h1 class="page-title">Ringkasan Dashboard</h1>
        <p class="page-subtitle">Selamat datang kembali, <?php echo $_SESSION['nama']; ?>!</p>
      </div>
      <div class="mt-4 md:mt-0 flex items-center bg-white px-4 py-2 rounded-lg border border-slate-200">
        <i class="ri-calendar-line text-emerald-600 mr-2 text-lg"></i>
        <div class="text-sm font-semibold text-slate-600">
          <?php
          $dayList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
          $monthList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
          echo $dayList[date('w')] . ', ' . date('j') . ' ' . $monthList[date('n') - 1] . ' ' . date('Y');
          ?>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-5 mb-8">

      <!-- Card Siswa -->
      <a href="data_santri" class="stat-card stat-card-blue flex items-center justify-between gap-3 group min-w-0">
        <div>
          <p class="stat-card-label">Total Siswa</p>
          <h3 class="stat-card-value"><?= $siswa ?></h3>
        </div>
        <div class="stat-card-icon">
          <i class="ri-group-line text-2xl"></i>
        </div>
      </a>

      <!-- Card Kelas -->
      <a href="data_kelas" class="stat-card stat-card-emerald flex items-center justify-between gap-3 group min-w-0">
        <div><p class="stat-card-label">Total Kelas</p><h3 class="stat-card-value"><?= $kelas ?></h3></div>
        <div class="stat-card-icon"><i class="ri-building-4-line text-2xl"></i></div>
      </a>

      <!-- Card Rapot Tahun Ini -->
      <div class="stat-card stat-card-orange flex items-center justify-between gap-3 min-w-0">
        <div><p class="stat-card-label">Rapor (Tahun Ini)</p><h3 class="stat-card-value"><?= $rapot_tahun ?></h3></div>
        <div class="stat-card-icon"><i class="ri-file-list-3-line text-2xl"></i></div>
      </div>

      <!-- Card Semua Rapot -->
      <div class="stat-card stat-card-rose flex items-center justify-between gap-3 min-w-0">
        <div><p class="stat-card-label">Total Rapor</p><h3 class="stat-card-value"><?= $rapot_semua ?></h3></div>
        <div class="stat-card-icon"><i class="ri-book-read-line text-2xl"></i></div>
      </div>

      <!-- Card Wali Kelas -->
      <div class="stat-card stat-card-violet flex items-center justify-between gap-3 min-w-0">
        <div><p class="stat-card-label">Wali Kelas</p><h3 class="stat-card-value"><?= $wali_kelas ?></h3></div>
        <div class="stat-card-icon"><i class="ri-user-star-line text-2xl"></i></div>
      </div>

    </div>

    <!-- Recent Activities Section -->
    <div class="ui-card overflow-hidden">
        <div class="ui-card-header">
            <h2 class="text-lg font-bold text-slate-900">Aktivitas Terbaru</h2>
            <a href="log_aktivitas" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700">Lihat Semua &rarr;</a>
        </div>
        <div class="hidden sm:block overflow-x-auto">
            <table class="ui-table min-w-[680px]">
                <thead>
                    <tr>
                        <th scope="col">Waktu</th>
                        <th scope="col">Pengguna</th>
                        <th scope="col">Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_log = "SELECT l.*, p.nama, p.peran
                                  FROM log_aktivitas l
                                  LEFT JOIN pengguna p ON l.id_pengguna = p.id_pengguna
                                  ORDER BY l.timestamp DESC LIMIT 5";
                    $res_log = mysqli_query($koneksi, $query_log);
                    $activityLogs = [];
                    if ($res_log && mysqli_num_rows($res_log) > 0) {
                        while ($log = mysqli_fetch_assoc($res_log)) {
                            $activityLogs[] = $log;
                            echo '<tr>';
                            echo '<td class="whitespace-nowrap">' . date('d M Y H:i', strtotime($log['timestamp'])) . '</td>';
                            $aktor = $log['nama'] ? htmlspecialchars($log['nama']) . ' (' . htmlspecialchars($log['peran']) . ')' : 'Sistem';
                            echo '<td>' . $aktor . '</td>';
                            echo '<td><span class="font-medium text-slate-700">' . htmlspecialchars($log['aksi']) . '</span>: ' . htmlspecialchars($log['detail']) . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="3" class="text-center text-slate-500 py-8">Belum ada aktivitas.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="space-y-3 p-4 sm:hidden">
                <?php if (!empty($activityLogs)): ?>
                    <?php foreach ($activityLogs as $log): ?>
                        <article class="activity-card">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-800"><?= htmlspecialchars($log['aksi']) ?></p>
                                    <p class="mt-1 text-sm text-slate-600"><?= htmlspecialchars($log['detail']) ?></p>
                                </div>
                                <time class="shrink-0 text-xs text-slate-400"><?= date('d M H:i', strtotime($log['timestamp'])) ?></time>
                            </div>
                            <p class="mt-3 text-xs font-medium text-emerald-700">
                                <?= $log['nama'] ? htmlspecialchars($log['nama']) . ' (' . htmlspecialchars($log['peran']) . ')' : 'Sistem' ?>
                            </p>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ui-empty-state p-6"><p class="text-sm text-slate-500">Belum ada aktivitas.</p></div>
                <?php endif; ?>
            </div>
        </div>

  </div>
</div>

<?php if (isset($total_alerts) && $total_alerts > 0): ?>
<div id="toast-alert" class="static mt-4 mx-4 sm:fixed sm:mt-0 sm:mx-0 sm:top-24 sm:right-5 sm:left-auto sm:z-50 flex items-center w-auto sm:w-full max-w-xs p-4 space-x-3 text-slate-500 bg-white rounded-xl shadow-lg border-l-4 border-amber-500 sm:transform sm:translate-x-full transition-transform duration-500 ease-out" role="alert">
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
        if (toast) toast.classList.remove('sm:translate-x-full');
    }, 500);
    setTimeout(closeToast, 6000);
    function closeToast() {
        const toast = document.getElementById('toast-alert');
        if (toast) {
            toast.classList.add('sm:translate-x-full');
            setTimeout(() => toast.remove(), 500);
        }
    }
</script>
<?php endif; ?>
