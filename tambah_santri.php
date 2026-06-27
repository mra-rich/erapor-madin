<?php
require 'koneksi.php';
require 'cek_sesi.php';
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64">
  <div class="p-4 rounded-lg mt-10">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Input Data Siswa</h2>

    <!-- Notifikasi Status -->
    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
      <div class="mb-4 p-4 rounded-lg <?php echo ($_GET['status'] == 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo htmlspecialchars($_GET['message']); ?>
      </div>
    <?php endif; ?>

    <form action="proses_input_siswa.php" method="POST">
      <label class="block mb-2 font-medium">NISN:</label>
      <input type="text" name="nisn" class="w-full p-2 border rounded mb-4" required>

      <label class="block mb-2 font-medium">Nama Lengkap:</label>
      <input type="text" name="nama" class="w-full p-2 border rounded mb-4" required>

      <label class="block mb-2 font-medium">Nomor Santri:</label>
      <input type="text" name="nomor_santri" class="w-full p-2 border rounded mb-4" required>

      <label class="block mb-2 font-medium">Pilih Kelas:</label>
      <select id="kelas" class="w-full p-2 border rounded mb-4" name="id_kelas" required>
        <option value="" disabled selected>Pilih Kelas</option>
        <?php
        $kelas_query = "SELECT id_kelas, nama_kelas FROM kelas ORDER BY id_kelas ASC";
        $kelas_result = mysqli_query($koneksi, $kelas_query);
        while ($kelas = mysqli_fetch_assoc($kelas_result)) {
          echo "<option value='{$kelas['id_kelas']}'>{$kelas['nama_kelas']}</option>";
        }
        ?>
      </select>

      <label class="block mb-2 font-medium">Tahun Ajaran:</label>
      <input type="text" name="tahun_ajaran" class="w-full p-2 border rounded mb-4" required>

      <label class="block mb-2 font-medium">Alamat:</label>
      <textarea name="alamat" class="w-full p-2 border rounded mb-4" required></textarea>

      <label class="block mb-2 font-medium">Nama Wali:</label>
      <input type="text" name="nama_wali" class="w-full p-2 border rounded mb-4" required>

      <input type="hidden" name="id_pengguna" id="id_pengguna" value="<?php echo $_SESSION['id_pengguna'] ?? ''; ?>">

      <div class="flex space-x-4">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded">Simpan Data</button>
        <a href="data_santri.php" class="px-4 py-2 bg-gray-500 text-white font-medium rounded">Batal</a>
      </div>
    </form>
  </div>
</div>

<?php include 'include/footer.php'; ?>