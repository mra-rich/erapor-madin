<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_MANAGE_GRADES);
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

$id_pengguna = $_SESSION['id_pengguna'] ?? 0;
$peran = $_SESSION['peran'] ?? '';

// Ambil tahun ajaran & semester aktif
$q_pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan LIMIT 1");
$data_pengaturan = mysqli_fetch_assoc($q_pengaturan);
$tahun_aktif = $data_pengaturan['tahun_ajaran'] ?? '2024/2025';
$semester_aktif = $data_pengaturan['semester'] ?? 1;

// Jika admin, ambil semua. Jika guru, ambil mapel yang diampunya saja.
if ($peran == 'Admin' || $peran == 'Kepala Madrasah') {
    $query_mapel = "SELECT pm.*, m.nama_mapel, k.nama_kelas, k.nama_rombel, t.nama_tingkat, p.nama as nama_guru,
                    (SELECT COUNT(n.id_nilai) 
                     FROM nilai n 
                     JOIN transaksi_raport tr ON n.id_transaksi = tr.id_transaksi 
                     JOIN riwayat_kelas r ON tr.id_siswa = r.id_siswa 
                     WHERE r.id_kelas = pm.id_kelas 
                       AND n.id_mapel = pm.id_mapel 
                       AND tr.tahun_ajaran = '$tahun_aktif' 
                       AND tr.semester = $semester_aktif) as jumlah_nilai
                    FROM pengampu_mapel pm 
                    JOIN mata_pelajaran m ON pm.id_mapel = m.id_mapel 
                    JOIN kelas k ON pm.id_kelas = k.id_kelas
                    LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat
                    JOIN pengguna p ON pm.id_guru = p.id_pengguna
                    WHERE pm.status = 'Aktif' AND m.status = 'Aktif'
                    ORDER BY t.nama_tingkat ASC, CAST(k.nama_kelas AS UNSIGNED) ASC, k.nama_rombel ASC, m.nama_mapel ASC";
} else {
    $query_mapel = "SELECT pm.*, m.nama_mapel, k.nama_kelas, k.nama_rombel, t.nama_tingkat, p.nama as nama_guru,
                    (SELECT COUNT(n.id_nilai) 
                     FROM nilai n 
                     JOIN transaksi_raport tr ON n.id_transaksi = tr.id_transaksi 
                     JOIN riwayat_kelas r ON tr.id_siswa = r.id_siswa 
                     WHERE r.id_kelas = pm.id_kelas 
                       AND n.id_mapel = pm.id_mapel 
                       AND tr.tahun_ajaran = '$tahun_aktif' 
                       AND tr.semester = $semester_aktif) as jumlah_nilai
                    FROM pengampu_mapel pm 
                    JOIN mata_pelajaran m ON pm.id_mapel = m.id_mapel 
                    JOIN kelas k ON pm.id_kelas = k.id_kelas
                    LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat
                    JOIN pengguna p ON pm.id_guru = p.id_pengguna
                    WHERE (pm.id_guru = '$id_pengguna' OR k.id_wali_kelas = '$id_pengguna') AND pm.status = 'Aktif' AND m.status = 'Aktif'
                    ORDER BY t.nama_tingkat ASC, CAST(k.nama_kelas AS UNSIGNED) ASC, k.nama_rombel ASC, m.nama_mapel ASC";
}

$result_mapel = mysqli_query($koneksi, $query_mapel);
?>

<div class="p-4 sm:ml-64 bg-slate-50 min-h-screen">
    <div class="p-4 rounded-lg mt-14 max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Kelas & Penilaian</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Semester Aktif: <span class="font-bold text-emerald-600"><?= $semester_aktif == 1 ? 'Ganjil' : 'Genap' ?> (<?= htmlspecialchars($tahun_aktif) ?>)</span>
                </p>
            </div>
        </div>

        <?php if (mysqli_num_rows($result_mapel) > 0): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600 border-collapse border border-slate-300">
                        <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                            <tr>
                                <th scope="col" class="py-4 px-6 font-bold text-center w-16 border border-slate-300">No</th>
                                <th scope="col" class="py-4 px-6 font-bold border border-slate-300">Kelas</th>
                                <th scope="col" class="py-4 px-6 font-bold border border-slate-300">Mata Pelajaran / Kitab</th>
                                <th scope="col" class="py-4 px-6 font-bold border border-slate-300">Nama Guru</th>
                                <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300">Status</th>
                                <th scope="col" class="py-4 px-6 font-bold text-center w-40 border border-slate-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result_mapel)): 
                                $tingkatan_kategori = $row['nama_tingkat'] ?? '';
                                $tingkatan_angka = $row['nama_kelas'];
                                $rombel_display = (!isset($row['nama_rombel']) || $row['nama_rombel'] === '-') ? '' : $row['nama_rombel'] . ' ';
                                $singkatan_map = ['Ibtida\'iyah' => 'MIF', 'Tsanawiyah' => 'MTsF', 'Aliyah' => 'MAF'];
                                $singkatan = $singkatan_map[$tingkatan_kategori] ?? $tingkatan_kategori;
                                $nama_kelas_lengkap = trim($tingkatan_angka . ' ' . $rombel_display . $singkatan);
                                
                                $status_badge = $row['jumlah_nilai'] > 0 
                                    ? '<span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full"><i class="ri-check-line mr-1"></i>Sudah Dinilai</span>'
                                    : '<span class="bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 rounded-full"><i class="ri-time-line mr-1"></i>Belum Dinilai</span>';
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2 px-6 text-center font-medium text-slate-900 border border-slate-300"><?= $no++ ?></td>
                                <td class="py-2 px-6 border border-slate-300">
                                    <span class="bg-emerald-50 text-emerald-700 font-bold px-2.5 py-1 rounded-lg border border-emerald-100"><?= htmlspecialchars($nama_kelas_lengkap) ?></span>
                                </td>
                                <td class="py-2 px-6 font-bold text-slate-800 border border-slate-300">
                                    <?= htmlspecialchars($row['nama_mapel']) ?>
                                    <?php if (!empty($row['nama_kitab'])): ?>
                                    <span class="text-slate-500 font-normal text-sm ml-1">| <?= htmlspecialchars($row['nama_kitab']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-6 border border-slate-300">
                                    <div class="font-semibold text-slate-700"><?= htmlspecialchars($row['nama_guru']) ?></div>
                                </td>
                                <td class="py-2 px-6 text-center border border-slate-300">
                                    <?= $status_badge ?>
                                </td>
                                <td class="py-2 px-6 text-center border border-slate-300">
                                    <a href="input_nilai_massal?id_mapel=<?= $row['id_mapel'] ?>&id_kelas=<?= $row['id_kelas'] ?>" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-lg text-xs px-4 py-2 shadow-md shadow-blue-500/30 transition-all inline-flex items-center">
                                        <i class="ri-pencil-fill mr-1"></i> Input
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white border-2 border-dashed border-slate-300 rounded-2xl p-12 text-center">
                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ri-folder-info-line text-4xl text-slate-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Belum Ada Kelas yang Diampu</h3>
                <p class="text-slate-500 max-w-md mx-auto">Anda belum ditetapkan sebagai pengampu mata pelajaran apapun. Silakan hubungi Administrator untuk pengaturan jadwal mengajar.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'include/footer.php'; ?>
