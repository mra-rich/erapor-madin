<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_VIEW_REPORTS);

// Ambil semester aktif
$q_pengaturan = mysqli_query($koneksi, "SELECT semester FROM pengaturan LIMIT 1");
$pengaturan_aktif = mysqli_fetch_assoc($q_pengaturan);
$semester_aktif = intval($pengaturan_aktif['semester'] ?? 1);

include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// Ambil semua kelas untuk tujuan
$queryKelasTujuan = "SELECT id_kelas, nama_kelas FROM kelas WHERE status = 'Aktif' ORDER BY nama_kelas";
$resultKelasTujuan = mysqli_query($koneksi, $queryKelasTujuan);
$kelasListTujuan = [];
while ($kelas = mysqli_fetch_assoc($resultKelasTujuan)) {
    $kelasListTujuan[] = $kelas;
}

// Ambil kelas asal (Wali Kelas hanya melihat kelasnya sendiri, Admin/Kepsek lihat semua)
$queryKelasAsal = "";
if ($_SESSION['peran'] === 'Wali Kelas') {
    $id_pengguna = (int)$_SESSION['id_pengguna'];
    // id_wali_kelas di tabel kelas menyimpan id_pengguna langsung
    $queryKelasAsal = "SELECT id_kelas, nama_kelas FROM kelas WHERE id_wali_kelas = '$id_pengguna' ORDER BY nama_kelas";
} else {
    $queryKelasAsal = "SELECT id_kelas, nama_kelas FROM kelas WHERE status = 'Aktif' ORDER BY nama_kelas";
}
$resultKelasAsal = mysqli_query($koneksi, $queryKelasAsal);
$kelasListAsal = [];
while ($kelas = mysqli_fetch_assoc($resultKelasAsal)) {
    $kelasListAsal[] = $kelas;
}


$id_kelas_asal = isset($_GET['kelas_asal']) ? intval($_GET['kelas_asal']) : 0;

// Jika Wali Kelas, auto-set kelas asal ke kelas sendiri
if ($_SESSION['peran'] === 'Wali Kelas' && count($kelasListAsal) > 0) {
    $id_kelas_asal = $kelasListAsal[0]['id_kelas'];
}
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 border-2 border-transparent mt-14">
        <!-- Header Halaman -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Kenaikan Kelas Massal</h1>
                <p class="text-sm text-gray-500 mt-1">Pindahkan siswa ke kelas tingkat berikutnya secara bersamaan.</p>
            </div>
        </div>

        <?php if ($semester_aktif == 1): ?>
        <!-- BLOKIR: Semester Ganjil -->
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-10 max-w-lg shadow">
                <i class="ri-lock-line text-6xl text-amber-400 mb-4"></i>
                <h2 class="text-xl font-bold text-amber-800 mb-2">Fitur Tidak Tersedia di Semester Ganjil</h2>
                <p class="text-amber-700 text-sm leading-relaxed">
                    Kenaikan kelas hanya dapat diproses pada <strong>Semester 2 (Genap)</strong>, yaitu di akhir tahun ajaran.<br><br>
                    Saat ini semester aktif adalah <strong>Semester 1 (Ganjil)</strong>. Silakan ganti semester terlebih dahulu melalui menu <strong>Identitas Madrasah</strong> jika ingin memproses kenaikan kelas.
                </p>
                <a href="identitas_madrasah" class="mt-6 inline-flex items-center px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl transition-colors">
                    <i class="ri-settings-4-line mr-2"></i> Buka Pengaturan
                </a>
            </div>
        </div>
        <?php else: ?>
            <?php if ($id_kelas_asal > 0): ?>
            <div class="px-5 py-3 bg-emerald-50/60 rounded-2xl border border-emerald-100 shadow-sm w-full sm:w-auto">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-emerald-800 font-bold text-sm whitespace-nowrap"><i class="ri-arrow-up-circle-line mr-1"></i> Naik Kelas ke- :</span>
                    <div class="flex flex-wrap gap-2">
                        <?php 
                        $id_kelas_selected = 0;
                        
                        // PREDIKSI OTOMATIS KELAS TUJUAN (Berdasarkan Kelas Asal)
                        if ($id_kelas_asal > 0) {
                            $q_asal = mysqli_query($koneksi, "SELECT id_tingkat, nama_kelas, nama_rombel FROM kelas WHERE id_kelas = $id_kelas_asal");
                            if ($asal = mysqli_fetch_assoc($q_asal)) {
                                $asal_tingkat = intval($asal['id_tingkat']);
                                $asal_nama_kelas = intval($asal['nama_kelas']);
                                $asal_rombel = mysqli_real_escape_string($koneksi, $asal['nama_rombel']);
                                
                                // Skenario 1: Naik 1 angka di tingkat yang sama
                                $tujuan_tingkat = $asal_tingkat;
                                $tujuan_nama_kelas = $asal_nama_kelas + 1;
                                $cek = mysqli_query($koneksi, "SELECT id_kelas FROM kelas WHERE id_tingkat = $tujuan_tingkat AND nama_kelas = '$tujuan_nama_kelas' AND nama_rombel = '$asal_rombel' AND status='Aktif' LIMIT 1");
                                
                                if (mysqli_num_rows($cek) > 0) {
                                    $row = mysqli_fetch_assoc($cek);
                                    $id_kelas_selected = $row['id_kelas'];
                                } else {
                                    // Skenario 2: Lulus tingkat ini
                                    $tujuan_tingkat = $asal_tingkat + 1;
                                    $tujuan_nama_kelas = 1;
                                    $cek2 = mysqli_query($koneksi, "SELECT id_kelas FROM kelas WHERE id_tingkat = $tujuan_tingkat AND nama_kelas = '$tujuan_nama_kelas' AND nama_rombel = '$asal_rombel' AND status='Aktif' LIMIT 1");
                                    if (mysqli_num_rows($cek2) > 0) {
                                        $row2 = mysqli_fetch_assoc($cek2);
                                        $id_kelas_selected = $row2['id_kelas'];
                                    }
                                }
                            }
                        }

                        $filter_prefix = 'f2';
                        $filter_name = 'kelas_tujuan';
                        $no_autosubmit = true;
                        $hide_labels = true;
                        $form_id = 'formKenaikan';
                        include 'include/filter_kelas.php'; 
                        ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="p-4 mb-4 text-sm <?php echo $_GET['status'] == 'success' ? 'text-emerald-800 bg-emerald-50' : 'text-red-800 bg-red-50'; ?> rounded-xl border <?php echo $_GET['status'] == 'success' ? 'border-emerald-200' : 'border-red-200'; ?>" role="alert">
                <span class="font-medium"><?php echo $_GET['status'] == 'success' ? 'Berhasil!' : 'Gagal!'; ?></span> <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Form Pencarian Kelas (hanya tampil untuk Admin/Kepala Madrasah) -->
        <?php if ($_SESSION['peran'] !== 'Wali Kelas'): ?>
        <div class="mb-6">
            <form method="GET" class="flex flex-wrap gap-4 items-end bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
                <?php 
                $id_kelas_selected = $id_kelas_asal;
                $filter_prefix = 'f1';
                $filter_name = 'kelas_asal';
                $hide_labels = true;
                include 'include/filter_kelas.php'; 
                ?>
                <?php if($id_kelas_asal > 0): ?>
                    <div class="flex-none w-full xl:w-auto">
                        <a href="kenaikan_kelas" class="w-full bg-gray-100 text-gray-700 px-6 py-3 rounded-xl hover:bg-gray-200 transition-colors flex items-center justify-center font-bold shadow-sm">
                            <i class="ri-refresh-line mr-2"></i> Reset
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        <?php endif; ?>

        <?php if ($id_kelas_asal > 0): ?>
            <?php
            $querySiswa = "SELECT id_siswa, nisn, nama, nomor_santri FROM siswa WHERE id_kelas = ? AND status = 'Aktif' ORDER BY nama";
            $stmt = mysqli_prepare($koneksi, $querySiswa);
            mysqli_stmt_bind_param($stmt, "i", $id_kelas_asal);
            mysqli_stmt_execute($stmt);
            $resultSiswa = mysqli_stmt_get_result($stmt);
            ?>

            <div>
                <div class="mt-4">
                    <form action="proses_kenaikan" method="POST" id="formKenaikan">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="kelas_asal" value="<?= $id_kelas_asal; ?>">
                        
                        <?php 
                        $ta_aktif = $_SESSION['tahun_ajaran'] ?? '1445/1446';
                        $ta_parts = explode('/', $ta_aktif);
                        $next_ta = $ta_aktif;
                        if (count($ta_parts) == 2 && is_numeric($ta_parts[0])) {
                            $next_ta = ($ta_parts[0] + 1) . '/' . ($ta_parts[1] + 1);
                        }
                        ?>
                        <input type="hidden" name="tahun_ajaran" value="<?= $next_ta; ?>">

                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-slate-600 border-collapse border border-slate-300">
                                <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                                    <tr>
                                        <th scope="col" class="py-4 px-6 font-bold border border-slate-300 w-4">
                                            <div class="flex items-center justify-center">
                                                <input id="checkbox-all" type="checkbox" class="w-5 h-5 text-emerald-600 bg-white border-slate-300 rounded focus:ring-emerald-500 focus:ring-2 cursor-pointer transition-colors shadow-sm">
                                                <label for="checkbox-all" class="sr-only">checkbox</label>
                                            </div>
                                        </th>
                                        <th scope="col" class="py-4 px-6 font-bold border border-slate-300">NISN</th>
                                        <th scope="col" class="py-4 px-6 font-bold border border-slate-300">No Santri</th>
                                        <th scope="col" class="py-4 px-6 font-bold border border-slate-300">Nama Santri</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($resultSiswa) > 0): ?>
                                        <?php while ($siswa = mysqli_fetch_assoc($resultSiswa)): ?>
                                            <tr class="hover:bg-slate-50 transition-colors group">
                                                <td class="w-4 py-2 px-6 border border-slate-300">
                                                    <div class="flex items-center justify-center">
                                                        <input id="checkbox-<?= $siswa['id_siswa']; ?>" name="siswa_ids[]" value="<?= $siswa['id_siswa']; ?>" type="checkbox" class="w-5 h-5 text-emerald-600 bg-white border-slate-300 rounded focus:ring-emerald-500 focus:ring-2 cursor-pointer transition-colors shadow-sm siswa-checkbox">
                                                        <label for="checkbox-<?= $siswa['id_siswa']; ?>" class="sr-only">checkbox</label>
                                                    </div>
                                                </td>
                                                <td class="py-2 px-6 border border-slate-300"><?= htmlspecialchars($siswa['nisn']); ?></td>
                                                <td class="py-2 px-6 text-slate-500 border border-slate-300"><?= htmlspecialchars($siswa['nomor_santri']); ?></td>
                                                <td class="py-2 px-6 font-bold text-slate-800 text-base border border-slate-300"><?= htmlspecialchars($siswa['nama']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="py-2 px-6 text-center text-slate-400 border border-slate-300">Tidak ada data siswa aktif di kelas ini.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            </div>
                        </div>
                        
                        <div class="flex justify-end border-t border-slate-100 pt-6 mt-4 gap-4">
                            <input type="hidden" name="action_type" id="action_type" value="naik">
                            
                            <button type="button" class="text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-bold rounded-xl text-base px-8 py-3.5 text-center inline-flex items-center transition-all" onclick="if(confirm('Apakah Anda yakin ingin MELULUSKAN siswa terpilih menjadi ALUMNI? Mereka tidak akan naik kelas tetapi masuk ke daftar Arsip/Alumni.')) { document.getElementById('action_type').value='lulus'; document.getElementById('formKenaikan').submit(); }">
                                <i class="ri-graduation-cap-line mr-2 text-xl"></i> Luluskan (Alumni)
                            </button>
                            
                            <button type="button" class="text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-bold rounded-xl text-base px-8 py-3.5 text-center inline-flex items-center transition-all shadow-lg shadow-emerald-500/30 transform hover:-translate-y-0.5" onclick="if(confirm('Apakah Anda yakin ingin menaikkan kelas siswa yang dipilih?')) { document.getElementById('action_type').value='naik'; document.getElementById('formKenaikan').submit(); }">
                                <i class="ri-arrow-up-circle-line mr-2 text-xl"></i> Proses Kenaikan Kelas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <script>
                // Script untuk Select All checkbox
                document.getElementById('checkbox-all').addEventListener('change', function(e) {
                    var checkboxes = document.querySelectorAll('.siswa-checkbox');
                    for (var i = 0; i < checkboxes.length; i++) {
                        checkboxes[i].checked = e.target.checked;
                    }
                });
            </script>
        <?php endif; ?> <!-- endif kelas asal -->
        <?php endif; ?> <!-- endif semester aktif == 2 -->
    </div>
</div>

<?php include 'include/footer.php'; ?>
