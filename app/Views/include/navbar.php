<nav class="fixed top-0 z-50 w-full bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm transition-all duration-300">
  <div class="px-4 py-3 lg:px-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center justify-start rtl:justify-end">
        <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-xl sm:hidden hover:bg-emerald-50 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-colors">
          <span class="sr-only">Buka sidebar</span>
          <i class="ri-menu-2-line text-2xl"></i>
        </button>
        <button onclick="toggleSidebar()" type="button" class="hidden sm:inline-flex items-center p-2 text-sm text-gray-500 rounded-xl hover:bg-emerald-50 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-colors mr-2">
          <span class="sr-only">Toggle sidebar</span>
          <i class="ri-menu-fold-line text-2xl" id="sidebar-toggle-icon"></i>
        </button>
        <a href="index" class="flex ms-2 md:me-24 items-center group">
          <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center mr-3 border border-emerald-100 group-hover:bg-emerald-500 transition-colors duration-300">
             <img src="<?= $assetBase ?? '' ?>assets/img/logo.png" class="h-6 object-contain group-hover:brightness-0 group-hover:invert transition-all" alt="Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3003/3003511.png'" />
          </div>
          <span class="self-center text-xl font-bold sm:text-2xl whitespace-nowrap brand-font text-slate-800">E-Rapor</span>
        </a>
      </div>
      <div class="flex items-center">
        <?php
        // Gunakan NotifikasiModel OOP jika tersedia, fallback ke legacy
        if (!isset($total_alerts)) {
            if (class_exists('App\\Models\\NotifikasiModel')) {
                $notifModel = new \App\Models\NotifikasiModel($koneksi);
                $idWali = null;
                if (isset($_SESSION['peran']) && $_SESSION['peran'] === 'Wali Kelas') {
                    $idWali = (int) $_SESSION['id_pengguna'];
                }
                $alertRows = $notifModel->getAlertKelasKurang($idWali);
                $total_alerts = count($alertRows);
                // Simpan rows agar bisa di-loop di dropdown
                $res_alert_nav_rows = $alertRows;
            } else {
                // Fallback legacy
                $filter_peringatan_nav = "";
                if (isset($_SESSION['peran']) && $_SESSION['peran'] === 'Wali Kelas') {
                    $id_pengguna_wali = $_SESSION['id_pengguna'];
                    $filter_peringatan_nav = "WHERE k.id_wali_kelas = '$id_pengguna_wali'";
                }
                $query_alert_nav = "SELECT k.nama_kelas, 
                                       (SELECT COUNT(*) FROM siswa s WHERE s.id_kelas = k.id_kelas AND s.status = 'Aktif') as total_siswa,
                                       (SELECT COUNT(DISTINCT t.id_siswa) FROM transaksi_raport t JOIN siswa s ON t.id_siswa = s.id_siswa WHERE s.id_kelas = k.id_kelas AND s.status = 'Aktif') as total_dinilai
                                     FROM kelas k
                                     $filter_peringatan_nav
                                     HAVING total_siswa > 0 AND total_dinilai < total_siswa";
                $res_alert_nav = mysqli_query($koneksi, $query_alert_nav);
                $total_alerts = mysqli_num_rows($res_alert_nav);
                $res_alert_nav_rows = null;
            }
        } else {
            $res_alert_nav_rows = $alerts ?? null;
        }
        ?>
        <!-- Full Screen Toggle Button -->
        <div class="relative mr-2">
            <button type="button" onclick="toggleFullScreen()" class="relative p-2 text-slate-400 rounded-full hover:bg-slate-50 hover:text-slate-600 focus:outline-none focus:ring-4 focus:ring-slate-100 transition-colors" title="Toggle Fullscreen">
                <i class="ri-fullscreen-line text-xl" id="fullscreen-icon"></i>
            </button>
        </div>
        
        <!-- Notification Bell -->
        <div class="relative mr-2">
            <button type="button" class="relative p-2 text-slate-400 rounded-full hover:bg-slate-50 hover:text-slate-600 focus:outline-none focus:ring-4 focus:ring-slate-100 transition-colors" data-dropdown-toggle="dropdown-notification">
                <i class="ri-notification-3-line text-xl"></i>
                <?php if ($total_alerts > 0): ?>
                    <div class="absolute inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-500 border border-white rounded-full top-1 right-1">
                        <?= $total_alerts ?>
                    </div>
                <?php endif; ?>
            </button>
            
            <!-- Dropdown Notification -->
            <div class="z-50 hidden my-4 w-72 text-base list-none bg-white divide-y divide-slate-100 rounded-2xl shadow-xl border border-slate-100 animate-fadeIn" id="dropdown-notification">
                <div class="block px-4 py-3 text-sm font-bold text-center text-slate-700 bg-slate-50/50 rounded-t-2xl">
                    Notifikasi Sistem
                </div>
                <div class="divide-y divide-slate-50 max-h-80 overflow-y-auto">
                    <?php if ($total_alerts > 0): 
                        // Tentukan sumber data: array OOP atau mysqli_result legacy
                        $alertItems = $res_alert_nav_rows ?? [];
                        if (empty($alertItems) && isset($res_alert_nav)) {
                            // Legacy mode: konversi result ke array
                            while ($r = mysqli_fetch_assoc($res_alert_nav)) { $alertItems[] = $r; }
                        }
                        foreach ($alertItems as $alert):
                            $kurang = $alert['total_siswa'] - $alert['total_dinilai'];
                            $pesan = ($alert['total_dinilai'] == 0) ? "0 nilai diinput" : "Kurang $kurang siswa";
                        ?>
                        <a href="dashboard" class="flex px-4 py-3 hover:bg-slate-50 transition-colors">
                            <div class="flex-shrink-0 mt-0.5">
                                <div class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center">
                                    <i class="ri-error-warning-fill text-lg"></i>
                                </div>
                            </div>
                            <div class="w-full ps-3">
                                <div class="text-slate-600 text-sm mb-0.5"><span class="font-bold text-slate-800">Kelas <?= htmlspecialchars($alert['nama_kelas']) ?></span></div>
                                <div class="text-xs text-slate-500"><?= $pesan ?>. Harap lengkapi.</div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="px-4 py-8 text-center text-sm text-slate-500 flex flex-col items-center">
                            <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center mb-3 text-emerald-500">
                                <i class="ri-check-double-line text-2xl"></i>
                            </div>
                            Semua nilai sudah lengkap!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="flex items-center">
          <div>
            <button type="button" class="flex items-center gap-3 text-sm bg-slate-50 border border-slate-200 rounded-full focus:ring-4 focus:ring-slate-100 pr-4 pl-1 py-1 hover:bg-slate-100 transition-colors" aria-expanded="false" data-dropdown-toggle="dropdown-user">
              <span class="sr-only">Buka menu pengguna</span>
              <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white shadow-sm">
                <i class="ri-user-smile-fill text-lg"></i>
              </div>
              <span class="font-semibold text-slate-700 hidden sm:block"><?php echo $_SESSION['nama'] ?? 'User'; ?></span>
              <i class="ri-arrow-down-s-line text-slate-400 hidden sm:block"></i>
            </button>
          </div>
          <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-2xl shadow-xl border border-slate-100 w-56 animate-fadeIn" id="dropdown-user">
            <div class="px-4 py-4" role="none">
              <p class="text-sm font-bold text-gray-900 truncate">
                <?php echo $_SESSION['nama'] ?? 'User'; ?>
              </p>
              <p class="text-xs text-emerald-600 font-medium truncate mt-1">
                @<?php echo $_SESSION['username'] ?? 'username'; ?>
              </p>
              <div class="mt-3 inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                <?php echo ucfirst($_SESSION['peran'] ?? 'Guest'); ?>
              </div>
            </div>
            <ul class="py-2 px-2" role="none">
              <li>
                <a href="dashboard" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-xl transition-all duration-200" role="menuitem">
                  <i class="ri-dashboard-3-line text-lg mr-3 text-slate-400"></i>
                  Dashboard
                </a>
              <li>
                <a href="profil" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-xl transition-all duration-200 mt-1" role="menuitem">
                  <i class="ri-user-settings-line text-lg mr-3 text-slate-400"></i>
                  Profil Saya
                </a>
              </li>
              <li>
                <a href="logout" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 rounded-xl transition-all duration-200 mt-1" role="menuitem">
                  <i class="ri-logout-circle-r-line text-lg mr-3"></i>
                  Keluar
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>