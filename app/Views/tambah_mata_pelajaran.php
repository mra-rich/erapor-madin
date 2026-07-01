<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_SUPER_ADMIN);
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// Ambil data tingkat
$tingkat_query = "SELECT * FROM tingkat_kelas ORDER BY id_tingkat ASC";
$tingkat_result = mysqli_query($koneksi, $tingkat_query);
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 rounded-lg mt-14 max-w-3xl mx-auto">
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Tambah Mata Pelajaran</h2>
                    <p class="text-sm text-gray-500 mt-1">Tambahkan mapel untuk satu tingkat kelas</p>
                </div>
                <a href="data_mata_pelajaran" class="text-gray-500 hover:text-gray-700 bg-white border border-gray-200 hover:bg-gray-100 rounded-lg text-sm px-4 py-2 transition-colors">
                    <i class="ri-arrow-left-line mr-1"></i> Kembali
                </a>
            </div>

            <div class="p-6">
                <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
                    <div class="mb-6 p-4 rounded-xl <?php echo ($_GET['status'] == 'success') ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
                        <div class="flex items-center">
                            <i class="<?php echo ($_GET['status'] == 'success') ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill'; ?> mr-2 text-xl"></i>
                            <span class="font-medium"><?php echo htmlspecialchars($_GET['message']); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="proses_tambah_mapel" method="POST" class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Nama Mata Pelajaran <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_mapel" id="nama_mapel" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" placeholder="Contoh: Aqidah, Fiqih" required>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Nama Arab (Otomatis/Manual) <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_mapel_arab" id="nama_mapel_arab" dir="rtl" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors font-arabic text-right text-lg" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Tingkat Kelas <span class="text-red-500">*</span></label>
                            <select name="id_tingkat" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" required>
                                <option value="" disabled selected>Pilih Tingkat</option>
                                <?php while ($t = mysqli_fetch_assoc($tingkat_result)): ?>
                                    <option value="<?php echo $t['id_tingkat']; ?>"><?php echo htmlspecialchars($t['nama_tingkat']); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Berlaku untuk semua rombel di tingkat ini.</p>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Nilai KKM <span class="text-red-500">*</span></label>
                            <input type="number" id="kkm" name="kkm" value="65" min="0" max="100" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" required>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900">Status <span class="text-red-500">*</span></label>
                            <select name="status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-colors" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Dihapus">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-4 mt-6 border-t border-gray-100">
                        <button type="submit" class="w-full text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-bold rounded-xl text-sm px-5 py-3.5 text-center transition-colors shadow-lg shadow-emerald-500/30">
                            <i class="ri-save-line mr-2"></i> Simpan Mata Pelajaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('nama_mapel').addEventListener('change', function() {
        const namaMapel = this.value;
        fetch('get_terjemahan_mapel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'nama_mapel=' + encodeURIComponent(namaMapel)
            })
            .then(response => response.json())
            .then(data => {
                if (data.terjemahan) {
                    document.getElementById('nama_mapel_arab').value = data.terjemahan;
                }
            })
            .catch(error => console.error('Error:', error));
    });
</script>

<?php include 'include/footer.php'; ?>