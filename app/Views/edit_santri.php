<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_MANAGE_STUDENTS);
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// Cek apakah ada ID yang dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: data_santri.php");
    exit();
}

$id_siswa = intval($_GET['id']);

// Ambil data siswa berdasarkan ID
$query = "SELECT * FROM siswa WHERE id_siswa = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id_siswa);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: data_santri.php");
    exit();
}

$siswa = mysqli_fetch_assoc($result);
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 rounded-lg mt-10">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Edit Data Siswa</h2>

        <!-- Notifikasi Status -->
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="mb-4 p-4 rounded-lg <?php echo ($_GET['status'] == 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <form action="proses_edit_siswa.php" method="POST">
            <input type="hidden" name="id_siswa" value="<?php echo $siswa['id_siswa']; ?>">

            <label class="block mb-2 font-medium">NISN:</label>
            <input type="text" name="nisn" class="w-full p-2 border rounded mb-4" value="<?php echo htmlspecialchars($siswa['nisn']); ?>" required>

            <label class="block mb-2 font-medium">Nama Lengkap:</label>
            <input type="text" name="nama" class="w-full p-2 border rounded mb-4" value="<?php echo htmlspecialchars($siswa['nama']); ?>" required>

            <label class="block mb-2 font-medium">Tempat Lahir:</label>
            <input type="text" name="tempat_lahir" class="w-full p-2 border rounded mb-4" value="<?php echo htmlspecialchars($siswa['tempat_lahir'] ?? ''); ?>">

            <label class="block mb-2 font-medium">Tanggal Lahir:</label>
            <input type="date" name="tanggal_lahir" class="w-full p-2 border rounded mb-4" value="<?php echo htmlspecialchars($siswa['tanggal_lahir'] ?? ''); ?>">

            <label class="block mb-2 font-medium">Jenis Kelamin:</label>
            <select name="jenis_kelamin" class="w-full p-2 border rounded mb-4">
                <option value="L" <?php echo (isset($siswa['jenis_kelamin']) && $siswa['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                <option value="P" <?php echo (isset($siswa['jenis_kelamin']) && $siswa['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
            </select>

            <label class="block mb-2 font-medium">No. Induk Santri:</label>
            <input type="text" name="nomor_santri" class="w-full p-2 border rounded mb-4" value="<?php echo htmlspecialchars($siswa['nomor_santri']); ?>" required>

            <label class="block mb-2 font-medium">Pilih Kelas:</label>
            <select id="kelas" class="w-full p-2 border rounded mb-4" name="id_kelas" required>
                <option value="" disabled>Pilih Kelas</option>
                <?php
                $kelas_query = "SELECT id_kelas, nama_kelas FROM kelas ORDER BY id_kelas ASC";
                $kelas_result = mysqli_query($koneksi, $kelas_query);
                while ($kelas = mysqli_fetch_assoc($kelas_result)) {
                    $selected = ($kelas['id_kelas'] == $siswa['id_kelas']) ? 'selected' : '';
                    echo "<option value='{$kelas['id_kelas']}' {$selected}>{$kelas['nama_kelas']}</option>";
                }
                ?>
            </select>

            <label class="block mb-2 font-medium">Tahun Ajaran:</label>
            <input type="text" name="tahun_ajaran" class="w-full p-2 border rounded mb-4" value="<?php echo htmlspecialchars($siswa['tahun_ajaran']); ?>" required>

            <label class="block mb-2 font-medium">Alamat:</label>
            <textarea name="alamat" class="w-full p-2 border rounded mb-4" required><?php echo htmlspecialchars($siswa['alamat']); ?></textarea>

            <label class="block mb-2 font-medium">Nama Wali:</label>
            <input type="text" name="nama_wali" class="w-full p-2 border rounded mb-4" value="<?php echo htmlspecialchars($siswa['nama_wali']); ?>" required>

            <div class="flex space-x-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded">Simpan Perubahan</button>
                <a href="data_santri.php" class="px-4 py-2 bg-gray-500 text-white font-medium rounded">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php include 'include/footer.php'; ?>