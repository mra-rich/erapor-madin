<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_GRADES);

// Cek apakah ada ID transaksi
if (!isset($_GET['id'])) {
    header("Location: data_nilai.php");
    exit;
}

$id_transaksi = $_GET['id'];

// Ambil data transaksi
$query = "SELECT 
            t.id_transaksi,
            t.id_siswa,
            t.tahun_ajaran,
            s.nama,
            s.nomor_santri,
            s.id_kelas,
            k.nama_kelas
          FROM transaksi_raport t
          JOIN siswa s ON t.id_siswa = s.id_siswa
          JOIN kelas k ON s.id_kelas = k.id_kelas
          WHERE t.id_transaksi = '$id_transaksi'";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: data_nilai.php");
    exit;
}

// Ambil data nilai
$queryNilai = "SELECT id_mapel, nilai_angka FROM nilai WHERE id_transaksi = '$id_transaksi'";
$resultNilai = mysqli_query($koneksi, $queryNilai);
$nilaiMapel = [];
while ($nilai = mysqli_fetch_assoc($resultNilai)) {
    $nilaiMapel[$nilai['id_mapel']] = $nilai['nilai_angka'];
}

// Ambil data absensi
$queryAbsensi = "SELECT izin, sakit, tanpa_keterangan FROM absensi WHERE id_transaksi = '$id_transaksi'";
$resultAbsensi = mysqli_query($koneksi, $queryAbsensi);
$absensi = mysqli_fetch_assoc($resultAbsensi);

// Ambil data kepribadian
$queryKepribadian = "SELECT kelakuan, kerajinan, kerapian FROM kepribadian WHERE id_transaksi = '$id_transaksi'";
$resultKepribadian = mysqli_query($koneksi, $queryKepribadian);
$kepribadian = mysqli_fetch_assoc($resultKepribadian);

// Ambil data catatan wali kelas
$queryCatatan = "SELECT catatan FROM catatan_wali_kelas WHERE id_transaksi = '$id_transaksi'";
$resultCatatan = mysqli_query($koneksi, $queryCatatan);
$catatan = mysqli_fetch_assoc($resultCatatan);

// Ambil mata pelajaran untuk kelas ini
$id_kelas = $data['id_kelas'];
$queryMapel = "SELECT id_mapel, nama_mapel, kategori FROM mata_pelajaran WHERE id_kelas = '$id_kelas' ORDER BY kategori";
$resultMapel = mysqli_query($koneksi, $queryMapel);
$mapelByKategori = [];
while ($mapel = mysqli_fetch_assoc($resultMapel)) {
    $mapelByKategori[$mapel['kategori']][] = $mapel;
}

// Proses update data
if (isset($_POST['update'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }
    $tahun_ajaran = $_POST['tahun_ajaran'];

    // Memulai Transaction
    mysqli_begin_transaction($koneksi);

    try {
        // Update transaksi_raport
        $stmt = mysqli_prepare($koneksi, "UPDATE transaksi_raport SET tahun_ajaran = ? WHERE id_transaksi = ?");
        if (!$stmt) throw new Exception(mysqli_error($koneksi));
        mysqli_stmt_bind_param($stmt, "si", $tahun_ajaran, $id_transaksi);
        if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);

        // Update nilai
        foreach ($_POST['nilai'] as $id_mapel => $nilai) {
            if ($nilai !== '') {
                $id_mapel = intval($id_mapel);
                $nilai = intval($nilai);

                // Cek apakah nilai sudah ada
                $stmt_check = mysqli_prepare($koneksi, "SELECT id_nilai FROM nilai WHERE id_transaksi = ? AND id_mapel = ?");
                if (!$stmt_check) throw new Exception(mysqli_error($koneksi));
                mysqli_stmt_bind_param($stmt_check, "ii", $id_transaksi, $id_mapel);
                mysqli_stmt_execute($stmt_check);
                mysqli_stmt_store_result($stmt_check);
                
                if (mysqli_stmt_num_rows($stmt_check) > 0) {
                    // Update nilai yang sudah ada
                    $stmt_upd = mysqli_prepare($koneksi, "UPDATE nilai SET nilai_angka = ? WHERE id_transaksi = ? AND id_mapel = ?");
                    if (!$stmt_upd) throw new Exception(mysqli_error($koneksi));
                    mysqli_stmt_bind_param($stmt_upd, "iii", $nilai, $id_transaksi, $id_mapel);
                    if (!mysqli_stmt_execute($stmt_upd)) throw new Exception(mysqli_stmt_error($stmt_upd));
                    mysqli_stmt_close($stmt_upd);
                } else {
                    // Tambah nilai baru
                    $stmt_ins = mysqli_prepare($koneksi, "INSERT INTO nilai (id_transaksi, id_mapel, nilai_angka) VALUES (?, ?, ?)");
                    if (!$stmt_ins) throw new Exception(mysqli_error($koneksi));
                    mysqli_stmt_bind_param($stmt_ins, "iii", $id_transaksi, $id_mapel, $nilai);
                    if (!mysqli_stmt_execute($stmt_ins)) throw new Exception(mysqli_stmt_error($stmt_ins));
                    mysqli_stmt_close($stmt_ins);
                }
                mysqli_stmt_close($stmt_check);
            }
        }

        // Update absensi
        $izin = intval($_POST['izin'] ?? 0);
        $sakit = intval($_POST['sakit'] ?? 0);
        $tanpa_keterangan = intval($_POST['tanpa_keterangan'] ?? 0);

        $stmt_abs_chk = mysqli_prepare($koneksi, "SELECT id_absensi FROM absensi WHERE id_transaksi = ?");
        if (!$stmt_abs_chk) throw new Exception(mysqli_error($koneksi));
        mysqli_stmt_bind_param($stmt_abs_chk, "i", $id_transaksi);
        mysqli_stmt_execute($stmt_abs_chk);
        mysqli_stmt_store_result($stmt_abs_chk);

        if (mysqli_stmt_num_rows($stmt_abs_chk) > 0) {
            $stmt_abs_upd = mysqli_prepare($koneksi, "UPDATE absensi SET izin = ?, sakit = ?, tanpa_keterangan = ? WHERE id_transaksi = ?");
            if (!$stmt_abs_upd) throw new Exception(mysqli_error($koneksi));
            mysqli_stmt_bind_param($stmt_abs_upd, "iiii", $izin, $sakit, $tanpa_keterangan, $id_transaksi);
            if (!mysqli_stmt_execute($stmt_abs_upd)) throw new Exception(mysqli_stmt_error($stmt_abs_upd));
            mysqli_stmt_close($stmt_abs_upd);
        } else {
            $stmt_abs_ins = mysqli_prepare($koneksi, "INSERT INTO absensi (id_transaksi, izin, sakit, tanpa_keterangan) VALUES (?, ?, ?, ?)");
            if (!$stmt_abs_ins) throw new Exception(mysqli_error($koneksi));
            mysqli_stmt_bind_param($stmt_abs_ins, "iiii", $id_transaksi, $izin, $sakit, $tanpa_keterangan);
            if (!mysqli_stmt_execute($stmt_abs_ins)) throw new Exception(mysqli_stmt_error($stmt_abs_ins));
            mysqli_stmt_close($stmt_abs_ins);
        }
        mysqli_stmt_close($stmt_abs_chk);

        // Update kepribadian
        $kelakuan = trim($_POST['kelakuan'] ?? '');
        $kerajinan = trim($_POST['kerajinan'] ?? '');
        $kerapian = trim($_POST['kerapian'] ?? '');

        $stmt_kep_chk = mysqli_prepare($koneksi, "SELECT id_kepribadian FROM kepribadian WHERE id_transaksi = ?");
        if (!$stmt_kep_chk) throw new Exception(mysqli_error($koneksi));
        mysqli_stmt_bind_param($stmt_kep_chk, "i", $id_transaksi);
        mysqli_stmt_execute($stmt_kep_chk);
        mysqli_stmt_store_result($stmt_kep_chk);

        if (mysqli_stmt_num_rows($stmt_kep_chk) > 0) {
            $stmt_kep_upd = mysqli_prepare($koneksi, "UPDATE kepribadian SET kelakuan = ?, kerajinan = ?, kerapian = ? WHERE id_transaksi = ?");
            if (!$stmt_kep_upd) throw new Exception(mysqli_error($koneksi));
            mysqli_stmt_bind_param($stmt_kep_upd, "sssi", $kelakuan, $kerajinan, $kerapian, $id_transaksi);
            if (!mysqli_stmt_execute($stmt_kep_upd)) throw new Exception(mysqli_stmt_error($stmt_kep_upd));
            mysqli_stmt_close($stmt_kep_upd);
        } else {
            $stmt_kep_ins = mysqli_prepare($koneksi, "INSERT INTO kepribadian (id_transaksi, kelakuan, kerajinan, kerapian) VALUES (?, ?, ?, ?)");
            if (!$stmt_kep_ins) throw new Exception(mysqli_error($koneksi));
            mysqli_stmt_bind_param($stmt_kep_ins, "isss", $id_transaksi, $kelakuan, $kerajinan, $kerapian);
            if (!mysqli_stmt_execute($stmt_kep_ins)) throw new Exception(mysqli_stmt_error($stmt_kep_ins));
            mysqli_stmt_close($stmt_kep_ins);
        }
        mysqli_stmt_close($stmt_kep_chk);

        // Update catatan wali kelas
        $catatan = trim($_POST['catatan'] ?? '');

        $stmt_cat_chk = mysqli_prepare($koneksi, "SELECT id_catatan FROM catatan_wali_kelas WHERE id_transaksi = ?");
        if (!$stmt_cat_chk) throw new Exception(mysqli_error($koneksi));
        mysqli_stmt_bind_param($stmt_cat_chk, "i", $id_transaksi);
        mysqli_stmt_execute($stmt_cat_chk);
        mysqli_stmt_store_result($stmt_cat_chk);

        if (mysqli_stmt_num_rows($stmt_cat_chk) > 0) {
            $stmt_cat_upd = mysqli_prepare($koneksi, "UPDATE catatan_wali_kelas SET catatan = ? WHERE id_transaksi = ?");
            if (!$stmt_cat_upd) throw new Exception(mysqli_error($koneksi));
            mysqli_stmt_bind_param($stmt_cat_upd, "si", $catatan, $id_transaksi);
            if (!mysqli_stmt_execute($stmt_cat_upd)) throw new Exception(mysqli_stmt_error($stmt_cat_upd));
            mysqli_stmt_close($stmt_cat_upd);
        } else {
            $stmt_cat_ins = mysqli_prepare($koneksi, "INSERT INTO catatan_wali_kelas (id_transaksi, catatan) VALUES (?, ?)");
            if (!$stmt_cat_ins) throw new Exception(mysqli_error($koneksi));
            mysqli_stmt_bind_param($stmt_cat_ins, "is", $id_transaksi, $catatan);
            if (!mysqli_stmt_execute($stmt_cat_ins)) throw new Exception(mysqli_stmt_error($stmt_cat_ins));
            mysqli_stmt_close($stmt_cat_ins);
        }
        mysqli_stmt_close($stmt_cat_chk);

        // Jika semua berhasil, eksekusi mutlak penyimpanan
        mysqli_commit($koneksi);

        // Redirect dengan status sukses
        header("Location: data_nilai.php?status=sukses");
        exit;

    } catch (Exception $e) {
        // Jika ada query yang gagal di tengah jalan, segera tarik kembali semuanya
        mysqli_rollback($koneksi);
        
        // Simpan pesan error di session agar bisa dimunculkan di notifikasi toast frontend
        $_SESSION['error'] = "Terjadi kegagalan saat menyimpan data nilai. Seluruh proses pengeditan dibatalkan untuk menghindari anomali. Detail error: " . $e->getMessage();
        
        // Redirect ulang ke halaman form nilai tanpa status sukses
        header("Location: edit_nilai.php?id=" . $id_transaksi);
        exit;
    }
}

// Setelah semua operasi header, baru include file HTML
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-14">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div class="text-xl font-bold text-gray-800 mb-2 md:mb-0">Edit Nilai Santri</div>
            <div class="text-sm font-medium text-gray-800">
                <?php
                $dayList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                $monthList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                echo $dayList[date('w')] . ', ' . date('j') . ' ' . $monthList[date('n') - 1] . ' ' . date('Y');
                ?>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-2">Informasi Santri</h2>
                <p><span class="font-medium">Nama:</span> <?= htmlspecialchars($data['nama']); ?></p>
                <p><span class="font-medium">No Induk:</span> <?= htmlspecialchars($data['nomor_santri']); ?></p>
                <p><span class="font-medium">Kelas:</span> <?= htmlspecialchars($data['nama_kelas']); ?></p>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                <div class="mb-6">
                    <label for="tahun_ajaran" class="block text-sm font-medium text-gray-700 mb-2">Tahun Ajaran</label>
                    <input type="text" id="tahun_ajaran" name="tahun_ajaran" value="<?= htmlspecialchars($data['tahun_ajaran']); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                </div>

                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-4">Nilai Mata Pelajaran</h2>

                    <?php foreach ($mapelByKategori as $kategori => $mapels): ?>
                        <div class="mb-4">
                            <h3 class="text-md font-medium mb-2"><?= htmlspecialchars($kategori); ?></h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <?php foreach ($mapels as $mapel): ?>
                                    <div>
                                        <label for="nilai_<?= $mapel['id_mapel']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                            <?= htmlspecialchars($mapel['nama_mapel']); ?>
                                        </label>
                                        <input type="number" id="nilai_<?= $mapel['id_mapel']; ?>" name="nilai[<?= $mapel['id_mapel']; ?>]"
                                            value="<?= isset($nilaiMapel[$mapel['id_mapel']]) ? $nilaiMapel[$mapel['id_mapel']] : ''; ?>"
                                            min="0" max="100" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-4">Absensi</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="izin" class="block text-sm font-medium text-gray-700 mb-1">Izin</label>
                            <input type="number" id="izin" name="izin" value="<?= $absensi ? $absensi['izin'] : '0'; ?>" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label for="sakit" class="block text-sm font-medium text-gray-700 mb-1">Sakit</label>
                            <input type="number" id="sakit" name="sakit" value="<?= $absensi ? $absensi['sakit'] : '0'; ?>" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label for="tanpa_keterangan" class="block text-sm font-medium text-gray-700 mb-1">Tanpa Keterangan</label>
                            <input type="number" id="tanpa_keterangan" name="tanpa_keterangan" value="<?= $absensi ? $absensi['tanpa_keterangan'] : '0'; ?>" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-4">Kepribadian</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="kelakuan" class="block text-sm font-medium text-gray-700 mb-1">Kelakuan</label>
                            <input type="text" id="kelakuan" name="kelakuan" value="<?= $kepribadian ? $kepribadian['kelakuan'] : ''; ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label for="kerajinan" class="block text-sm font-medium text-gray-700 mb-1">Kerajinan</label>
                            <input type="text" id="kerajinan" name="kerajinan" value="<?= $kepribadian ? $kepribadian['kerajinan'] : ''; ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label for="kerapian" class="block text-sm font-medium text-gray-700 mb-1">Kerapian</label>
                            <input type="text" id="kerapian" name="kerapian" value="<?= $kepribadian ? $kepribadian['kerapian'] : ''; ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="catatan" class="block text-sm font-medium text-gray-700 mb-2">Catatan Wali Kelas</label>
                    <textarea id="catatan" name="catatan" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"><?= $catatan ? $catatan['catatan'] : ''; ?></textarea>
                </div>

                <div class="flex justify-end">
                    <a href="data_nilai.php" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg mr-2">Batal</a>
                    <button type="submit" name="update" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'include/footer.php'; ?>