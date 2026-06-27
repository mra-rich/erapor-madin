<?php
require 'koneksi.php';
require 'cek_sesi.php';
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// Ambil semua kelas
$queryKelas = "SELECT id_kelas, nama_kelas FROM kelas ORDER BY nama_kelas";
$resultKelas = mysqli_query($koneksi, $queryKelas);
$kelasList = [];
while ($kelas = mysqli_fetch_assoc($resultKelas)) {
    $kelasList[] = $kelas;
}

// Jika ada filter kelas yang dipilih
$selectedKelas = isset($_GET['kelas']) && $_GET['kelas'] !== "" ? $_GET['kelas'] : null;

// Jika ada filter semester yang dipilih
$selectedSemester = isset($_GET['semester']) && $_GET['semester'] !== "" ? $_GET['semester'] : null;

// Proses hapus data
if (isset($_GET['hapus'])) {
    $id_transaksi = $_GET['hapus'];

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
    <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-14">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div class="text-xl font-bold text-gray-800 mb-2 md:mb-0">Data Nilai Santri</div>
            <div class="text-sm font-medium text-gray-800">
                <?php
                $dayList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                $monthList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                echo $dayList[date('w')] . ', ' . date('j') . ' ' . $monthList[date('n') - 1] . ' ' . date('Y');
                ?>
            </div>
        </div>

        <div class="mb-4">
            <a href="tambah_nilai.php"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i> Tambah Nilai
            </a>
        </div>

        <div class="mb-4">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label for="kelas" class="block font-medium mb-1">Pilih Kelas:</label>
                    <select name="kelas" id="kelas" class="px-8 py-2 border rounded">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($kelasList as $kelas): ?>
                            <option value="<?= $kelas['id_kelas']; ?>" <?= ($selectedKelas == $kelas['id_kelas']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($kelas['nama_kelas']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="semester" class="block font-medium mb-1">Pilih Semester:</label>
                    <select name="semester" id="semester" class="px-8 py-2 border rounded">
                        <option value="">Semua Semester</option>
                        <option value="1" <?= ($selectedSemester == '1') ? 'selected' : ''; ?>>Semester 1</option>
                        <option value="2" <?= ($selectedSemester == '2') ? 'selected' : ''; ?>>Semester 2</option>
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </form>
        </div>

        <?php
        // Jika kelas tertentu dipilih, tampilkan hanya kelas tersebut
        if ($selectedKelas) {
            $kelasToShow = array_filter($kelasList, function ($kelas) use ($selectedKelas) {
                return $kelas['id_kelas'] == $selectedKelas;
            });
        } else {
            // Jika tidak ada kelas yang dipilih, tampilkan semua kelas
            $kelasToShow = $kelasList;
        }

        // Loop untuk setiap kelas
        foreach ($kelasToShow as $kelas):
            $id_kelas = $kelas['id_kelas'];
            $nama_kelas = $kelas['nama_kelas'];

            // Query untuk siswa di kelas ini
            $whereClause = "WHERE s.id_kelas = '$id_kelas'";

            // Tambahkan filter semester jika dipilih
            if ($selectedSemester) {
                $whereClause .= " AND t.semester = '$selectedSemester'";
            }

            $query = "SELECT 
                        s.id_siswa, 
                        s.nama, 
                        s.nomor_santri, 
                        s.id_kelas, 
                        t.id_transaksi,
                        t.tahun_ajaran,
                        t.semester, 
                        s.nama_wali,
                        a.izin, a.sakit, a.tanpa_keterangan,
                        k.kelakuan, k.kerajinan, k.kerapian,
                        c.catatan
                            FROM siswa s
                            LEFT JOIN transaksi_raport t ON s.id_siswa = t.id_siswa
                            LEFT JOIN absensi a ON t.id_transaksi = a.id_transaksi
                            LEFT JOIN kepribadian k ON t.id_transaksi = k.id_transaksi
                            LEFT JOIN catatan_wali_kelas c ON t.id_transaksi = c.id_transaksi
                            $whereClause
                            ORDER BY s.id_siswa DESC";

            $result = mysqli_query($koneksi, $query);

            // Cek apakah ada siswa di kelas ini
            if (mysqli_num_rows($result) > 0):
        ?>
                <div class="mb-8">
                    <h2 class="text-xl font-bold mb-4">Kelas: <?= htmlspecialchars($nama_kelas); ?></h2>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg p-5">
                        <?php
                        // Query untuk mata pelajaran di kelas ini
                        $queryMapel = "SELECT id_mapel, nama_mapel, kategori FROM mata_pelajaran WHERE id_kelas = '$id_kelas' ORDER BY kategori";
                        $resultMapel = mysqli_query($koneksi, $queryMapel);
                        $mapelByKategori = [];
                        while ($mapel = mysqli_fetch_assoc($resultMapel)) {
                            $mapelByKategori[$mapel['kategori']][] = $mapel;
                        }
                        ?>

                        <table id="santriTable_<?= $id_kelas; ?>" class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th rowspan="2" class="px-4 py-2">No</th>
                                    <th rowspan="2" class="px-4 py-2">No Induk</th>
                                    <th rowspan="2" class="px-4 py-2">Nama Santri</th>
                                    <th rowspan="2" class="px-4 py-2">Semester</th>

                                    <!-- Generate kategori header -->
                                    <?php foreach ($mapelByKategori as $kategori => $mapels): ?>
                                        <th colspan="<?= count($mapels); ?>" class="px-4 py-2 text-center">
                                            <?= htmlspecialchars($kategori); ?>
                                        </th>
                                    <?php endforeach; ?>

                                    <th rowspan="2" class="px-4 py-2">Jumlah</th>
                                    <th rowspan="2" class="px-4 py-2">Rata-rata</th>
                                    <th colspan="3" class="px-4 py-2 text-center">Kepribadian</th>
                                    <th rowspan="2" class="px-4 py-2">Catatan Wali Kelas</th>
                                    <th rowspan="2" class="px-4 py-2">Aksi</th>
                                </tr>
                                <tr>
                                    <!-- Generate sub-header mapel -->
                                    <?php foreach ($mapelByKategori as $mapels): ?>
                                        <?php foreach ($mapels as $mapel): ?>
                                            <th class="px-4 py-2"><?= htmlspecialchars($mapel['nama_mapel']); ?></th>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>

                                    <th class="px-4 py-2">Kelakuan</th>
                                    <th class="px-4 py-2">Kerajinan</th>
                                    <th class="px-4 py-2">Kerapihan</th>
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
                                    <tr class="bg-white">
                                        <td class="px-4 py-2"><?= $no++; ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($row['nomor_santri']); ?></td>
                                        <td class="px-4 py-2 font-medium"><?= htmlspecialchars($row['nama']); ?></td>
                                        <td class="px-4 py-2"><?= $row['semester'] ? 'Semester ' . $row['semester'] : '-'; ?></td>

                                        <!-- Generate nilai siswa -->
                                        <?php foreach ($mapelByKategori as $mapels): ?>
                                            <?php foreach ($mapels as $mapel): ?>
                                                <td class="px-4 py-2">
                                                    <?= isset($nilaiMapel[$mapel['id_mapel']]) ? $nilaiMapel[$mapel['id_mapel']] : '-'; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>

                                        <td class="px-4 py-2"><?= $totalNilai; ?></td>
                                        <td class="px-4 py-2"><?= round($rataRata, 2); ?></td>

                                        <td class="px-4 py-2"><?= htmlspecialchars($row['kelakuan'] ?? '-'); ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($row['kerajinan'] ?? '-'); ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($row['kerapian'] ?? '-'); ?></td>

                                        <td class="px-4 py-2"><?= htmlspecialchars($row['catatan'] ?? '-'); ?></td>
                                        <td class="px-4 py-2">
                                            <div class="flex items-center space-x-2">
                                                <?php if ($idTransaksi): ?>
                                                    <a href="edit_nilai.php?id=<?= $idTransaksi; ?>" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="data_nilai.php?hapus=<?= $idTransaksi; ?>" class="text-red-600 dark:text-red-400 hover:underline" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                        <i class="fas fa-trash-alt"></i> Hapus
                                                    </a>
                                                <?php else: ?>
                                                    <a href="tambah_nilai.php?id_siswa=<?= $idSiswa; ?>" class="text-green-600 dark:text-green-400 hover:underline">
                                                        <i class="fas fa-plus"></i> Tambah
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
        <?php
            endif;
        endforeach;
        ?>
    </div>
</div>

<!-- DataTables & FontAwesome -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php foreach ($kelasToShow as $kelas): ?>
            new simpleDatatables.DataTable("#santriTable_<?= $kelas['id_kelas']; ?>");
        <?php endforeach; ?>
    });
</script>

<?php include 'include/footer.php'; ?>