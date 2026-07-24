<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_SUPER_ADMIN);
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// Ambil data tingkat
$tingkat_query = "SELECT * FROM tingkat_kelas ORDER BY id_tingkat ASC";
$tingkat_result = mysqli_query($koneksi, $tingkat_query);
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 rounded-lg mt-14 max-w-2xl mx-auto">
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Tambah Kelas Baru</h2>
                    <p class="text-sm text-gray-500 mt-1">Lengkapi form di bawah untuk menambah Rombel</p>
                </div>
                <a href="data_kelas" class="text-gray-500 hover:text-gray-700 bg-white border border-gray-200 hover:bg-gray-100 rounded-lg text-sm px-4 py-2 transition-colors">
                    <i class="ri-arrow-left-line mr-1"></i> Kembali
                </a>
            </div>

            <div class="p-6">
                <!-- Notifikasi Status -->
                <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
                    <div class="mb-6 p-4 rounded-xl <?php echo ($_GET['status'] == 'success') ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
                        <div class="flex items-center">
                            <i class="<?php echo ($_GET['status'] == 'success') ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill'; ?> mr-2 text-xl"></i>
                            <span class="font-medium"><?php echo htmlspecialchars($_GET['message']); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="proses_tambah_kelas" method="POST" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-900">Tingkat Kelas <span class="text-red-500">*</span></label>
                        <select name="id_tingkat" id="id_tingkat" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors" required>
                            <option value="" disabled selected>Pilih Tingkat</option>
                            <?php while ($t = mysqli_fetch_assoc($tingkat_result)): ?>
                                <option value="<?php echo $t['id_tingkat']; ?>"><?php echo htmlspecialchars($t['nama_tingkat']); ?></option>
                            <?php endwhile; ?>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Pilih tingkat/level kelas (misal: Kelas 1).</p>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-900">Nama Rombel <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_rombel" id="nama_rombel" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors" placeholder="Contoh: A, B, Putra, Putri" required>
                        <p class="mt-1 text-xs text-gray-500">Kode grup rombongan belajar dalam tingkat tersebut.</p>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-900">Nama Tampilan (Lengkap) <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_kelas" id="nama_kelas" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors" placeholder="Contoh: Kelas 1A" required>
                        <p class="mt-1 text-xs text-gray-500">Ini yang akan tampil di raport dan aplikasi.</p>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-900">Wali Kelas <span class="text-red-500">*</span></label>
                        <select name="id_wali_kelas" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors" required>
                            <option value="" disabled selected>Pilih Wali Kelas</option>
                            <?php
                            $wali_query = "SELECT id_pengguna, nama FROM pengguna WHERE peran = 'Wali Kelas' ORDER BY nama ASC";
                            $wali_result = mysqli_query($koneksi, $wali_query);
                            while ($wali = mysqli_fetch_assoc($wali_result)) {
                                echo "<option value='{$wali['id_pengguna']}'>{$wali['nama']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="pt-4 mt-6 border-t border-gray-100">
                        <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-xl text-sm px-5 py-3.5 text-center transition-colors shadow-lg shadow-blue-500/30">
                            <i class="ri-save-line mr-2"></i> Simpan Kelas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-fill nama tampilan lengkap
    document.getElementById('nama_rombel').addEventListener('input', function() {
        var select = document.getElementById('id_tingkat');
        var tingkat_text = select.options[select.selectedIndex].text;
        var rombel = this.value;
        if(select.value !== "") {
            document.getElementById('nama_kelas').value = tingkat_text + " " + rombel;
        }
    });

    document.getElementById('id_tingkat').addEventListener('change', function() {
        var tingkat_text = this.options[this.selectedIndex].text;
        var rombel = document.getElementById('nama_rombel').value;
        if(rombel !== "") {
            document.getElementById('nama_kelas').value = tingkat_text + " " + rombel;
        }
    });
</script>

<?php include 'include/footer.php'; ?>