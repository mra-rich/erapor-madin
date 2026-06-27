<?php
require 'koneksi.php';
require 'cek_sesi.php';

// Cek apakah ada ID kelas
if (!isset($_GET['id'])) {
    header("Location: data_kelas.php");
    exit;
}

$id_kelas = $_GET['id'];

// Ambil data kelas
$query = "SELECT * FROM kelas WHERE id_kelas = '$id_kelas'";
$result = mysqli_query($koneksi, $query);
$kelas = mysqli_fetch_assoc($result);

if (!$kelas) {
    header("Location: data_kelas.php");
    exit;
}

include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 rounded-lg mt-10">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Edit Kelas</h2>

        <!-- Notifikasi Status -->
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="mb-4 p-4 rounded-lg <?php echo ($_GET['status'] == 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <form action="proses_edit_kelas.php" method="POST">
            <input type="hidden" name="id_kelas" value="<?php echo $kelas['id_kelas']; ?>">

            <label class="block mb-2 font-medium">Nama Kelas:</label>
            <input type="text" name="nama_kelas" class="w-full p-2 border rounded mb-4" value="<?php echo htmlspecialchars($kelas['nama_kelas']); ?>" required>

            <label class="block mb-2 font-medium">Tingkat:</label>
            <select name="tingkat" class="w-full p-2 border rounded mb-4" required>
                <option value="" disabled>Pilih Tingkat</option>
                <option value="VII" <?php echo ($kelas['tingkat'] == 'VII') ? 'selected' : ''; ?>>VII</option>
                <option value="VIII" <?php echo ($kelas['tingkat'] == 'VIII') ? 'selected' : ''; ?>>VIII</option>
                <option value="IX" <?php echo ($kelas['tingkat'] == 'IX') ? 'selected' : ''; ?>>IX</option>
            </select>

            <label class="block mb-2 font-medium">Wali Kelas:</label>
            <select name="id_wali_kelas" class="w-full p-2 border rounded mb-4" required>
                <option value="" disabled>Pilih Wali Kelas</option>
                <?php
                $wali_query = "SELECT id_pengguna, nama FROM pengguna WHERE peran = 'Wali Kelas' ORDER BY nama ASC";
                $wali_result = mysqli_query($koneksi, $wali_query);
                while ($wali = mysqli_fetch_assoc($wali_result)) {
                    $selected = ($wali['id_pengguna'] == $kelas['id_wali_kelas']) ? 'selected' : '';
                    echo "<option value='{$wali['id_pengguna']}' {$selected}>{$wali['nama']}</option>";
                }
                ?>
            </select>

            <div class="flex justify-between mt-6">
                <a href="data_kelas.php" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'include/footer.php'; ?>