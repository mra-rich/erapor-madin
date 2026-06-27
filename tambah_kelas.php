<?php
require 'koneksi.php';
require 'cek_sesi.php';
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 rounded-lg mt-10">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Tambah Kelas Baru</h2>

        <!-- Notifikasi Status -->
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="mb-4 p-4 rounded-lg <?php echo ($_GET['status'] == 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <form action="proses_tambah_kelas.php" method="POST">
            <label class="block mb-2 font-medium">Nama Kelas:</label>
            <input type="text" name="nama_kelas" class="w-full p-2 border rounded mb-4" required>

            <label class="block mb-2 font-medium">Tingkat:</label>
            <select name="tingkat" class="w-full p-2 border rounded mb-4" required>
                <option value="" disabled selected>Pilih Tingkat</option>
                <option value="VII">VII</option>
                <option value="VIII">VIII</option>
                <option value="IX">IX</option>
            </select>

            <label class="block mb-2 font-medium">Wali Kelas:</label>
            <select name="id_wali_kelas" class="w-full p-2 border rounded mb-4" required>
                <option value="" disabled selected>Pilih Wali Kelas</option>
                <?php
                $wali_query = "SELECT id_pengguna, nama FROM pengguna WHERE peran = 'Wali Kelas' ORDER BY nama ASC";
                $wali_result = mysqli_query($koneksi, $wali_query);
                while ($wali = mysqli_fetch_assoc($wali_result)) {
                    echo "<option value='{$wali['id_pengguna']}'>{$wali['nama']}</option>";
                }
                ?>
            </select>

            <div class="flex justify-between mt-6">
                <a href="data_kelas.php" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <i class="fas fa-save mr-2"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'include/footer.php'; ?>