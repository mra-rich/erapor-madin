<?php
require 'koneksi.php';
require 'cek_sesi.php';
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// Query database
$siswa = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM siswa"))['total'];
$kelas = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kelas"))['total'];
$rapot_tahun = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi_raport WHERE tahun_ajaran = '2024/2025'"))['total'];
$rapot_semua = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi_raport"))['total'];
$wali_kelas = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengguna WHERE peran = 'Wali Kelas'"))['total'];
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
      <div class="bg-blue-500 rounded-2xl p-6 shadow-lg shadow-blue-500/30 premium-card relative overflow-hidden group">
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
      </div>

      <!-- Card Kelas -->
      <div class="bg-emerald-500 rounded-2xl p-6 shadow-lg shadow-emerald-500/30 premium-card relative overflow-hidden group">
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
      </div>

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

    <!-- Empty State / Welcome Illustration Area (Optional) -->
    <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 premium-card text-center flex flex-col items-center justify-center py-16">
        <div class="w-24 h-24 bg-emerald-50 rounded-full flex items-center justify-center mb-4">
            <i class="ri-rocket-2-line text-4xl text-emerald-500"></i>
        </div>
        <h2 class="text-xl font-bold text-slate-800 brand-font">Sistem Siap Digunakan!</h2>
        <p class="text-slate-500 mt-2 max-w-md">Pilih menu di sidebar sebelah kiri untuk mulai mengelola data master, nilai, dan mencetak rapor santri.</p>
    </div>

  </div>
</div>

<?php include 'include/footer.php'; ?>