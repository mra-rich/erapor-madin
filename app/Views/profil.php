<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';

$id_pengguna = $_SESSION['id_pengguna'];

// Ambil data profil
$query = "SELECT * FROM pengguna WHERE id_pengguna = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id_pengguna);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64 bg-slate-50 min-h-screen">
    <div class="p-4 mt-16 max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 brand-font">Profil Saya</h1>
                <p class="text-sm font-medium text-slate-500 mt-1">Kelola informasi akun dan ganti password Anda.</p>
            </div>
        </div>

        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="p-4 mb-6 text-sm <?php echo $_GET['status'] == 'success' ? 'text-green-800 bg-green-50' : 'text-red-800 bg-red-50'; ?> rounded-xl border <?php echo $_GET['status'] == 'success' ? 'border-green-200' : 'border-red-200'; ?>" role="alert">
                <span class="font-medium"><?php echo $_GET['status'] == 'success' ? 'Berhasil!' : 'Gagal!'; ?></span> <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Profil Singkat -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 premium-card text-center">
                    <div class="w-24 h-24 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-md">
                        <span class="text-3xl font-bold text-emerald-600">
                            <?php echo strtoupper(substr($user['nama'], 0, 1)); ?>
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800 brand-font"><?php echo htmlspecialchars($user['nama']); ?></h2>
                    <p class="text-sm text-slate-500 font-medium mb-4"><?php echo htmlspecialchars($user['peran']); ?></p>
                    
                    <div class="text-left mt-6 space-y-3">
                        <div>
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Username</p>
                            <p class="text-sm font-medium text-slate-700"><?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Ganti Password -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 premium-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-lg font-bold text-slate-800 brand-font"><i class="ri-lock-password-line mr-2 text-emerald-500"></i>Ganti Password</h3>
                    </div>
                    <div class="p-6">
                        <form action="proses_ganti_password.php" method="POST" class="space-y-5">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div>
                                <label for="password_lama" class="block mb-2 text-sm font-medium text-gray-900">Password Lama</label>
                                <input type="password" name="password_lama" id="password_lama" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5 transition-colors" required>
                            </div>
                            
                            <div>
                                <label for="password_baru" class="block mb-2 text-sm font-medium text-gray-900">Password Baru</label>
                                <input type="password" name="password_baru" id="password_baru" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5 transition-colors" required minlength="6">
                                <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter.</p>
                            </div>

                            <div>
                                <label for="konfirmasi_password" class="block mb-2 text-sm font-medium text-gray-900">Konfirmasi Password Baru</label>
                                <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5 transition-colors" required minlength="6">
                            </div>
                            
                            <div class="pt-2">
                                <button type="submit" class="w-full md:w-auto text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-xl text-sm px-5 py-2.5 text-center inline-flex justify-center items-center transition-all duration-300">
                                    <i class="ri-save-line mr-2"></i> Simpan Password Baru
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'include/footer.php'; ?>
