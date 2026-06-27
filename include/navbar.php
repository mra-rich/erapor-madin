<nav class="fixed top-0 z-50 w-full bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm transition-all duration-300">
  <div class="px-4 py-3 lg:px-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center justify-start rtl:justify-end">
        <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-xl sm:hidden hover:bg-emerald-50 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200 transition-colors">
          <span class="sr-only">Buka sidebar</span>
          <i class="ri-menu-2-line text-2xl"></i>
        </button>
        <a href="index.php" class="flex ms-2 md:me-24 items-center group">
          <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center mr-3 border border-emerald-100 group-hover:bg-emerald-500 transition-colors duration-300">
             <img src="assets/img/logo.png" class="h-6 object-contain group-hover:brightness-0 group-hover:invert transition-all" alt="Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3003/3003511.png'" />
          </div>
          <span class="self-center text-xl font-bold sm:text-2xl whitespace-nowrap brand-font text-slate-800">E-Rapor</span>
        </a>
      </div>
      <div class="flex items-center">
        <div class="flex items-center ms-3">
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
                <a href="dashboard.php" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-xl transition-all duration-200" role="menuitem">
                  <i class="ri-dashboard-3-line text-lg mr-3 text-slate-400"></i>
                  Dashboard
                </a>
              </li>
              <li>
                <a href="include/logout.php" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 rounded-xl transition-all duration-200 mt-1" role="menuitem">
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