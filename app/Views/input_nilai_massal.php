<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_GRADES);

$id_mapel = isset($_GET['id_mapel']) ? (int)$_GET['id_mapel'] : 0;
$id_kelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : 0;

if ($id_mapel == 0 || $id_kelas == 0) {
    die("Parameter tidak valid.");
}

$id_pengguna = $_SESSION['id_pengguna'] ?? 0;
$peran = $_SESSION['peran'] ?? '';

// Cek hak akses guru terhadap mapel ini
if ($peran == 'Guru' || $peran == 'Wali Kelas') {
    $cek_akses = mysqli_query($koneksi, "
        SELECT pm.id 
        FROM pengampu_mapel pm 
        JOIN kelas k ON pm.id_kelas = k.id_kelas 
        WHERE pm.id_mapel = $id_mapel 
          AND pm.id_kelas = $id_kelas 
          AND (pm.id_guru = $id_pengguna OR k.id_wali_kelas = $id_pengguna) 
          AND pm.status = 'Aktif'
    ");
    if (mysqli_num_rows($cek_akses) == 0) {
        die("Anda tidak memiliki akses ke mata pelajaran ini di kelas tersebut.");
    }
}

// Ambil tahun ajaran & semester aktif
$q_pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
$data_pengaturan = mysqli_fetch_assoc($q_pengaturan);
$tahun_aktif = $data_pengaturan['tahun_ajaran'] ?? '2024/2025';
$semester_aktif = $data_pengaturan['semester'] ?? 1;

// Ambil detail mapel & kelas
$q_detail = mysqli_query($koneksi, "
    SELECT m.nama_mapel, k.nama_kelas, k.nama_rombel, t.nama_tingkat 
    FROM mata_pelajaran m 
    JOIN kelas k ON k.id_kelas = $id_kelas
    LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat
    WHERE m.id_mapel = $id_mapel
");
$detail = mysqli_fetch_assoc($q_detail);
$nama_mapel = $detail['nama_mapel'] ?? 'Unknown';

$tingkatan_kategori = $detail['nama_tingkat'] ?? '';
$tingkatan_angka = $detail['nama_kelas'] ?? '';
$rombel_display = (!isset($detail['nama_rombel']) || $detail['nama_rombel'] === '-') ? '' : $detail['nama_rombel'] . ' ';
$singkatan_map = ['Ibtida\'iyah' => 'MIF', 'Tsanawiyah' => 'MTsF', 'Aliyah' => 'MAF'];
$singkatan = $singkatan_map[$tingkatan_kategori] ?? $tingkatan_kategori;
$nama_kelas_lengkap = trim($tingkatan_angka . ' ' . $rombel_display . $singkatan);
if(empty($nama_kelas_lengkap)) $nama_kelas_lengkap = 'Unknown';

// Ambil daftar siswa di kelas ini beserta nilainya (jika sudah ada)
$query_siswa = "
    SELECT s.id_siswa, s.nama, s.nisn,
           (SELECT n.nilai_angka 
            FROM nilai n 
            JOIN transaksi_raport tr ON n.id_transaksi = tr.id_transaksi 
            WHERE tr.id_siswa = s.id_siswa 
              AND tr.tahun_ajaran = '$tahun_aktif' 
              AND tr.semester = $semester_aktif 
              AND n.id_mapel = $id_mapel LIMIT 1) as nilai_angka
    FROM riwayat_kelas r
    JOIN siswa s ON r.id_siswa = s.id_siswa
    WHERE r.id_kelas = $id_kelas AND r.tahun_ajaran = '$tahun_aktif'
    ORDER BY s.nama ASC
";
$result_siswa = mysqli_query($koneksi, $query_siswa);
$siswa_list = [];
while ($row = mysqli_fetch_assoc($result_siswa)) {
    $siswa_list[] = $row;
}

include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64 bg-slate-50 min-h-screen">
    <div class="p-4 rounded-lg mt-14 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 mb-6 flex flex-col md:flex-row justify-between md:items-center">
            <div class="flex items-center mb-4 md:mb-0">
                <a href="penilaian_mapel.php" class="w-10 h-10 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center mr-4 transition-colors">
                    <i class="ri-arrow-left-line text-lg"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Input Nilai: <?= htmlspecialchars($nama_mapel) ?></h2>
                    <p class="text-sm text-slate-500 mt-1">Kelas <?= htmlspecialchars($nama_kelas_lengkap) ?> &bull; Semester <?= $semester_aktif == 1 ? 'Ganjil' : 'Genap' ?> (<?= htmlspecialchars($tahun_aktif) ?>)</p>
                </div>
            </div>
            
            <div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-xl border border-emerald-100 text-sm font-semibold flex items-center">
                <i class="ri-information-line mr-2"></i> Tersimpan otomatis ke Semester Aktif
            </div>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="p-4 mb-6 text-sm text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center" role="alert">
                <i class="ri-checkbox-circle-fill text-xl mr-2"></i>
                <div>
                    <span class="font-bold">Berhasil!</span> Semua nilai santri untuk mata pelajaran ini berhasil disimpan.
                </div>
            </div>
        <?php endif; ?>

        <!-- Form Input Massal -->
        <form action="proses_nilai_massal.php" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="id_mapel" value="<?= $id_mapel ?>">
            <input type="hidden" name="id_kelas" value="<?= $id_kelas ?>">

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600 border-collapse border border-slate-300">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                        <tr>
                            <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300 w-16">No</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300">NISN</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300">Nama Santri</th>
                            <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300 w-40">Nilai Angka</th>
                            <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300 w-48">Nilai Huruf (Auto)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (count($siswa_list) > 0): ?>
                            <?php $no = 1; foreach ($siswa_list as $siswa): ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2 px-6 text-center font-medium text-slate-900 border border-slate-300"><?= $no++ ?></td>
                                <td class="py-2 px-6 border border-slate-300"><?= htmlspecialchars($siswa['nisn']) ?></td>
                                <td class="py-2 px-6 font-bold text-slate-800 text-base border border-slate-300"><?= htmlspecialchars($siswa['nama']) ?></td>
                                <td class="py-2 px-6 border border-slate-300">
                                    <input type="number" 
                                           name="nilai[<?= $siswa['id_siswa'] ?>]" 
                                           value="<?= htmlspecialchars($siswa['nilai_angka'] ?? '') ?>" 
                                           class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5 text-center font-bold shadow-sm transition-colors input-nilai" 
                                           min="0" max="100" 
                                           oninput="convertNilai(this, 'huruf_<?= $siswa['id_siswa'] ?>')" 
                                           placeholder="-">
                                </td>
                                <td class="py-2 px-6 border border-slate-300">
                                    <input type="text" 
                                           id="huruf_<?= $siswa['id_siswa'] ?>" 
                                           class="bg-slate-100 border-none text-slate-500 text-sm rounded-lg block w-full p-2.5 text-center font-semibold pointer-events-none" 
                                           readonly>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-2 px-6 text-center text-slate-500 border border-slate-300">
                                    <i class="ri-team-line text-4xl mb-2 text-slate-300"></i>
                                    <p>Belum ada santri yang terdaftar di kelas ini.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (count($siswa_list) > 0): ?>
            <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-between items-center sticky bottom-0 z-10 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <p class="text-sm text-slate-500">Pastikan semua nilai terisi dengan benar (Skala 0-100).</p>
                <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-xl text-sm px-6 py-2.5 shadow-lg shadow-blue-500/30 transition-all duration-200 inline-flex items-center">
                    <i class="ri-save-3-fill mr-2"></i> Simpan Semua Nilai
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
// Fungsi untuk konversi angka ke huruf (sesuaikan dengan logic madrasah)
function convertNilai(inputObj, idTarget) {
    let nilai = parseInt(inputObj.value);
    let target = document.getElementById(idTarget);
    
    if (isNaN(nilai) || inputObj.value === '') {
        target.value = '';
        return;
    }
    
    if (nilai < 0) nilai = 0;
    if (nilai > 100) nilai = 100;
    inputObj.value = nilai; // Auto-correct
    
    let huruf = '';
    // Gunakan standar yang ada di aplikasi (contoh umum)
    if (nilai >= 90) {
        huruf = 'Amat Baik';
    } else if (nilai >= 80) {
        huruf = 'Baik';
    } else if (nilai >= 70) {
        huruf = 'Cukup';
    } else if (nilai >= 60) {
        huruf = 'Kurang';
    } else {
        huruf = 'Sangat Kurang';
    }
    target.value = huruf;
}

// Trigger konversi saat halaman dimuat untuk data yang sudah ada
document.addEventListener("DOMContentLoaded", function() {
    let inputs = document.querySelectorAll('.input-nilai');
    inputs.forEach(function(input) {
        if(input.value !== '') {
            // Trigger input event
            input.dispatchEvent(new Event('input'));
        }
    });
});
</script>

<?php include 'include/footer.php'; ?>
