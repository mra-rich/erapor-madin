<?php
require 'koneksi.php';
require 'cek_sesi.php';
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// Cek apakah ada ID mapel
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: data_mata_pelajaran.php");
    exit;
}

$id_mapel = intval($_GET['id']);

// Ambil data mata pelajaran
$query = "SELECT * FROM mata_pelajaran WHERE id_mapel = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id_mapel);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$mapel = mysqli_fetch_assoc($result);

if (!$mapel) {
    header("Location: data_mata_pelajaran.php?status=error&message=Data mata pelajaran tidak ditemukan!");
    exit;
}
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 rounded-lg mt-10">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Edit Mata Pelajaran</h2>

        <!-- Notifikasi Status -->
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="mb-4 p-4 rounded-lg <?php echo ($_GET['status'] == 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <form action="proses_edit_mapel.php" method="POST">
            <input type="hidden" name="id_mapel" value="<?= $mapel['id_mapel']; ?>">

            <label class="block mb-2 font-medium">Nama Mata Pelajaran:</label>
            <input type="text" name="nama_mapel" id="nama_mapel" class="w-full p-2 border rounded mb-4" value="<?= htmlspecialchars($mapel['nama_mapel']); ?>" required>

            <label class="block mb-2 font-medium">Nama Mata Pelajaran (Arab):</label>
            <input type="text" name="nama_mapel_arab" id="nama_mapel_arab" class="w-full p-2 border rounded mb-4" value="<?= htmlspecialchars($mapel['nama_mapel_arab']); ?>" required>

            <label class="block mb-2 font-medium">Kategori:</label>
            <select name="kategori" class="w-full p-2 border rounded mb-4" required>
                <option value="" disabled>Pilih Kategori</option>
                <option value="TES TERTULIS" <?= ($mapel['kategori'] == 'TES TERTULIS') ? 'selected' : ''; ?>>TES TERTULIS</option>
                <option value="HAFALAN" <?= ($mapel['kategori'] == 'HAFALAN') ? 'selected' : ''; ?>>HAFALAN</option>
                <option value="TES BACA" <?= ($mapel['kategori'] == 'TES BACA') ? 'selected' : ''; ?>>TES BACA</option>
            </select>

            <label class="block mb-2 font-medium">Kelas:</label>
            <select name="id_kelas" class="w-full p-2 border rounded mb-4" required>
                <option value="" disabled>Pilih Kelas</option>
                <?php
                $kelas_query = "SELECT id_kelas, nama_kelas FROM kelas ORDER BY id_kelas ASC";
                $kelas_result = mysqli_query($koneksi, $kelas_query);
                while ($kelas = mysqli_fetch_assoc($kelas_result)) {
                    $selected = ($kelas['id_kelas'] == $mapel['id_kelas']) ? 'selected' : '';
                    echo "<option value='{$kelas['id_kelas']}' {$selected}>{$kelas['nama_kelas']}</option>";
                }
                ?>
            </select>

            <div class="flex justify-between">
                <a href="data_mata_pelajaran.php" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Kembali</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php include 'include/footer.php'; ?>