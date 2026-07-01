<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_VIEW_ALL);
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

?>

<div class="p-4 sm:ml-64">
  <div class="p-4 border-2 border-transparent mt-14">
    
    <!-- Header Halaman -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Data Arsip & Buku Induk Santri</h1>
        <p class="text-gray-500 mt-2">Kelola data alumni, santri boyong, pindah, atau lulus.</p>
      </div>
      <!-- Tombol Aksi Kanan Atas -->
      <div class="flex flex-wrap items-center gap-2">

        <?php 
          $export_url = "export_santri.php";
          if (isset($_GET['kelas']) && $_GET['kelas'] > 0) {
              $export_url .= "?kelas=" . (int)$_GET['kelas'];
          }
        ?>
        <a href="<?= $export_url ?>" target="_blank" download="Data_Santri.xls" class="inline-flex justify-center items-center px-4 py-2 text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 focus:ring-2 focus:ring-indigo-300 font-medium rounded-lg text-sm transition-colors shadow-sm">
          <i class="ri-file-download-line mr-1.5 text-base"></i> Export
        </a>
        <?php if ($_SESSION['peran'] === 'Admin'): ?>
          <button type="button" onclick="openOffcanvas('offcanvas-import-siswa')" class="inline-flex justify-center items-center px-4 py-2 text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 focus:ring-2 focus:ring-emerald-300 font-medium rounded-lg text-sm transition-colors shadow-sm">
            <i class="ri-file-excel-2-line mr-1.5 text-base"></i> Import
          </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Notifikasi Status -->


    <!-- Form Pencarian Kelas -->
    <?php if ($_SESSION['peran'] !== 'Wali Kelas'): ?>
    <div class="mb-6">
      <form method="GET" class="flex flex-col xl:flex-row gap-4 items-end bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
          <?php 
          $id_kelas_selected = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;
          include 'include/filter_kelas.php'; 
          ?>
          <?php if($id_kelas_selected > 0): ?>
              <div class="flex-none w-full xl:w-auto">
                  <a href="data_santri.php" class="w-full bg-gray-100 text-gray-700 px-6 py-3 rounded-xl hover:bg-gray-200 transition-colors flex items-center justify-center font-bold shadow-sm">
                      <i class="ri-refresh-line mr-2"></i> Reset
                  </a>
              </div>
          <?php endif; ?>
      </form>
    </div>
    <?php endif; ?>

    <!-- Toolbar Tabel -->
    <div class="mb-4 flex flex-wrap gap-2 justify-end items-center bg-gray-50 p-3 rounded-lg border">
      <div class="mr-auto my-auto flex flex-wrap items-center gap-4">
        <div class="relative w-full sm:w-64">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="ri-search-line text-gray-400"></i>
          </div>
          <input type="text" id="customSearchInput" class="bg-white border border-gray-200 text-gray-800 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2 py-1.5 shadow-sm transition-colors" placeholder="Cari santri...">
        </div>
      </div>
    </div>
    
    <style>
      /* Sembunyikan default search dari simple-datatables */
      .dataTable-search, .datatable-search, .dataTable-input, .datatable-input {
          display: none !important;
      }
      /* Rapikan sedikit container datatable-top agar tidak terlalu tinggi kalau kosong sebelah kiri */
      .dataTable-top, .datatable-top {
          display: none !important;
      }
      
      /* Perbaiki tampilan dropdown entries per page agar panahnya tidak bertumpuk dengan angka */
      .dataTable-selector, .datatable-selector, 
      .dataTable-dropdown select, .datatable-dropdown select {
          padding: 0.375rem 2.5rem 0.375rem 1rem !important;
          border-radius: 0.5rem !important;
          border: 1px solid #d1d5db !important;
          background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
          background-position: right 0.5rem center !important;
          background-repeat: no-repeat !important;
          background-size: 1.5em 1.5em !important;
          -webkit-appearance: none !important;
          -moz-appearance: none !important;
          appearance: none !important;
          background-color: white !important;
          font-size: 0.875rem !important;
          font-weight: 500 !important;
          color: #374151 !important;
          margin-right: 0.5rem !important;
          min-width: 4.5rem !important;
          cursor: pointer;
      }
      
      .dataTable-dropdown label, .datatable-dropdown label {
          display: flex !important;
          align-items: center !important;
          color: #4b5563 !important;
          font-size: 0.875rem !important;
      }
    </style>

    <!-- DataTable -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table id="santriTable" class="w-full text-sm text-left text-slate-600 border-collapse border border-slate-300">
          <thead class="text-xs text-slate-700 uppercase bg-slate-50">
            <tr>
              <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300 w-16">No</th>
              <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Nama</th>
              <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">No. Induk</th>
              <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Kelas</th>
              <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Tahun Ajaran</th>
              <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Alamat</th>
              <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Nama Wali</th>
              <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300 whitespace-nowrap">Status</th>
              <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300 whitespace-nowrap">Aksi</th>
            </tr>
          </thead>
        <tbody>
          <?php
          $id_pengguna = $_SESSION['id_pengguna'];
          $filter_kelas = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;
          $where_clause = "";

          if ($filter_kelas > 0) {
              $where_clause = " AND kelas.id_kelas = $filter_kelas";
          }

          if ($_SESSION['peran'] === 'Wali Kelas') {
              $query = "SELECT siswa.*, CONCAT(kelas.nama_kelas, ' ', IFNULL(kelas.nama_rombel,''), ' ', tingkat_kelas.nama_tingkat) as nama_kelas 
                      FROM siswa 
                      LEFT JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
                      LEFT JOIN tingkat_kelas ON kelas.id_tingkat = tingkat_kelas.id_tingkat
                      WHERE kelas.id_wali_kelas = '$id_pengguna' AND siswa.status != 'Aktif' $where_clause
                      ORDER BY siswa.id_siswa DESC";
          } else {
              $query = "SELECT siswa.*, CONCAT(kelas.nama_kelas, ' ', IFNULL(kelas.nama_rombel,''), ' ', tingkat_kelas.nama_tingkat) as nama_kelas 
                      FROM siswa 
                      LEFT JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
                      LEFT JOIN tingkat_kelas ON kelas.id_tingkat = tingkat_kelas.id_tingkat
                      WHERE siswa.status != 'Aktif' $where_clause 
                      ORDER BY siswa.id_siswa DESC";
          }
          $result = mysqli_query($koneksi, $query);
          $no = 1;

          while ($row = mysqli_fetch_assoc($result)) :
          ?>
            <tr class="hover:bg-slate-50 transition-colors group <?php if ($_SESSION['peran'] !== 'Kepala Madrasah'): ?>cursor-pointer<?php endif; ?>" <?php if ($_SESSION['peran'] !== 'Kepala Madrasah'): ?>onclick="openEditSantri(<?= $row['id_siswa']; ?>)"<?php endif; ?>>
              <td class="py-2 px-6 border border-slate-300 text-center whitespace-nowrap"><?= $no++; ?></td>
              <td class="py-2 px-6 border border-slate-300 font-bold text-slate-800 whitespace-nowrap"><?= htmlspecialchars($row['nama']); ?></td>
              <td class="py-2 px-6 border border-slate-300 whitespace-nowrap"><span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md text-xs font-semibold font-mono"><?= htmlspecialchars($row['nomor_santri']); ?></span></td>
              <td class="py-2 px-6 border border-slate-300 font-medium text-blue-600 whitespace-nowrap"><?= htmlspecialchars($row['nama_kelas'] ?? 'Belum Ada Kelas'); ?></td>
              <td class="py-2 px-6 border border-slate-300 whitespace-nowrap"><?= htmlspecialchars($row['tahun_ajaran']); ?></td>
              <td class="py-2 px-6 border border-slate-300 text-slate-500 truncate max-w-[200px] whitespace-nowrap" title="<?= htmlspecialchars($row['alamat'] ?? '-'); ?>"><?= htmlspecialchars($row['alamat'] ?? '-'); ?></td>
              <td class="py-2 px-6 border border-slate-300 whitespace-nowrap"><?= htmlspecialchars($row['nama_wali']); ?></td>
              <td class="py-2 px-6 border border-slate-300 text-center whitespace-nowrap">
                <?php
                $statusColor = 'bg-slate-100 text-slate-700';
                if ($row['status'] == 'Alumni' || $row['status'] == 'Lulus') $statusColor = 'bg-emerald-100 text-emerald-700';
                if ($row['status'] == 'Boyong') $statusColor = 'bg-amber-100 text-amber-700';
                if ($row['status'] == 'Pindah') $statusColor = 'bg-blue-100 text-blue-700';
                if ($row['status'] == 'Dihapus') $statusColor = 'bg-red-100 text-red-700';
                ?>
                <span class="<?= $statusColor; ?> px-2.5 py-1 rounded-md text-xs font-semibold"><?= htmlspecialchars($row['status']); ?></span>
              </td>
              <td class="py-2 px-6 border border-slate-300 text-center whitespace-nowrap">
                <div class="flex justify-center space-x-1">
                    <a href="cetak_buku_induk.php?id_siswa=<?= $row['id_siswa']; ?>" target="_blank" onclick="event.stopPropagation();" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-full transition-all duration-200" title="Cetak Buku Induk">
                        <i class="ri-book-read-line text-lg"></i>
                    </a>
                    <button type="button" onclick="event.stopPropagation(); openDetailSantri(<?= $row['id_siswa']; ?>)" class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-full transition-all duration-200" title="Detail">
                        <i class="ri-eye-line text-lg"></i>
                    </button>
                    <?php if ($_SESSION['peran'] !== 'Kepala Madrasah'): ?>
                    <button type="button" onclick="event.stopPropagation(); openEditSantri(<?= $row['id_siswa']; ?>)" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-all duration-200" title="Edit">
                        <i class="ri-edit-line text-lg"></i>
                    </button>
                    <?php endif; ?>
                    <?php if ($_SESSION['peran'] !== 'Wali Kelas' && $_SESSION['peran'] !== 'Kepala Madrasah'): ?>
                    <a hx-get="hapus_santri.php?id=<?= $row['id_siswa']; ?>&konfirmasi=ya&csrf_token=<?= generate_csrf_token(); ?>" hx-target="closest tr" hx-swap="outerHTML swap:1s" hx-confirm="Apakah Anda yakin ingin menghapus data ini?" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-all duration-200 cursor-pointer" onclick="event.stopPropagation();" title="Hapus">
                        <i class="ri-delete-bin-line text-lg"></i>
                    </a>
                    <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ================= MODAL OFFCANVAS TAMBAH SANTRI ================= -->
<div id="overlay-tambah-siswa" class="fixed inset-0 bg-gray-900/60 z-40 hidden backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeOffcanvas('offcanvas-tambah-siswa')"></div>

<div id="offcanvas-tambah-siswa" class="fixed inset-y-0 right-0 z-50 w-full max-w-2xl bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    
    <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50 flex justify-between items-center">
        <div>
            <h3 class="text-xl font-extrabold text-gray-800">Tambah Data Santri</h3>
            <p class="text-sm text-gray-500 mt-1">Isi formulir pendaftaran santri baru</p>
        </div>
        <button type="button" onclick="closeOffcanvas('offcanvas-tambah-siswa')" class="text-gray-400 bg-white hover:bg-gray-100 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center shadow-sm">
            <i class="ri-close-line text-2xl"></i>
        </button>
    </div>

    <div class="p-6 flex-1 overflow-y-auto">
        <form action="proses_input_siswa.php" method="POST" id="formTambahSantri" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
            
            <!-- SECTION 1: Identitas Santri -->
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                <h4 class="text-md font-bold text-gray-800 border-b border-gray-200 pb-2 mb-4">I. Identitas Santri</h4>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Nama Santri <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">No. Induk <span class="text-red-500">*</span></label>
                            <input type="text" name="nomor_santri" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">NISN</label>
                            <input type="text" name="nisn" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Status Dalam Keluarga</label>
                            <input type="text" name="status_dalam_keluarga" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Anak Ke</label>
                            <input type="number" name="anak_ke" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-700">Alamat Santri</label>
                        <textarea name="alamat" rows="2" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"></textarea>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Riwayat Sekolah & Akademik -->
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 mt-4">
                <h4 class="text-md font-bold text-gray-800 border-b border-gray-200 pb-2 mb-4">II. Riwayat Sekolah & Akademik</h4>
                <div class="space-y-4">
                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-700">Sekolah Asal</label>
                        <input type="text" name="sekolah_asal" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Diterima Di Kelas</label>
                            <input type="text" name="diterima_di_kelas" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Pada Tanggal</label>
                            <input type="date" name="diterima_pada_tanggal" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Kelas Saat Ini <span class="text-red-500">*</span></label>
                            <select name="id_kelas" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                <option value="" disabled selected>-- Pilih Kelas --</option>
                                <?php
                                $kelas_query = "SELECT k.id_kelas, CONCAT(k.nama_kelas, ' ', IFNULL(k.nama_rombel,''), ' ', t.nama_tingkat) as nama_kelas FROM kelas k LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat ORDER BY k.id_kelas ASC";
                                $kelas_result = mysqli_query($koneksi, $kelas_query);
                                while ($k = mysqli_fetch_assoc($kelas_result)) {
                                    echo "<option value='{$k['id_kelas']}'>" . htmlspecialchars($k['nama_kelas']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Tahun Ajaran <span class="text-red-500">*</span></label>
                            <select name="tahun_ajaran" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                <?php
                                $current_year_hijri = 1446;
                                for ($i = 0; $i < 5; $i++) {
                                    $start_year = $current_year_hijri + $i;
                                    $end_year = $start_year + 1;
                                    $ta = "{$start_year}/{$end_year}";
                                    echo "<option value=\"$ta\">$ta</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Orang Tua & Wali -->
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 mt-4">
                <h4 class="text-md font-bold text-gray-800 border-b border-gray-200 pb-2 mb-4">III. Orang Tua & Wali</h4>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Nama Ayah</label>
                            <input type="text" name="nama_ayah" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Pekerjaan Ayah</label>
                            <input type="text" name="pekerjaan_ayah" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Nama Ibu</label>
                            <input type="text" name="nama_ibu" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Pekerjaan Ibu</label>
                            <input type="text" name="pekerjaan_ibu" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Nama Wali (Opsional)</label>
                            <input type="text" name="nama_wali" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-700">Pekerjaan Wali</label>
                            <input type="text" name="pekerjaan_wali" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-700">Alamat Orang Tua</label>
                        <textarea name="alamat_orang_tua" rows="2" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"></textarea>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-700">No. Handphone</label>
                        <input type="text" name="no_handphone" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                </div>
            </div>

        </form>
    </div>
    
    <div class="p-6 border-t border-gray-100 bg-gray-50">
        <button type="button" onclick="document.getElementById('formTambahSantri').submit();" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-xl text-lg px-5 py-4 text-center transition-all shadow-lg shadow-blue-500/30 flex items-center justify-center">
            <i class="ri-save-line mr-2"></i> Simpan Data Santri
        </button>
    </div>
</div>


<!-- ================= MODAL OFFCANVAS EDIT SANTRI ================= -->
<div id="overlay-edit-siswa" class="fixed inset-0 bg-gray-900/60 z-40 hidden backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeOffcanvas('offcanvas-edit-siswa')"></div>

<div id="offcanvas-edit-siswa" class="fixed top-0 right-0 z-50 h-screen w-full max-w-2xl bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">
    <div class="sticky top-0 bg-white z-10 px-6 py-4 border-b flex justify-between items-center">
        <div>
            <h3 class="text-xl font-bold text-gray-800">Edit Data Santri</h3>
            <p class="text-sm text-gray-500 mt-1">Perbarui informasi santri di bawah ini.</p>
        </div>
        <button type="button" onclick="closeOffcanvas('offcanvas-edit-siswa')" class="text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-full p-2 transition-colors">
            <i class="ri-close-line text-2xl"></i>
        </button>
    </div>

    <form action="proses_edit_siswa.php" method="POST" class="p-6">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
        <input type="hidden" name="id_siswa" id="edit_id_siswa">

        <!-- Bagian 1: Data Utama -->
        <div class="mb-8">
            <h4 class="text-md font-semibold text-blue-700 mb-4 flex items-center border-b pb-2"><i class="ri-user-settings-line mr-2"></i> Data Utama</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NISN</label>
                    <input type="text" name="nisn" id="edit_nisn" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Nomor Induk Santri Nasional">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Induk <span class="text-red-500">*</span></label>
                    <input type="text" name="nomor_santri" id="edit_nomor_santri" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Nomor unik santri">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="edit_nama" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Nama lengkap sesuai akta">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas</label>
                    <select name="id_kelas" id="edit_id_kelas" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="">-- Belum Ada Kelas --</option>
                        <?php
                        // Re-fetch for edit dropdown
                        $kelas_result_edit = mysqli_query($koneksi, "SELECT k.id_kelas, CONCAT(k.nama_kelas, ' ', IFNULL(k.nama_rombel,''), ' ', t.nama_tingkat) as nama_kelas FROM kelas k LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat ORDER BY k.id_kelas ASC");
                        while ($kelas = mysqli_fetch_assoc($kelas_result_edit)) {
                            echo "<option value='{$kelas['id_kelas']}'>{$kelas['nama_kelas']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                    <input type="text" name="tahun_ajaran" id="edit_tahun_ajaran" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Misal: 1445/1446">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Santri <span class="text-red-500">*</span></label>
                    <select name="status" id="edit_status" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="Aktif">Aktif</option>
                        <option value="Boyong">Boyong</option>
                        <option value="Alumni">Alumni</option>
                        <option value="Lulus">Lulus</option>
                        <option value="Pindah">Pindah</option>
                        <option value="Dihapus">Dihapus</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Bagian 2: Data Pribadi -->
        <div class="mb-8">
            <h4 class="text-md font-semibold text-blue-700 mb-4 flex items-center border-b pb-2"><i class="ri-profile-line mr-2"></i> Data Pribadi</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" id="edit_tempat_lahir" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="edit_tanggal_lahir" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                    <select name="jenis_kelamin" id="edit_jenis_kelamin" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="L">Laki-Laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status dalam Keluarga</label>
                    <input type="text" name="status_dalam_keluarga" id="edit_status_dalam_keluarga" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Misal: Anak Kandung">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Anak Ke-</label>
                    <input type="number" name="anak_ke" id="edit_anak_ke" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                    <textarea name="alamat" id="edit_alamat" rows="2" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
        </div>

        <!-- Bagian 3: Pendidikan Sebelumnya -->
        <div class="mb-8">
            <h4 class="text-md font-semibold text-blue-700 mb-4 flex items-center border-b pb-2"><i class="ri-building-4-line mr-2"></i> Pendidikan Sebelumnya</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sekolah Asal</label>
                    <input type="text" name="sekolah_asal" id="edit_sekolah_asal" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Diterima di Kelas</label>
                    <input type="text" name="diterima_di_kelas" id="edit_diterima_di_kelas" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Diterima Pada Tanggal</label>
                    <input type="date" name="diterima_pada_tanggal" id="edit_diterima_pada_tanggal" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Bagian 4: Data Orang Tua / Wali -->
        <div class="mb-8">
            <h4 class="text-md font-semibold text-blue-700 mb-4 flex items-center border-b pb-2"><i class="ri-parent-line mr-2"></i> Data Orang Tua / Wali</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ayah</label>
                    <input type="text" name="nama_ayah" id="edit_nama_ayah" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ibu</label>
                    <input type="text" name="nama_ibu" id="edit_nama_ibu" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan Ayah</label>
                    <input type="text" name="pekerjaan_ayah" id="edit_pekerjaan_ayah" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan Ibu</label>
                    <input type="text" name="pekerjaan_ibu" id="edit_pekerjaan_ibu" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Orang Tua</label>
                    <textarea name="alamat_orang_tua" id="edit_alamat_orang_tua" rows="2" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <hr class="sm:col-span-2 my-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Wali</label>
                    <input type="text" name="nama_wali" id="edit_nama_wali" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan Wali</label>
                    <input type="text" name="pekerjaan_wali" id="edit_pekerjaan_wali" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Handphone (WA)</label>
                    <input type="text" name="no_handphone" id="edit_no_handphone" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Misal: 081234567890">
                </div>
            </div>
        </div>

        <div class="border-t pt-5 flex justify-end space-x-3 bg-white sticky bottom-0 z-10">
            <button type="button" onclick="closeOffcanvas('offcanvas-edit-siswa')" class="px-5 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm hover:shadow-blue-500/30"><i class="ri-save-line mr-2"></i>Simpan Perubahan</button>
        </div>
    </form>
</div>

<!-- ================= MODAL OFFCANVAS IMPORT SANTRI ================= -->
<div id="overlay-import-siswa" class="fixed inset-0 bg-gray-900/60 z-40 hidden backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeOffcanvas('offcanvas-import-siswa')"></div>

<div id="offcanvas-import-siswa" class="fixed inset-y-0 right-0 z-50 w-full md:w-[450px] bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    
    <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-green-50 flex justify-between items-center">
        <div>
            <h3 class="text-xl font-extrabold text-gray-800">Import Data Santri</h3>
            <p class="text-sm text-gray-500 mt-1">Unggah file Excel (.xlsx)</p>
        </div>
        <button type="button" onclick="closeOffcanvas('offcanvas-import-siswa')" class="text-gray-400 bg-white hover:bg-gray-100 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center shadow-sm">
            <i class="ri-close-line text-2xl"></i>
        </button>
    </div>

    <div class="p-6 flex-1 overflow-y-auto flex flex-col items-center" id="import-step-1">
        
        <a href="template_santri.xlsx" target="_blank" hx-disable class="w-full inline-flex items-center justify-center px-4 py-3 mb-6 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-medium rounded-xl transition-colors duration-200 shadow-sm border border-emerald-200" download>
            <i class="ri-file-excel-2-fill text-xl mr-2"></i> Unduh Template Excel
        </a>

        <form enctype="multipart/form-data" id="formImportSantri" class="w-full space-y-6">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
            
            <div class="w-full">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih File Excel (.xlsx)</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-emerald-400 hover:bg-emerald-50/30 transition-all duration-200 group relative">
                    <div class="space-y-1 text-center">
                        <i class="ri-upload-cloud-2-line text-4xl text-gray-400 group-hover:text-emerald-500 transition-colors"></i>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="file_excel" class="relative cursor-pointer rounded-md font-medium text-emerald-600 hover:text-emerald-500 focus-within:outline-none">
                                <span>Telusuri File</span>
                                <input id="file_excel" name="file_excel" type="file" accept=".xlsx" class="sr-only" required onchange="document.getElementById('file-name').textContent = this.files[0].name">
                            </label>
                        </div>
                        <p class="text-xs text-gray-500" id="file-name">Maksimal ukuran file 5MB</p>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Preview Container (Hidden by default) -->
    <div class="p-6 flex-1 overflow-y-auto hidden flex-col w-full" id="import-step-2">
        <div class="mb-4">
            <h4 class="text-lg font-bold text-gray-800">Ringkasan Validasi</h4>
            <p class="text-sm text-gray-500" id="preview-summary"></p>
        </div>

        <!-- Invalid Data section -->
        <div id="invalid-data-container" class="hidden mb-6">
            <h5 class="text-sm font-bold text-red-600 mb-2"><i class="ri-error-warning-line mr-1"></i> Data Gagal / Ditolak</h5>
            <div class="bg-red-50 rounded-lg p-3 border border-red-100 max-h-48 overflow-y-auto">
                <ul class="text-xs text-red-700 space-y-2" id="invalid-data-list">
                    <!-- populated by JS -->
                </ul>
            </div>
        </div>

        <!-- Valid Data section -->
        <div id="valid-data-container" class="hidden">
            <h5 class="text-sm font-bold text-emerald-600 mb-2"><i class="ri-checkbox-circle-line mr-1"></i> Data Valid Siap Import</h5>
            <div class="bg-emerald-50 rounded-lg p-3 border border-emerald-100 max-h-64 overflow-y-auto">
                <table class="w-full text-xs text-left text-gray-700">
                    <thead class="text-gray-900 border-b border-emerald-200">
                        <tr>
                            <th class="py-2">Baris</th>
                            <th class="py-2">Nama</th>
                            <th class="py-2">No. Induk</th>
                        </tr>
                    </thead>
                    <tbody id="valid-data-tbody" class="divide-y divide-emerald-100">
                        <!-- populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="p-6 border-t border-gray-100 bg-gray-50" id="import-footer">
        <button type="button" id="btn-preview-import" onclick="previewImportSantri()" class="w-full text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-bold rounded-xl text-lg px-5 py-4 text-center transition-all shadow-lg shadow-emerald-500/30 flex items-center justify-center">
            <i class="ri-eye-line mr-2"></i> Preview Data
        </button>
        <button type="button" id="btn-confirm-import" onclick="confirmImportSantri()" class="hidden w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-xl text-lg px-5 py-4 text-center transition-all shadow-lg shadow-blue-500/30 flex items-center justify-center">
            <i class="ri-save-line mr-2"></i> Konfirmasi Import
        </button>
    </div>
</div>

<!-- ================= MODAL OFFCANVAS DETAIL SANTRI ================= -->
<div id="overlay-detail-siswa" class="fixed inset-0 bg-gray-900/60 z-40 hidden backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeOffcanvas('offcanvas-detail-siswa')"></div>

<div id="offcanvas-detail-siswa" class="fixed top-0 right-0 z-50 h-screen w-full max-w-2xl bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">
    <div class="sticky top-0 bg-white z-10 px-6 py-4 border-b flex justify-between items-center">
        <div>
            <h3 class="text-xl font-bold text-gray-800">Detail Data Santri</h3>
            <p class="text-sm text-gray-500 mt-1">Informasi lengkap santri terpilih.</p>
        </div>
        <button type="button" onclick="closeOffcanvas('offcanvas-detail-siswa')" class="text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-full p-2 transition-colors">
            <i class="ri-close-line text-2xl"></i>
        </button>
    </div>

    <div class="p-6">
        <!-- Bagian 1: Data Utama -->
        <div class="mb-8">
            <h4 class="text-md font-semibold text-emerald-700 mb-4 flex items-center border-b pb-2"><i class="ri-user-settings-line mr-2"></i> Data Utama</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><p class="text-sm text-gray-500">NISN</p><p id="det_nisn" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">No. Induk</p><p id="det_nomor_santri" class="font-medium text-gray-900">-</p></div>
                <div class="sm:col-span-2"><p class="text-sm text-gray-500">Nama Lengkap</p><p id="det_nama" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">Kelas</p><p id="det_id_kelas" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">Tahun Ajaran</p><p id="det_tahun_ajaran" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">Status Santri</p><p id="det_status" class="font-medium text-gray-900">-</p></div>
            </div>
        </div>

        <!-- Bagian 2: Data Pribadi -->
        <div class="mb-8">
            <h4 class="text-md font-semibold text-emerald-700 mb-4 flex items-center border-b pb-2"><i class="ri-profile-line mr-2"></i> Data Pribadi</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><p class="text-sm text-gray-500">Tempat Lahir</p><p id="det_tempat_lahir" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">Tanggal Lahir</p><p id="det_tanggal_lahir" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">Jenis Kelamin</p><p id="det_jenis_kelamin" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">Status dlm Keluarga</p><p id="det_status_dalam_keluarga" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">Anak Ke</p><p id="det_anak_ke" class="font-medium text-gray-900">-</p></div>
            </div>
        </div>

        <!-- Bagian 3: Alamat & Sekolah Asal -->
        <div class="mb-8">
            <h4 class="text-md font-semibold text-emerald-700 mb-4 flex items-center border-b pb-2"><i class="ri-map-pin-line mr-2"></i> Alamat & Asal Sekolah</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2"><p class="text-sm text-gray-500">Alamat Lengkap</p><p id="det_alamat" class="font-medium text-gray-900">-</p></div>
                <div class="sm:col-span-2"><p class="text-sm text-gray-500">Sekolah Asal</p><p id="det_sekolah_asal" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">Diterima di Kelas</p><p id="det_diterima_di_kelas" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">Diterima Tanggal</p><p id="det_diterima_pada_tanggal" class="font-medium text-gray-900">-</p></div>
            </div>
        </div>

        <!-- Bagian 4: Data Orang Tua / Wali -->
        <div class="mb-8">
            <h4 class="text-md font-semibold text-emerald-700 mb-4 flex items-center border-b pb-2"><i class="ri-parent-line mr-2"></i> Data Orang Tua & Wali</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><p class="text-sm text-gray-500">Nama Ayah</p><p id="det_nama_ayah" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">Pekerjaan Ayah</p><p id="det_pekerjaan_ayah" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">Nama Ibu</p><p id="det_nama_ibu" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">Pekerjaan Ibu</p><p id="det_pekerjaan_ibu" class="font-medium text-gray-900">-</p></div>
                <div class="sm:col-span-2"><p class="text-sm text-gray-500">Alamat Orang Tua</p><p id="det_alamat_orang_tua" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">Nama Wali</p><p id="det_nama_wali" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">Pekerjaan Wali</p><p id="det_pekerjaan_wali" class="font-medium text-gray-900">-</p></div>
                <div><p class="text-sm text-gray-500">No. Handphone</p><p id="det_no_handphone" class="font-medium text-gray-900">-</p></div>
            </div>
        </div>
    </div>
</div>

<!-- Tambahkan skrip RemixIcon bila perlu -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<script>
function openEditSantri(id) {
    // Tampilkan offcanvas dengan efek transisi
    openOffcanvas('offcanvas-edit-siswa');
    
    // Ambil data via AJAX
    fetch('get_santri_ajax.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const s = data.data;
                document.getElementById('edit_id_siswa').value = s.id_siswa;
                document.getElementById('edit_nisn').value = s.nisn || '';
                document.getElementById('edit_nama').value = s.nama || '';
                document.getElementById('edit_nomor_santri').value = s.nomor_santri || '';
                document.getElementById('edit_id_kelas').value = s.id_kelas || '';
                document.getElementById('edit_tahun_ajaran').value = s.tahun_ajaran || '';
                document.getElementById('edit_status').value = s.status || 'Aktif';
                
                document.getElementById('edit_tempat_lahir').value = s.tempat_lahir || '';
                document.getElementById('edit_tanggal_lahir').value = s.tanggal_lahir || '';
                document.getElementById('edit_jenis_kelamin').value = s.jenis_kelamin || 'L';
                document.getElementById('edit_status_dalam_keluarga').value = s.status_dalam_keluarga || '';
                document.getElementById('edit_anak_ke').value = s.anak_ke || '';
                
                document.getElementById('edit_alamat').value = s.alamat || '';
                document.getElementById('edit_sekolah_asal').value = s.sekolah_asal || '';
                document.getElementById('edit_diterima_di_kelas').value = s.diterima_di_kelas || '';
                document.getElementById('edit_diterima_pada_tanggal').value = s.diterima_pada_tanggal || '';
                
                document.getElementById('edit_nama_ayah').value = s.nama_ayah || '';
                document.getElementById('edit_nama_ibu').value = s.nama_ibu || '';
                document.getElementById('edit_pekerjaan_ayah').value = s.pekerjaan_ayah || '';
                document.getElementById('edit_pekerjaan_ibu').value = s.pekerjaan_ibu || '';
                document.getElementById('edit_alamat_orang_tua').value = s.alamat_orang_tua || '';
                
                document.getElementById('edit_nama_wali').value = s.nama_wali || '';
                document.getElementById('edit_pekerjaan_wali').value = s.pekerjaan_wali || '';
                document.getElementById('edit_no_handphone').value = s.no_handphone || '';
            } else {
                alert('Gagal mengambil data: ' + data.message);
                closeOffcanvas('offcanvas-edit-siswa');
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan jaringan.');
            closeOffcanvas('offcanvas-edit-siswa');
        });
}

function openDetailSantri(id) {
    openOffcanvas('offcanvas-detail-siswa');
    fetch('get_santri_ajax.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const s = data.data;
                const setText = (id, val) => document.getElementById(id).textContent = val || '-';
                setText('det_nisn', s.nisn);
                setText('det_nomor_santri', s.nomor_santri);
                setText('det_nama', s.nama);
                setText('det_id_kelas', s.nama_kelas || 'Belum Ada Kelas');
                setText('det_tahun_ajaran', s.tahun_ajaran);
                setText('det_status', s.status);
                setText('det_tempat_lahir', s.tempat_lahir);
                setText('det_tanggal_lahir', s.tanggal_lahir);
                setText('det_jenis_kelamin', s.jenis_kelamin === 'L' ? 'Laki-laki' : (s.jenis_kelamin === 'P' ? 'Perempuan' : s.jenis_kelamin));
                setText('det_status_dalam_keluarga', s.status_dalam_keluarga);
                setText('det_anak_ke', s.anak_ke);
                setText('det_alamat', s.alamat);
                setText('det_sekolah_asal', s.sekolah_asal);
                setText('det_diterima_di_kelas', s.diterima_di_kelas);
                setText('det_diterima_pada_tanggal', s.diterima_pada_tanggal);
                setText('det_nama_ayah', s.nama_ayah);
                setText('det_nama_ibu', s.nama_ibu);
                setText('det_pekerjaan_ayah', s.pekerjaan_ayah);
                setText('det_pekerjaan_ibu', s.pekerjaan_ibu);
                setText('det_alamat_orang_tua', s.alamat_orang_tua);
                setText('det_nama_wali', s.nama_wali);
                setText('det_pekerjaan_wali', s.pekerjaan_wali);
                setText('det_no_handphone', s.no_handphone);
            } else {
                alert('Gagal mengambil data: ' + data.message);
                closeOffcanvas('offcanvas-detail-siswa');
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan jaringan.');
            closeOffcanvas('offcanvas-detail-siswa');
        });
}
</script>


<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script>

  // Toggle Offcanvas functions
  function openOffcanvas(id) {
      const offcanvas = document.getElementById(id);
      const overlayId = id.replace('offcanvas', 'overlay');
      const overlay = document.getElementById(overlayId);
      
      overlay.classList.remove('hidden');
      // Force reflow
      void overlay.offsetWidth;
      overlay.classList.remove('opacity-0');
      
      offcanvas.classList.remove('translate-x-full');
  }

  function closeOffcanvas(id) {
      const offcanvas = document.getElementById(id);
      const overlayId = id.replace('offcanvas', 'overlay');
      const overlay = document.getElementById(overlayId);
      
      offcanvas.classList.add('translate-x-full');
      
      overlay.classList.add('opacity-0');
      setTimeout(() => {
          overlay.classList.add('hidden');
          // Reset import modal if closed
          if (id === 'offcanvas-import-siswa') {
              document.getElementById('import-step-1').classList.remove('hidden');
              document.getElementById('import-step-2').classList.add('hidden');
              document.getElementById('btn-preview-import').classList.remove('hidden');
              document.getElementById('btn-confirm-import').classList.add('hidden');
              document.getElementById('formImportSantri').reset();
              document.getElementById('file-name').textContent = 'Maksimal ukuran file 5MB';
          }
      }, 300);
  }

  // Import Validation Logic
  let validDataCache = [];

  function previewImportSantri() {
      const fileInput = document.getElementById('file_excel');
      if (fileInput.files.length === 0) {
          alert('Silakan pilih file Excel terlebih dahulu.');
          return;
      }

      const btn = document.getElementById('btn-preview-import');
      btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memvalidasi...';
      btn.disabled = true;

      const formData = new FormData(document.getElementById('formImportSantri'));

      fetch('preview_import_siswa.php', {
          method: 'POST',
          body: formData
      })
      .then(response => response.json())
      .then(data => {
          btn.innerHTML = '<i class="ri-eye-line mr-2"></i> Preview Data';
          btn.disabled = false;

          if (data.status === 'error') {
              alert(data.message);
              return;
          }

          validDataCache = data.valid;
          
          document.getElementById('import-step-1').classList.add('hidden');
          document.getElementById('import-step-2').classList.remove('hidden');
          document.getElementById('import-step-2').classList.add('flex');
          
          document.getElementById('btn-preview-import').classList.add('hidden');
          document.getElementById('btn-confirm-import').classList.remove('hidden');

          const summaryText = `<span class="text-emerald-600 font-bold">${data.valid.length} Valid</span> | <span class="text-red-600 font-bold">${data.invalid.length} Ditolak</span>`;
          document.getElementById('preview-summary').innerHTML = summaryText;

          // Render Invalid Data
          const invalidContainer = document.getElementById('invalid-data-container');
          const invalidList = document.getElementById('invalid-data-list');
          if (data.invalid.length > 0) {
              invalidContainer.classList.remove('hidden');
              invalidList.innerHTML = data.invalid.map(item => 
                  `<li><strong>Baris ${item.baris}:</strong> ${item.nama || '(Tanpa Nama)'} - <span class="font-semibold">${item.alasan}</span></li>`
              ).join('');
          } else {
              invalidContainer.classList.add('hidden');
          }

          // Render Valid Data
          const validContainer = document.getElementById('valid-data-container');
          const validTbody = document.getElementById('valid-data-tbody');
          if (data.valid.length > 0) {
              validContainer.classList.remove('hidden');
              validTbody.innerHTML = data.valid.map(item => 
                  `<tr>
                      <td class="py-2">${item.baris}</td>
                      <td class="py-2 font-medium">${item.nama}</td>
                      <td class="py-2">${item.nomor_santri}</td>
                  </tr>`
              ).join('');
              document.getElementById('btn-confirm-import').disabled = false;
          } else {
              validContainer.classList.add('hidden');
              document.getElementById('btn-confirm-import').disabled = true;
          }
      })
      .catch(error => {
          alert('Terjadi kesalahan sistem.');
          btn.innerHTML = '<i class="ri-eye-line mr-2"></i> Preview Data';
          btn.disabled = false;
      });
  }

  function confirmImportSantri() {
      if (validDataCache.length === 0) return;

      const btn = document.getElementById('btn-confirm-import');
      btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
      btn.disabled = true;

      const csrfToken = document.querySelector('input[name="csrf_token"]').value;

      fetch('proses_import_siswa.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify({
              csrf_token: csrfToken,
              import_data: validDataCache
          })
      })
      .then(response => response.json())
      .then(data => {
          if (data.status === 'success') {
              window.location.href = `data_santri.php?status=success&message=${data.message}`;
          } else {
              alert(data.message);
              btn.innerHTML = '<i class="ri-save-line mr-2"></i> Konfirmasi Import';
              btn.disabled = false;
          }
      })
      .catch(error => {
          alert('Terjadi kesalahan saat menyimpan data.');
          btn.innerHTML = '<i class="ri-save-line mr-2"></i> Konfirmasi Import';
          btn.disabled = false;
      });
  }
</script>

<?php include 'include/footer.php'; ?>