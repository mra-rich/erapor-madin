<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_GRADES);

include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 border-2 border-transparent mt-14">
        <!-- Header Halaman -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Import Nilai dari Excel</h1>
                <p class="text-sm text-gray-500 mt-1">Unduh template dan unggah data nilai santri secara massal</p>
            </div>
            <!-- Tombol Aksi Kanan Atas -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="data_nilai.php" class="inline-flex justify-center items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors shadow-sm text-sm">
                    <i class="fas fa-arrow-left mr-1.5 text-base"></i> Kembali ke Data Nilai
                </a>
            </div>
        </div>

        <?php
        if (isset($_SESSION['import_status'])) {
            $status = $_SESSION['import_status'];
            $msg = $_SESSION['import_msg'];
            if ($status == 'sukses') {
                echo "<div class='p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200' role='alert'>
                        <span class='font-medium'>Berhasil!</span> $msg
                      </div>";
            } else {
                echo "<div class='p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200' role='alert'>
                        <span class='font-medium'>Gagal!</span> $msg
                      </div>";
            }
            unset($_SESSION['import_status']);
            unset($_SESSION['import_msg']);
        }
        ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Langkah 1: Download Template -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Langkah 1: Unduh Template</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Unduh template Excel yang berisi format kolom yang sesuai untuk mengimpor nilai santri.
                </p>
                <form action="template_import_excel.php" method="GET">
                    <?php 
                    $no_autosubmit = true;
                    $id_kelas_selected = isset($id_kelas) ? $id_kelas : (isset($kelas_aktif) ? $kelas_aktif : 0); 
                    include 'include/filter_kelas.php'; 
                    ?>
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-download mr-2"></i> Unduh Template Excel
                    </button>
                </form>
            </div>

            <!-- Langkah 2: Upload Data -->
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Langkah 2: Unggah File Excel</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Isi template yang telah diunduh, simpan, lalu unggah file Excel (.xlsx) di bawah ini.
                </p>
                <form action="proses_import_nilai.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                        <select name="semester" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                            <option value="1" <?= ($_SESSION['semester'] == '1') ? 'selected' : '' ?>>Semester 1 (Ganjil)</option>
                            <option value="2" <?= ($_SESSION['semester'] == '2') ? 'selected' : '' ?>>Semester 2 (Genap)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">File Excel (.xlsx)</label>
                        <input type="file" name="file_excel" accept=".xlsx" required
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-upload mr-2"></i> Proses Import
                    </button>
                </form>
            </div>
        </div>
        
        <div class="mt-6">
            <a href="data_nilai.php" class="text-blue-600 hover:underline"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Data Nilai</a>
        </div>
    </div>
</div>

<?php include 'include/footer.php'; ?>
