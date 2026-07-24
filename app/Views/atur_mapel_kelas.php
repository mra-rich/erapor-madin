<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// Ambil semua mapel
$query_mapel = mysqli_query($koneksi, "SELECT * FROM mata_pelajaran ORDER BY id_mapel ASC");
$semua_mapel = [];
while ($row = mysqli_fetch_assoc($query_mapel)) {
    $semua_mapel[] = $row;
}

// Jika ada request get kelas, ambil mapel yang sudah tersimpan untuk kelas tersebut
$id_kelas = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;
$mapel_terpilih = [];
if ($id_kelas > 0) {
    $query_terpilih = mysqli_query($koneksi, "SELECT id_mapel FROM mapel_kelas WHERE id_kelas = $id_kelas");
    while ($row = mysqli_fetch_assoc($query_terpilih)) {
        $mapel_terpilih[] = $row['id_mapel'];
    }
}
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 rounded-lg mt-14">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Pengaturan Mapel per Kelas</h2>

        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="p-4 mb-4 text-sm rounded-lg <?php echo $_GET['status'] == 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'; ?>" role="alert">
                <span class="font-medium"><?php echo $_GET['status'] == 'success' ? 'Berhasil!' : 'Gagal!'; ?></span> <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Form Pilih Kelas -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <form action="" method="GET" class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="block mb-2 text-sm font-medium text-gray-900">Pilih Kelas</label>
                    <select name="kelas" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option value="">-- Pilih Kelas --</option>
                        <?php
                        $query_kelas = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                        while ($k = mysqli_fetch_assoc($query_kelas)) {
                            $selected = ($id_kelas == $k['id_kelas']) ? 'selected' : '';
                            echo "<option value='{$k['id_kelas']}' {$selected}>{$k['nama_kelas']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">Pilih</button>
            </form>
        </div>

        <?php if ($id_kelas > 0): ?>
        <!-- Form Atur Mapel -->
        <form action="proses_atur_mapel_kelas" method="POST" class="bg-white p-6 rounded-lg shadow-md">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="id_kelas" value="<?php echo $id_kelas; ?>">
            <h3 class="text-lg font-semibold border-b pb-2 mb-4">Daftar Mata Pelajaran</h3>
            <p class="text-sm text-gray-600 mb-4">Centang mata pelajaran yang diajarkan di kelas ini.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <?php foreach ($semua_mapel as $mapel): ?>
                    <div class="flex items-center p-3 border rounded hover:bg-gray-50 cursor-pointer">
                        <input id="mapel-<?php echo $mapel['id_mapel']; ?>" type="checkbox" name="mapel[]" value="<?php echo $mapel['id_mapel']; ?>" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" <?php echo in_array($mapel['id_mapel'], $mapel_terpilih) ? 'checked' : ''; ?>>
                        <label for="mapel-<?php echo $mapel['id_mapel']; ?>" class="ml-2 w-full text-sm font-medium text-gray-900 cursor-pointer">
                            <?php echo htmlspecialchars($mapel['nama_mapel']); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center space-x-4">
                <button type="submit" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Simpan Pengaturan</button>
            </div>
        </form>
        <?php else: ?>
        <div class="bg-gray-50 p-6 rounded-lg text-center text-gray-500 border border-dashed">
            Silakan pilih kelas terlebih dahulu untuk mengatur mata pelajaran.
        </div>
        <?php endif; ?>

    </div>
</div>

<?php include 'include/footer.php'; ?>
