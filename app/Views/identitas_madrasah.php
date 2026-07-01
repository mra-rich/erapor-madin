<?php
require 'koneksi.php';
require 'cek_sesi.php';
require 'csrf.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);

include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// Ambil data identitas
$query = mysqli_query($koneksi, "SELECT * FROM identitas_madrasah WHERE id = 1");
$identitas = mysqli_fetch_assoc($query);

// Ambil data pengaturan (tahun ajaran & semester aktif)
$q_peng = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
$pengaturan = mysqli_fetch_assoc($q_peng);
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-14">
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div class="text-xl font-bold text-gray-800 mb-2 md:mb-0">Identitas Lembaga</div>
        </div>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div id="toast-success" class="flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow dark:text-gray-400 dark:bg-gray-800" role="alert">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                    </svg>
                    <span class="sr-only">Check icon</span>
                </div>
                <div class="ms-3 text-sm font-normal"><?= $_SESSION['flash_message'] ?></div>
                <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-dismiss-target="#toast-success" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <form action="proses_identitas.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Data Lembaga</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="md:col-span-2 flex flex-col md:flex-row gap-6 items-start">
                        <!-- Preview Logo -->
                        <div class="w-32 h-32 flex-shrink-0 border-2 border-dashed border-gray-300 rounded-lg overflow-hidden flex items-center justify-center bg-gray-50">
                            <?php if (!empty($identitas['logo']) && file_exists('uploads/' . $identitas['logo'])): ?>
                                <img src="uploads/<?= htmlspecialchars($identitas['logo']) ?>" alt="Logo Madrasah" class="w-full h-full object-contain">
                            <?php else: ?>
                                <i class="ri-image-add-line text-4xl text-gray-400"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logo Madrasah</label>
                            <input type="file" name="logo" accept="image/png, image/jpeg, image/jpg" class="w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <p class="mt-1 text-xs text-gray-500">Format yang diizinkan: JPG, JPEG, PNG. Maksimal 2MB. Logo ini akan digunakan pada kop surat, rapor, dan leger.</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Madrasah</label>
                        <input type="text" name="nama_madrasah" value="<?= htmlspecialchars($identitas['nama_madrasah'] ?? '') ?>" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NSMD (No Statistik)</label>
                        <input type="text" name="nsmd" value="<?= htmlspecialchars($identitas['nsmd'] ?? '') ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NPSN</label>
                        <input type="text" name="npsn" value="<?= htmlspecialchars($identitas['npsn'] ?? '') ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border">
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Kepala Madrasah</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kepala Madrasah</label>
                        <input type="text" name="nama_kepala" value="<?= htmlspecialchars($identitas['nama_kepala'] ?? '') ?>" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NIP / NIY Kepala Madrasah</label>
                        <input type="text" name="nip_kepala" value="<?= htmlspecialchars($identitas['nip_kepala'] ?? '') ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border">
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Alamat & Kontak</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Lengkap (Jalan / Dusun)</label>
                        <textarea name="alamat" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border"><?= htmlspecialchars($identitas['alamat'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kecamatan</label>
                        <input type="text" name="kecamatan" value="<?= htmlspecialchars($identitas['kecamatan'] ?? '') ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kabupaten / Kota</label>
                        <input type="text" name="kabupaten" value="<?= htmlspecialchars($identitas['kabupaten'] ?? '') ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Provinsi</label>
                        <input type="text" name="provinsi" value="<?= htmlspecialchars($identitas['provinsi'] ?? '') ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kode Pos</label>
                        <input type="text" name="kode_pos" value="<?= htmlspecialchars($identitas['kode_pos'] ?? '') ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                        <input type="text" name="telepon" value="<?= htmlspecialchars($identitas['telepon'] ?? '') ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                        <input type="text" name="website" value="<?= htmlspecialchars($identitas['website'] ?? '') ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($identitas['email'] ?? '') ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border">
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">🗓️ Tahun Ajaran & Semester Aktif</h3>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-700"><i class="ri-information-line mr-1"></i> Pengaturan ini menentukan tahun ajaran dan semester yang sedang aktif. Semua input nilai, rapor, dan laporan akan mengacu pada pengaturan ini.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tahun Ajaran Aktif</label>
                        <input type="text" name="tahun_ajaran" value="<?= htmlspecialchars($pengaturan['tahun_ajaran'] ?? '') ?>" placeholder="Contoh: 2024/2025" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border">
                        <p class="mt-1 text-xs text-gray-500">Format: YYYY/YYYY (contoh: 2024/2025)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Semester Aktif</label>
                        <select name="semester" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2 border">
                            <option value="1" <?= (($pengaturan['semester'] ?? '') == 1) ? 'selected' : '' ?>>Semester 1 (Ganjil)</option>
                            <option value="2" <?= (($pengaturan['semester'] ?? '') == 2) ? 'selected' : '' ?>>Semester 2 (Genap)</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end mt-8 border-t pt-6">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <i class="ri-save-line text-lg mr-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'include/footer.php'; ?>
