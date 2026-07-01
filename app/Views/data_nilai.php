<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_VIEW_ALL);
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// Ambil semua kelas
if ($_SESSION['peran'] === 'Wali Kelas') {
    $id_pengguna = $_SESSION['id_pengguna'];
    $queryKelas = "SELECT k.id_kelas, k.nama_kelas, k.nama_rombel, t.nama_tingkat 
                   FROM kelas k 
                   LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat 
                   WHERE k.id_wali_kelas = '$id_pengguna' 
                   ORDER BY t.nama_tingkat ASC, CAST(k.nama_kelas AS UNSIGNED) ASC, k.nama_rombel ASC";
} else {
    $queryKelas = "SELECT k.id_kelas, k.nama_kelas, k.nama_rombel, t.nama_tingkat 
                   FROM kelas k 
                   LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat 
                   ORDER BY t.nama_tingkat ASC, CAST(k.nama_kelas AS UNSIGNED) ASC, k.nama_rombel ASC";
}
$resultKelas = mysqli_query($koneksi, $queryKelas);
$kelasList = [];
while ($kelas = mysqli_fetch_assoc($resultKelas)) {
    $tingkatan_kategori = $kelas['nama_tingkat'] ?? '';
    $tingkatan_angka = $kelas['nama_kelas'] ?? '';
    $rombel_display = (!isset($kelas['nama_rombel']) || $kelas['nama_rombel'] === '-') ? '' : $kelas['nama_rombel'] . ' ';
    $singkatan_map = ['Ibtida\'iyah' => 'MIF', 'Tsanawiyah' => 'MTsF', 'Aliyah' => 'MAF'];
    $singkatan = $singkatan_map[$tingkatan_kategori] ?? $tingkatan_kategori;
    $kelas['nama_kelas_lengkap'] = trim($tingkatan_angka . ' ' . $rombel_display . $singkatan);
    if(empty($kelas['nama_kelas_lengkap'])) $kelas['nama_kelas_lengkap'] = 'Unknown';
    $kelasList[] = $kelas;
}

// Jika ada filter kelas yang dipilih
$selectedKelas = isset($_GET['kelas']) && $_GET['kelas'] !== "" ? $_GET['kelas'] : null;

if ($_SESSION['peran'] === 'Wali Kelas' && count($kelasList) > 0) {
    $selectedKelas = $kelasList[0]['id_kelas'];
}

// Jika ada filter semester yang dipilih
$selectedSemester = isset($_GET['semester']) && $_GET['semester'] !== "" ? $_GET['semester'] : null;

// Proses hapus data
if (isset($_GET['hapus'])) {
    if (!isset($_GET['csrf_token']) || !verify_csrf_token($_GET['csrf_token'])) {
        die("<script>alert('CSRF token validation failed'); window.location.href='data_nilai.php';</script>");
    }
    $id_transaksi = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    // Pencatatan Log Aktivitas (Standar AGENTS.md)
    $id_pengguna_log = $_SESSION['id_pengguna'] ?? 0;
    $aktivitas = "Menghapus data nilai (ID Transaksi: $id_transaksi)";
    $tabel_terkait = "transaksi_raport, nilai, absensi, kepribadian, catatan_wali_kelas";
    mysqli_query($koneksi, "INSERT INTO log_aktivitas (id_pengguna, aksi, detail) VALUES ('$id_pengguna_log', 'Hapus Data Nilai', '$aktivitas')");

    // Hapus data dari tabel terkait
    mysqli_query($koneksi, "DELETE FROM nilai WHERE id_transaksi = '$id_transaksi'");
    mysqli_query($koneksi, "DELETE FROM absensi WHERE id_transaksi = '$id_transaksi'");
    mysqli_query($koneksi, "DELETE FROM kepribadian WHERE id_transaksi = '$id_transaksi'");
    mysqli_query($koneksi, "DELETE FROM catatan_wali_kelas WHERE id_transaksi = '$id_transaksi'");
    mysqli_query($koneksi, "DELETE FROM transaksi_raport WHERE id_transaksi = '$id_transaksi'");

    echo "<script>
        alert('Data berhasil dihapus!');
        window.location.href='data_nilai.php';
    </script>";
}

// Tampilkan notifikasi jika ada
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'sukses') {
        echo "<div class='p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50' role='alert'>
                <span class='font-medium'>Berhasil!</span> Data berhasil diperbarui.
              </div>";
    } else if ($_GET['status'] == 'gagal') {
        echo "<div class='p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50' role='alert'>
                <span class='font-medium'>Gagal!</span> Terjadi kesalahan saat memperbarui data.
              </div>";
    }
}
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 border-2 border-transparent mt-14">
        
        <!-- Header Halaman -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Data Nilai Santri</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola dan filter data nilai, absensi, serta kepribadian santri</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
                <?php if ($_SESSION['peran'] !== 'Kepala Madrasah'): ?>
                <a href="tambah_nilai.php" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-sm text-sm">
                    <i class="fas fa-plus mr-1.5 text-base"></i> Tambah Nilai
                </a>
                <a href="import_nilai.php" class="inline-flex justify-center items-center px-4 py-2 text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 focus:ring-2 focus:ring-emerald-300 font-medium rounded-lg text-sm transition-colors shadow-sm">
                    <i class="fas fa-file-excel mr-1.5 text-base"></i> Import Excel
                </a>
                <?php endif; ?>
                <a href="export_nilai.php?kelas=<?= urlencode($selectedKelas ?? ''); ?>&semester=<?= urlencode($selectedSemester ?? ''); ?>" class="inline-flex justify-center items-center px-4 py-2 text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 focus:ring-2 focus:ring-indigo-300 font-medium rounded-lg text-sm transition-colors shadow-sm">
                    <i class="fas fa-file-excel mr-1.5 text-base"></i> Export Excel
                </a>
            </div>
        </div>

        <!-- Form Pencarian -->
        <div class="mb-6">
            <form method="GET" class="flex flex-col xl:flex-row gap-4 items-end bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
                <?php if ($_SESSION['peran'] !== 'Wali Kelas'): ?>
                <?php 
                $no_autosubmit = true;
                $id_kelas_selected = isset($id_kelas) ? $id_kelas : (isset($kelas_aktif) ? $kelas_aktif : 0); 
                include 'include/filter_kelas.php'; 
                ?>
                <?php endif; ?>

                <div class="flex-1 w-full min-w-[150px]">
                    <label for="semester" class="block text-sm font-bold text-gray-700 mb-2">Semester</label>
                    <select name="semester" id="semester" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm transition-colors cursor-pointer">
                        <option value="">Semua Semester</option>
                        <option value="1" <?= ($selectedSemester == '1') ? 'selected' : ''; ?>>Semester 1 (Ganjil)</option>
                        <option value="2" <?= ($selectedSemester == '2') ? 'selected' : ''; ?>>Semester 2 (Genap)</option>
                    </select>
                </div>

                <div class="flex-none w-full xl:w-auto flex gap-2">
                    <button type="submit" class="w-full xl:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md shadow-blue-500/30 transition-all flex justify-center items-center">
                        <i class="ri-search-line mr-2"></i> Tampilkan
                    </button>
                    <?php if($selectedKelas || $selectedSemester): ?>
                        <a href="data_nilai.php" class="w-full xl:w-auto px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors flex items-center justify-center shadow-sm">
                            <i class="ri-refresh-line mr-2"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php
        // Jika kelas tertentu dipilih, tampilkan hanya kelas tersebut
        if ($selectedKelas) {
            $kelasToShow = array_filter($kelasList, function ($kelas) use ($selectedKelas) {
                return $kelas['id_kelas'] == $selectedKelas;
            });
        } else {
            // Jika tidak ada kelas yang dipilih, minta user memilih dulu
            $kelasToShow = [];
            echo '<div class="flex flex-col items-center justify-center p-12 mt-4 bg-white border border-gray-100 rounded-2xl shadow-sm">';
            echo '<i class="ri-filter-3-line text-6xl text-blue-200 mb-4"></i>';
            echo '<h3 class="text-xl font-bold text-gray-700">Pilih Kelas Terlebih Dahulu</h3>';
            echo '<p class="text-gray-500 text-center mt-2 max-w-md">Silakan gunakan filter di atas untuk memilih Tingkat, Kelas, dan Rombel, kemudian klik <strong class="text-blue-600">Tampilkan</strong> untuk memuat data nilai.</p>';
            echo '</div>';
        }

        // Loop untuk setiap kelas
        foreach ($kelasToShow as $kelas):
            $id_kelas = $kelas['id_kelas'];
            $nama_kelas_lengkap = $kelas['nama_kelas_lengkap'];

            // Query untuk siswa di kelas ini
            $q_ta = mysqli_query($koneksi, "SELECT tahun_ajaran FROM pengaturan LIMIT 1");
            $ta_aktif = mysqli_fetch_assoc($q_ta)['tahun_ajaran'];

            // Query untuk siswa di kelas ini
            $whereClause = "WHERE r.id_kelas = '$id_kelas' AND r.tahun_ajaran = '$ta_aktif'";

            // Tambahkan filter semester jika dipilih
            if ($selectedSemester) {
                $whereClause .= " AND t.semester = '$selectedSemester'";
            }

            $query = "SELECT 
                        s.id_siswa, 
                        s.nama, 
                        s.nomor_santri, 
                        r.id_kelas, 
                        t.id_transaksi,
                        t.tahun_ajaran,
                        t.semester, 
                        s.nama_wali,
                        a.izin, a.sakit, a.tanpa_keterangan,
                        k.kelakuan, k.kerajinan, k.kerapian,
                        c.catatan
                            FROM riwayat_kelas r
                            JOIN siswa s ON r.id_siswa = s.id_siswa
                            LEFT JOIN transaksi_raport t ON s.id_siswa = t.id_siswa AND t.tahun_ajaran = r.tahun_ajaran
                            LEFT JOIN absensi a ON t.id_transaksi = a.id_transaksi
                            LEFT JOIN kepribadian k ON t.id_transaksi = k.id_transaksi
                            LEFT JOIN catatan_wali_kelas c ON t.id_transaksi = c.id_transaksi
                            $whereClause
                            ORDER BY s.nama ASC";

            $result = mysqli_query($koneksi, $query);

            // Cek apakah ada siswa di kelas ini
            if (mysqli_num_rows($result) > 0):
        ?>
                <div class="mb-8">
                    <h2 class="text-xl font-bold mb-4">Kelas: <?= htmlspecialchars($nama_kelas_lengkap); ?></h2>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="overflow-x-auto">
                        <?php
                        // Query untuk mata pelajaran di kelas ini
                        $queryMapel = "SELECT m.id_mapel, m.nama_mapel FROM mata_pelajaran m JOIN mapel_kelas mk ON m.id_mapel = mk.id_mapel WHERE mk.id_kelas = '$id_kelas' ORDER BY m.id_mapel";
                        $resultMapel = mysqli_query($koneksi, $queryMapel);
                        $mapelByKategori = [];
                        while ($mapel = mysqli_fetch_assoc($resultMapel)) {
                            $mapelByKategori['Mata Pelajaran'][] = $mapel;
                        }
                        ?>
                        <table id="santriTable_<?= $id_kelas; ?>" class="w-full text-sm text-left text-slate-600 border-collapse border border-slate-300">
                            <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                                <tr>
                                    <th rowspan="2" class="py-4 px-6 font-bold text-center border border-slate-300 w-16">No</th>
                                    <th rowspan="2" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">No Induk</th>
                                    <th rowspan="2" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Nama Santri</th>
                                    <th rowspan="2" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Semester</th>

                                    <!-- Generate kategori header -->
                                    <?php foreach ($mapelByKategori as $kategori => $mapels): ?>
                                        <th colspan="<?= count($mapels); ?>" class="py-4 px-6 font-bold border border-slate-300 text-center">
                                            <?= htmlspecialchars($kategori); ?>
                                        </th>
                                    <?php endforeach; ?>

                                    <th rowspan="2" class="py-4 px-6 font-bold border border-slate-300 text-center whitespace-nowrap">Jumlah</th>
                                    <th rowspan="2" class="py-4 px-6 font-bold border border-slate-300 text-center whitespace-nowrap">Rata-rata</th>
                                    <th colspan="3" class="py-4 px-6 font-bold border border-slate-300 text-center">Kepribadian</th>
                                    <th rowspan="2" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Catatan Wali Kelas</th>
                                    <?php if ($_SESSION['peran'] !== 'Kepala Madrasah'): ?>
                                    <th rowspan="2" class="py-4 px-6 font-bold border border-slate-300 text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                                <tr>
                                    <!-- Generate sub-header mapel -->
                                    <?php foreach ($mapelByKategori as $mapels): ?>
                                        <?php foreach ($mapels as $mapel): ?>
                                            <th class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap"><?= htmlspecialchars($mapel['nama_mapel']); ?></th>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>

                                    <th class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap text-center">Kelakuan</th>
                                    <th class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap text-center">Kerajinan</th>
                                    <th class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap text-center">Kerapihan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)):
                                    $idSiswa = $row['id_siswa'];
                                    $idTransaksi = $row['id_transaksi'];

                                    // Ambil nilai siswa dari tabel nilai
                                    $queryNilai = "SELECT id_mapel, nilai_angka FROM nilai 
                                                WHERE id_transaksi IN (SELECT id_transaksi FROM transaksi_raport WHERE id_siswa = $idSiswa)";
                                    $resultNilai = mysqli_query($koneksi, $queryNilai);

                                    $nilaiMapel = [];
                                    $totalNilai = 0;
                                    $jumlahMapel = 0;

                                    while ($nilai = mysqli_fetch_assoc($resultNilai)) {
                                        $nilaiMapel[$nilai['id_mapel']] = $nilai['nilai_angka'];
                                        $totalNilai += $nilai['nilai_angka'];
                                        $jumlahMapel++;
                                    }

                                    $rataRata = $jumlahMapel > 0 ? $totalNilai / $jumlahMapel : 0;
                                ?>
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="py-2 px-6 border border-slate-300 whitespace-nowrap text-center"><?= $no++; ?></td>
                                        <td class="py-2 px-6 border border-slate-300 whitespace-nowrap"><?= htmlspecialchars($row['nomor_santri']); ?></td>
                                        <td class="py-2 px-6 border border-slate-300 whitespace-nowrap font-medium"><?= htmlspecialchars($row['nama']); ?></td>
                                        <td class="py-2 px-6 border border-slate-300 whitespace-nowrap"><?= $row['semester'] ? 'Semester ' . $row['semester'] : '-'; ?></td>

                                        <!-- Generate nilai siswa -->
                                        <?php foreach ($mapelByKategori as $mapels): ?>
                                            <?php foreach ($mapels as $mapel): ?>
                                                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap text-center">
                                                    <?= isset($nilaiMapel[$mapel['id_mapel']]) ? $nilaiMapel[$mapel['id_mapel']] : '-'; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>

                                        <td class="py-2 px-6 border border-slate-300 whitespace-nowrap text-center font-bold text-blue-600"><?= $totalNilai; ?></td>
                                        <td class="py-2 px-6 border border-slate-300 whitespace-nowrap text-center font-bold text-emerald-600"><?= round($rataRata, 2); ?></td>

                                        <td class="py-2 px-6 border border-slate-300 whitespace-nowrap text-center"><?= htmlspecialchars($row['kelakuan'] ?? '-'); ?></td>
                                        <td class="py-2 px-6 border border-slate-300 whitespace-nowrap text-center"><?= htmlspecialchars($row['kerajinan'] ?? '-'); ?></td>
                                        <td class="py-2 px-6 border border-slate-300 whitespace-nowrap text-center"><?= htmlspecialchars($row['kerapian'] ?? '-'); ?></td>

                                        <td class="py-2 px-6 border border-slate-300 whitespace-nowrap text-slate-500 italic max-w-[200px] truncate" title="<?= htmlspecialchars($row['catatan'] ?? ''); ?>"><?= htmlspecialchars($row['catatan'] ?? '-'); ?></td>
                                        <?php if ($_SESSION['peran'] !== 'Kepala Madrasah' && $_SESSION['peran'] !== 'Wali Kelas'): ?>
                                        <td class="py-2 px-6 border border-slate-300 whitespace-nowrap text-center">
                                            <div class="flex justify-center space-x-1">
                                                <?php if ($idTransaksi): ?>
                                                    <a href="edit_nilai.php?id=<?= $idTransaksi; ?>" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-all duration-200" title="Edit">
                                                        <i class="ri-edit-line text-lg"></i>
                                                    </a>
                                                    <a href="data_nilai.php?hapus=<?= $idTransaksi; ?>&csrf_token=<?= generate_csrf_token(); ?>" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-all duration-200" onclick="return sweetConfirm(event, this, 'Apakah Anda yakin ingin menghapus data ini?');" title="Hapus">
                                                        <i class="ri-delete-bin-line text-lg"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="tambah_nilai.php?id_siswa=<?= $idSiswa; ?>" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-full transition-all duration-200" title="Tambah">
                                                        <i class="ri-add-line text-lg"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
        <?php
            endif;
        endforeach;
        ?>
    </div>
</div>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<?php include 'include/footer.php'; ?>