<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $tahun_ajaran = mysqli_real_escape_string($koneksi, $_POST['tahun_ajaran']);
    $semester = (int) $_POST['semester'];
    
    // Karena tabel pengaturan hanya ada 1 baris, kita UPDATE saja baris pertama
    $query = "UPDATE pengaturan SET tahun_ajaran = '$tahun_ajaran', semester = $semester";
    if (mysqli_query($koneksi, $query)) {
        // Update session juga agar efeknya langsung terasa
        $_SESSION['tahun_ajaran'] = $tahun_ajaran;
        $_SESSION['semester'] = $semester;
        
        header("Location: pengaturan.php?status=success");
        exit();
    } else {
        $error = "Gagal memperbarui pengaturan: " . mysqli_error($koneksi);
    }
}

// Ambil data saat ini
$q_pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
$data_pengaturan = mysqli_fetch_assoc($q_pengaturan);
if (!$data_pengaturan) {
    $data_pengaturan = ['tahun_ajaran' => '2024/2025', 'semester' => 1];
}

include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64 bg-slate-50 min-h-screen">
  <div class="p-4 mt-16 max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 premium-card">
        <div class="flex items-center mb-6 border-b border-slate-100 pb-4">
            <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mr-3">
                <i class="ri-settings-3-fill text-xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-800 brand-font">Pengaturan Sistem</h2>
                <p class="text-sm text-slate-500">Konfigurasi Tahun Ajaran dan Semester Aktif</p>
            </div>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success') : ?>
            <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200" role="alert">
                <span class="font-bold">Berhasil!</span> Pengaturan sistem berhasil diperbarui.
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)) : ?>
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
                <span class="font-bold">Error!</span> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="mb-5">
                <label for="tahun_ajaran" class="block mb-2 text-sm font-medium text-slate-700">Tahun Ajaran Aktif</label>
                <input type="text" id="tahun_ajaran" name="tahun_ajaran" value="<?php echo htmlspecialchars($data_pengaturan['tahun_ajaran']); ?>" 
                       class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5 transition-colors" 
                       placeholder="Contoh: 2024/2025" required>
                <p class="mt-1 text-xs text-slate-500">Gunakan format YYYY/YYYY.</p>
            </div>
            
            <div class="mb-6">
                <label for="semester" class="block mb-2 text-sm font-medium text-slate-700">Semester Aktif</label>
                <select id="semester" name="semester" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5 transition-colors" required>
                    <option value="1" <?php echo ($data_pengaturan['semester'] == 1) ? 'selected' : ''; ?>>1 (Ganjil)</option>
                    <option value="2" <?php echo ($data_pengaturan['semester'] == 2) ? 'selected' : ''; ?>>2 (Genap)</option>
                </select>
            </div>
            
            <button type="submit" class="text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center transition-colors">
                <i class="ri-save-line mr-1"></i> Simpan Pengaturan
            </button>
        </form>
    </div>
    
    <div class="mt-6 bg-blue-50 border border-blue-100 rounded-2xl p-4 text-sm text-blue-800 flex">
        <i class="ri-information-line text-lg mr-3 mt-0.5"></i>
        <div>
            <strong>Informasi:</strong> Perubahan pada Tahun Ajaran dan Semester akan langsung berlaku untuk seluruh pengguna (Admin, Wali Kelas, Guru). Nilai dan Absensi yang diinput akan otomatis masuk ke tahun ajaran dan semester aktif ini.
        </div>
    </div>
  </div>
</div>

<?php include 'include/footer.php'; ?>
