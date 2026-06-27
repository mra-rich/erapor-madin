<?php
require 'koneksi.php';
require 'cek_sesi.php';
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 rounded-lg mt-10">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Tambah Mata Pelajaran</h2>

        <!-- Notifikasi Status -->
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="mb-4 p-4 rounded-lg <?php echo ($_GET['status'] == 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <form action="proses_tambah_mapel.php" method="POST">
            <label class="block mb-2 font-medium">Nama Mata Pelajaran:</label>
            <input type="text" name="nama_mapel" id="nama_mapel" class="w-full p-2 border rounded mb-4" required>

            <label class="block mb-2 font-medium">Nama Mata Pelajaran (Arab):</label>
            <input type="text" name="nama_mapel_arab" id="nama_mapel_arab" class="w-full p-2 border rounded mb-4" required>

            <label class="block mb-2 font-medium">Kategori:</label>
            <select name="kategori" class="w-full p-2 border rounded mb-4" required>
                <option value="" disabled selected>Pilih Kategori</option>
                <option value="TES TERTULIS">TES TERTULIS</option>
                <option value="HAFALAN">HAFALAN</option>
                <option value="TES BACA">TES BACA</option>
            </select>

            <label class="block mb-2 font-medium">Kelas:</label>
            <select name="id_kelas" class="w-full p-2 border rounded mb-4" required>
                <option value="" disabled selected>Pilih Kelas</option>
                <?php
                $kelas_query = "SELECT id_kelas, nama_kelas FROM kelas ORDER BY id_kelas ASC";
                $kelas_result = mysqli_query($koneksi, $kelas_query);
                while ($kelas = mysqli_fetch_assoc($kelas_result)) {
                    echo "<option value='{$kelas['id_kelas']}'>{$kelas['nama_kelas']}</option>";
                }
                ?>
            </select>

            <div class="flex justify-between">
                <a href="data_mata_pelajaran.php" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Kembali</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Script untuk auto-fill terjemahan Arab -->
<script>
    document.getElementById('nama_mapel').addEventListener('change', function() {
        const namaMapel = this.value;

        // Buat request AJAX untuk mendapatkan terjemahan
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