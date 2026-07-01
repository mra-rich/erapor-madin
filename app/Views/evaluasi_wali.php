<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_MANAGE_GRADES);

$id_pengguna = $_SESSION['id_pengguna'] ?? 0;
$peran = $_SESSION['peran'] ?? '';

// Halaman ini khusus Wali Kelas (atau Admin yang mewakili)
if (!in_array($peran, ['Wali Kelas', 'Admin'])) {
    die("Akses ditolak. Halaman ini khusus Wali Kelas atau Admin.");
}

// Ambil tahun ajaran & semester aktif
$q_pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
$data_pengaturan = mysqli_fetch_assoc($q_pengaturan);
$tahun_aktif = $data_pengaturan['tahun_ajaran'] ?? '2024/2025';
$semester_aktif = $data_pengaturan['semester'] ?? 1;

// Cari kelas binaan untuk wali kelas ini
$query_kelas = "SELECT k.*, t.nama_tingkat FROM kelas k LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat WHERE id_wali_kelas = $id_pengguna LIMIT 1";
if ($peran == 'Admin') {
    $query_kelas = "SELECT k.*, t.nama_tingkat FROM kelas k LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat LIMIT 1"; // Fallback Admin
}
$result_kelas = mysqli_query($koneksi, $query_kelas);
$kelas_binaan = mysqli_fetch_assoc($result_kelas);

if (!$kelas_binaan) {
    include 'include/header.php';
    include 'include/navbar.php';
    include 'include/sidebar.php';
    echo '<div class="p-4 sm:ml-64 bg-slate-50 min-h-screen"><div class="p-4 rounded-lg mt-14 max-w-7xl mx-auto"><div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-100">Anda belum ditetapkan sebagai Wali Kelas.</div></div></div>';
    include 'include/footer.php';
    exit;
}

$id_kelas = $kelas_binaan['id_kelas'];
$tingkatan_kategori = $kelas_binaan['nama_tingkat'] ?? '';
$tingkatan_angka = $kelas_binaan['nama_kelas'];
$rombel_display = (!isset($kelas_binaan['nama_rombel']) || $kelas_binaan['nama_rombel'] === '-') ? '' : $kelas_binaan['nama_rombel'] . ' ';
$singkatan_map = ['Ibtida\'iyah' => 'MIF', 'Tsanawiyah' => 'MTsF', 'Aliyah' => 'MAF'];
$singkatan = $singkatan_map[$tingkatan_kategori] ?? $tingkatan_kategori;
$nama_kelas_lengkap = trim($tingkatan_angka . ' ' . $rombel_display . $singkatan);

// Ambil daftar siswa beserta datanya
$query_siswa = "
    SELECT 
        s.id_siswa, s.nama, s.nisn, 
        tr.id_transaksi,
        kp.kelakuan, kp.kerajinan, kp.kerapian, kp.kedisiplinan,
        ex.baca_quran, ex.baca_kitab, ex.muhafadhoh, ex.kaligrafi,
        cw.catatan,
        ab.sakit, ab.izin, ab.tanpa_keterangan
    FROM riwayat_kelas r
    JOIN siswa s ON r.id_siswa = s.id_siswa
    LEFT JOIN transaksi_raport tr ON tr.id_siswa = s.id_siswa AND tr.tahun_ajaran = '$tahun_aktif' AND tr.semester = $semester_aktif
    LEFT JOIN kepribadian kp ON kp.id_transaksi = tr.id_transaksi
    LEFT JOIN ekstrakurikuler ex ON ex.id_transaksi = tr.id_transaksi
    LEFT JOIN catatan_wali_kelas cw ON cw.id_transaksi = tr.id_transaksi
    LEFT JOIN absensi ab ON ab.id_transaksi = tr.id_transaksi
    WHERE r.id_kelas = $id_kelas AND r.tahun_ajaran = '$tahun_aktif'
    ORDER BY s.nama ASC
";
$result_siswa = mysqli_query($koneksi, $query_siswa);

include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64 bg-slate-50 min-h-screen">
    <div class="p-4 rounded-lg mt-14 max-w-full mx-auto">
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 mb-6 flex flex-col md:flex-row justify-between md:items-center">
            <div class="flex items-center mb-4 md:mb-0">
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Evaluasi Kelas Binaan</h2>
                    <p class="text-sm text-slate-500 mt-1">
                        Kelas: <span class="font-bold text-indigo-600"><?= htmlspecialchars($nama_kelas_lengkap) ?></span> &bull; 
                        Semester: <span class="font-bold text-emerald-600"><?= $semester_aktif == 1 ? 'Ganjil' : 'Genap' ?> (<?= htmlspecialchars($tahun_aktif) ?>)</span>
                    </p>
                </div>
            </div>
        </div>

        <form action="proses_evaluasi_wali" method="POST" id="formEvaluasi">
            <input type="hidden" name="id_kelas" value="<?= $id_kelas ?>">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600 border-collapse border border-slate-300">
                        <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th rowspan="2" class="py-3 px-4 font-bold text-center border border-slate-300">No</th>
                                <th rowspan="2" class="py-3 px-4 font-bold min-w-[200px] border border-slate-300">Nama Santri / NISN</th>
                                <th colspan="4" class="py-3 px-4 font-bold text-center border border-slate-300 bg-blue-50">Kepribadian</th>
                                <th colspan="4" class="py-3 px-4 font-bold text-center border border-slate-300 bg-emerald-50">Ekstrakurikuler</th>
                                <th colspan="3" class="py-3 px-4 font-bold text-center border border-slate-300 bg-amber-50">Absensi</th>
                                <th rowspan="2" class="py-3 px-4 font-bold text-center min-w-[250px] border border-slate-300">Catatan Wali Kelas</th>
                            </tr>
                            <tr>
                                <th class="py-2 px-3 font-bold text-center border border-slate-300 bg-blue-50/50">Kelakuan</th>
                                <th class="py-2 px-3 font-bold text-center border border-slate-300 bg-blue-50/50">Kerajinan</th>
                                <th class="py-2 px-3 font-bold text-center border border-slate-300 bg-blue-50/50">Kerapian</th>
                                <th class="py-2 px-3 font-bold text-center border border-slate-300 bg-blue-50/50">Kedisiplinan</th>
                                <th class="py-2 px-3 font-bold text-center border border-slate-300 bg-emerald-50/50">Baca Al Qur'an</th>
                                <th class="py-2 px-3 font-bold text-center border border-slate-300 bg-emerald-50/50">Baca Kitab</th>
                                <th class="py-2 px-3 font-bold text-center border border-slate-300 bg-emerald-50/50">Muhafadhoh</th>
                                <th class="py-2 px-3 font-bold text-center border border-slate-300 bg-emerald-50/50">Kaligrafi Arab</th>
                                <th class="py-2 px-2 font-bold text-center border border-slate-300 bg-amber-50/50" title="Sakit">S</th>
                                <th class="py-2 px-2 font-bold text-center border border-slate-300 bg-amber-50/50" title="Izin">I</th>
                                <th class="py-2 px-2 font-bold text-center border border-slate-300 bg-amber-50/50" title="Alpha">A</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $options_kepribadian = ['A' => 'Sangat Baik', 'B' => 'Baik', 'C' => 'Cukup', 'D' => 'Kurang'];
                            $options_ekstra = ['A' => 'Sangat Baik', 'B' => 'Baik', 'C' => 'Cukup', 'D' => 'Kurang'];
                            
                            function getColorClass($val) {
                                switch($val) {
                                    case 'C': return 'bg-yellow-100 text-yellow-800 border-yellow-300';
                                    case 'D': return 'bg-red-100 text-red-800 border-red-300';
                                    default: return 'bg-gray-50 text-gray-900 border-gray-300';
                                }
                            }
                            
                            while($row = mysqli_fetch_assoc($result_siswa)): 
                                $id_s = $row['id_siswa'];
                                $kelakuan = $row['kelakuan'] ?: 'B';
                                $kerajinan = $row['kerajinan'] ?: 'B';
                                $kerapian = $row['kerapian'] ?: 'B';
                                $kedisiplinan = $row['kedisiplinan'] ?: 'B';
                                $baca_quran = $row['baca_quran'] ?: 'B';
                                $baca_kitab = $row['baca_kitab'] ?: 'B';
                                $muhafadhoh = $row['muhafadhoh'] ?: 'B';
                                $kaligrafi = $row['kaligrafi'] ?: 'B';
                                $sakit = $row['sakit'] ?? 0;
                                $izin = $row['izin'] ?? 0;
                                $tanpa_keterangan = $row['tanpa_keterangan'] ?? 0;
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-2 px-4 text-center border border-slate-300"><?= $no++ ?></td>
                                <td class="py-2 px-4 border border-slate-300">
                                    <div class="font-bold text-slate-800"><?= htmlspecialchars($row['nama']) ?></div>
                                    <div class="text-xs text-slate-500"><?= htmlspecialchars($row['nisn']) ?></div>
                                    <input type="hidden" name="id_siswa[]" value="<?= $id_s ?>">
                                </td>
                                
                                <!-- Kepribadian -->
                                <td class="py-2 px-2 border border-slate-300">
                                    <select name="kelakuan[<?= $id_s ?>]" class="<?= getColorClass($kelakuan) ?> text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                                        <?php foreach($options_kepribadian as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($kelakuan == $val) ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="py-2 px-2 border border-slate-300">
                                    <select name="kerajinan[<?= $id_s ?>]" class="<?= getColorClass($kerajinan) ?> text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                                        <?php foreach($options_kepribadian as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($kerajinan == $val) ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="py-2 px-2 border border-slate-300">
                                    <select name="kerapian[<?= $id_s ?>]" class="<?= getColorClass($kerapian) ?> text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                                        <?php foreach($options_kepribadian as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($kerapian == $val) ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="py-2 px-2 border border-slate-300">
                                    <select name="kedisiplinan[<?= $id_s ?>]" class="<?= getColorClass($kedisiplinan) ?> text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                                        <?php foreach($options_kepribadian as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($kedisiplinan == $val) ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <!-- Ekstrakurikuler -->
                                <td class="py-2 px-2 border border-slate-300">
                                    <select name="baca_quran[<?= $id_s ?>]" class="<?= getColorClass($baca_quran) ?> text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                                        <?php foreach($options_ekstra as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($baca_quran == $val) ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="py-2 px-2 border border-slate-300">
                                    <select name="baca_kitab[<?= $id_s ?>]" class="<?= getColorClass($baca_kitab) ?> text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                                        <?php foreach($options_ekstra as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($baca_kitab == $val) ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="py-2 px-2 border border-slate-300">
                                    <select name="muhafadhoh[<?= $id_s ?>]" class="<?= getColorClass($muhafadhoh) ?> text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                                        <?php foreach($options_ekstra as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($muhafadhoh == $val) ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="py-2 px-2 border border-slate-300">
                                    <select name="kaligrafi[<?= $id_s ?>]" class="<?= getColorClass($kaligrafi) ?> text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                                        <?php foreach($options_ekstra as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($kaligrafi == $val) ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <!-- Absensi -->
                                <td class="py-2 px-2 border border-slate-300">
                                    <input type="number" name="sakit[<?= $id_s ?>]" value="<?= $sakit ?>" min="0" class="w-12 text-center text-xs bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 p-1.5 block">
                                </td>
                                <td class="py-2 px-2 border border-slate-300">
                                    <input type="number" name="izin[<?= $id_s ?>]" value="<?= $izin ?>" min="0" class="w-12 text-center text-xs bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 p-1.5 block">
                                </td>
                                <td class="py-2 px-2 border border-slate-300">
                                    <input type="number" name="alpha[<?= $id_s ?>]" value="<?= $tanpa_keterangan ?>" min="0" class="w-12 text-center text-xs bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 p-1.5 block">
                                </td>

                                <!-- Catatan -->
                                <td class="py-2 px-2 border border-slate-300">
                                    <textarea name="catatan[<?= $id_s ?>]" rows="2" class="block p-2 w-full text-xs text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Tulis catatan..."><?= htmlspecialchars($row['catatan'] ?? '') ?></textarea>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($no == 1): ?>
                            <tr>
                                <td colspan="10" class="py-2 px-6 text-center text-slate-500 border border-slate-300">Tidak ada santri di kelas ini.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end mt-4 items-center">
            </div>
        </form>
    </div>
</div>

<!-- Hidden div for Tailwind CDN parser to detect dynamic JS/PHP classes -->
<div class="hidden bg-yellow-50 text-yellow-700 bg-red-50 text-red-700 bg-white text-slate-700"></div>

<script>
(function() {
    const form = document.getElementById('formEvaluasi');
    if (!form) return;

    const selects = form.querySelectorAll('select.eval-select');
    
    function updateColor(selectElement) {
        const value = selectElement.value;
        selectElement.classList.remove('bg-yellow-50', 'text-yellow-700', 'bg-red-50', 'text-red-700', 'bg-white', 'text-slate-700');
        
        if (value === 'C') {
            selectElement.classList.add('bg-yellow-50', 'text-yellow-700');
        } else if (value === 'D') {
            selectElement.classList.add('bg-red-50', 'text-red-700');
        } else {
            selectElement.classList.add('bg-white', 'text-slate-700');
        }
    }

    let saveTimeout = null;

    const autoSave = () => {
        saveTimeout = null;
        const formData = new FormData(form);
        formData.append('ajax', '1');

        fetch('proses_evaluasi_wali.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, timerProgressBar: true });
            if (data.status === 'success') {
                Toast.fire({ icon: 'success', title: 'Perubahan tersimpan' });
            } else {
                Toast.fire({ icon: 'error', title: 'Gagal menyimpan data' });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, timerProgressBar: true });
            Toast.fire({ icon: 'error', title: 'Koneksi terputus' });
        });
    };

    form.addEventListener('input', (e) => {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(autoSave, 1000);
    });

    selects.forEach(select => {
        updateColor(select);
        select.addEventListener("change", () => {
            updateColor(select);
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(autoSave, 500);
        });
    });

    const flushAutoSave = () => {
        if (saveTimeout) {
            clearTimeout(saveTimeout);
            saveTimeout = null;
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            if (navigator.sendBeacon) {
                navigator.sendBeacon('proses_evaluasi_wali.php', formData);
            } else {
                fetch('proses_evaluasi_wali.php', { method: 'POST', body: formData, keepalive: true });
            }
        }
    };

    if (window.__eraporFlushAutoSave) {
        window.removeEventListener('beforeunload', window.__eraporFlushAutoSave);
        window.removeEventListener('pagehide', window.__eraporFlushAutoSave);
        document.body.removeEventListener('htmx:beforeRequest', window.__eraporFlushAutoSave);
    }
    
    window.__eraporFlushAutoSave = flushAutoSave;

    window.addEventListener('beforeunload', window.__eraporFlushAutoSave);
    window.addEventListener('pagehide', window.__eraporFlushAutoSave);
    document.body.addEventListener('htmx:beforeRequest', window.__eraporFlushAutoSave);
})();
</script>

<?php include 'include/footer.php'; ?>
