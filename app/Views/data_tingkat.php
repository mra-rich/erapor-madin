<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// Ambil data tingkat
$query = "SELECT * FROM tingkat_kelas ORDER BY id_tingkat ASC";
$result = mysqli_query($koneksi, $query);

// Handle tambah
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_tingkat'])) {
    $nama_tingkat = mysqli_real_escape_string($koneksi, $_POST['nama_tingkat']);
    $query_insert = "INSERT INTO tingkat_kelas (nama_tingkat) VALUES ('$nama_tingkat')";
    if (mysqli_query($koneksi, $query_insert)) {
        require_once 'logger.php';
        catat_log($koneksi, $_SESSION['id_pengguna'], 'Tambah Tingkat', "Menambahkan tingkat baru: $nama_tingkat");
        echo "<script>window.location.href='data_tingkat?status=success&message=Data berhasil ditambahkan';</script>";
    } else {
        echo "<script>window.location.href='data_tingkat?status=error&message=Data gagal ditambahkan';</script>";
    }
}

// Handle hapus
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    $query_hapus = "DELETE FROM tingkat_kelas WHERE id_tingkat = $id_hapus";
    if (mysqli_query($koneksi, $query_hapus)) {
        require_once 'logger.php';
        catat_log($koneksi, $_SESSION['id_pengguna'], 'Hapus Tingkat', "Menghapus tingkat ID: $id_hapus");
        echo "<script>window.location.href='data_tingkat?status=success&message=Data berhasil dihapus';</script>";
    } else {
        echo "<script>window.location.href='data_tingkat?status=error&message=Data gagal dihapus';</script>";
    }
}
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 rounded-lg mt-14">
        
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-extrabold text-gray-800 tracking-tight">Master Tingkat Kelas</h2>
            <button data-modal-target="tambah-modal" data-modal-toggle="tambah-modal" class="text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-xl text-sm px-5 py-2 text-center inline-flex items-center shadow-lg shadow-emerald-500/30 transition-all duration-200">
                <i class="ri-add-line mr-2 text-lg"></i> Tambah Tingkat
            </button>
        </div>



        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600 border-collapse border border-slate-300">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                    <tr>
                        <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300 w-16">No</th>
                        <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Nama Tingkat</th>
                        <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300 whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php 
                    $no = 1;
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)): 
                    ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="py-2 px-6 border border-slate-300 whitespace-nowrap font-medium text-slate-900 text-center"><?php echo $no++; ?></td>
                            <td class="py-2 px-6 border border-slate-300 whitespace-nowrap">
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold mr-2 px-3 py-1.5 rounded-full shadow-sm border border-blue-200"><?php echo htmlspecialchars($row['nama_tingkat']); ?></span>
                            </td>
                            <td class="py-2 px-6 border border-slate-300 whitespace-nowrap text-center">
                                <a href="data_tingkat?hapus=<?php echo $row['id_tingkat']; ?>" onclick="return sweetConfirm(event, this, 'Yakin ingin menghapus? Tingkat ini mungkin digunakan di tabel Kelas dan Mapel!');" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2.5 rounded-xl inline-flex items-center transition-colors shadow-sm cursor-pointer" title="Hapus">
                                    <i class="ri-delete-bin-line text-lg"></i>
                                </a>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    } else {
                        echo '<tr><td colspan="3" class="py-2 px-6 text-center text-slate-500 border border-slate-300">Belum ada master tingkat kelas.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div id="tambah-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full backdrop-blur-sm bg-gray-900/50 transition-opacity">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-2xl shadow-xl">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                <h3 class="text-xl font-bold text-gray-900">
                    Tambah Tingkat Kelas
                </h3>
                <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center transition-colors" data-modal-hide="tambah-modal">
                    <i class="ri-close-line text-xl"></i>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <div class="p-4 md:p-5">
                <form class="space-y-4" action="" method="POST">
                    <div>
                        <label for="nama_tingkat" class="block mb-2 text-sm font-medium text-gray-900">Nama Tingkat</label>
                        <input type="text" name="nama_tingkat" id="nama_tingkat" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3" placeholder="Contoh: Kelas 1, Kelas 2" required>
                    </div>
                    <button type="submit" name="tambah_tingkat" class="w-full text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-xl text-sm px-5 py-3 text-center transition-colors">Simpan Tingkat</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
<?php include 'include/footer.php'; ?>
