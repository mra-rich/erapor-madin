<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// Ambil data guru untuk opsi Wali Kelas
$query_guru = "SELECT id_pengguna, nama FROM pengguna WHERE peran IN ('Guru', 'Wali Kelas') AND status = 'Aktif'";
$result_guru = mysqli_query($koneksi, $query_guru);
$gurus = [];
while ($g = mysqli_fetch_assoc($result_guru)) {
    $gurus[] = $g;
}

// Ambil data tingkat kelas (Master Reference)
$query_tingkat = "SELECT * FROM tingkat_kelas ORDER BY id_tingkat ASC";
$result_tingkat = mysqli_query($koneksi, $query_tingkat);
$tingkat_master = [];
while ($t = mysqli_fetch_assoc($result_tingkat)) {
    $tingkat_master[] = $t;
}
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 rounded-lg mt-14">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-800 tracking-tight">Master Kelas & Rombel</h2>
                <p class="text-sm text-gray-500 mt-1">Kelola data tingkat pendidikan dan rombongan belajar secara terintegrasi</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <button type="button" onclick="openOffcanvas('offcanvas-import-kelas')" class="inline-flex justify-center items-center px-4 py-2.5 text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 focus:ring-2 focus:ring-emerald-300 font-medium rounded-lg text-sm transition-colors shadow-sm">
                    <i class="ri-file-excel-line mr-2 text-lg"></i> Import Kelas
                </button>
                <button type="button" onclick="openOffcanvas('offcanvas-tambah')" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-xl text-sm px-6 py-2.5 text-center inline-flex items-center shadow-lg shadow-blue-500/30 transition-all duration-200 whitespace-nowrap">
                    <i class="ri-add-line mr-2 text-lg"></i> Tambah Rombel Baru
                </button>
            </div>
        </div>



        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="kelasTable" class="w-full text-sm text-left text-slate-600 border-collapse border border-slate-300">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                        <tr>
                            <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300 w-16">No</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Tingkatan</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Kelas</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Rombel</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Nama Kelas Lengkap</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Wali Kelas</th>
                            <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                <tbody>
                        <?php
                        $query = "SELECT k.*, p.nama as nama_wali_kelas, t.nama_tingkat 
                                  FROM kelas k 
                                  LEFT JOIN pengguna p ON k.id_wali_kelas = p.id_pengguna 
                                  LEFT JOIN tingkat_kelas t ON k.id_tingkat = t.id_tingkat
                                  ORDER BY FIELD(t.nama_tingkat, 'Ibtida\'iyah', 'Tsanawiyah', 'Aliyah') ASC, CAST(k.nama_kelas AS UNSIGNED) ASC, k.nama_rombel ASC";
                        $result = mysqli_query($koneksi, $query);
                        $no = 1;

                        while ($row = mysqli_fetch_assoc($result)) :
                            $tingkatan_kategori = $row['nama_tingkat']; // 'Ibtida\'iyah', 'Tsanawiyah', 'Aliyah'
                            $tingkatan_angka = $row['nama_kelas']; // '1', '2', '3'
                            $rombel_display = ($row['nama_rombel'] === '-') ? '' : ($row['nama_rombel'] ?? '') . ' ';
                            $singkatan_map = ['Ibtida\'iyah' => 'MIF', 'Tsanawiyah' => 'MTsF', 'Aliyah' => 'MAF'];
                            $singkatan = $singkatan_map[$tingkatan_kategori] ?? $tingkatan_kategori;
                            $nama_kelas_lengkap = trim($tingkatan_angka . ' ' . $rombel_display . $singkatan);
                        ?>
                            <tr class="hover:bg-slate-50 transition-colors group cursor-pointer" hx-get="data_santri.php?kelas=<?= $row['id_kelas']; ?>" hx-target="body" hx-push-url="true">
                                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap font-medium text-slate-900 text-center"><?= $no++; ?></td>
                                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap font-semibold text-slate-700">
                                    <?= htmlspecialchars($tingkatan_kategori); ?>
                                </td>
                                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap">
                                    <span class="bg-indigo-100 text-indigo-800 text-xs font-bold px-3 py-1.5 rounded-full border border-indigo-200 shadow-sm">Kelas <?= htmlspecialchars($tingkatan_angka); ?></span>
                                </td>
                                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap font-extrabold text-blue-700 text-lg">
                                    <?= ($row['nama_rombel'] === '-') ? '<span class="text-slate-400 text-sm font-normal italic">Tanpa Rombel</span>' : htmlspecialchars($row['nama_rombel'] ?? '-'); ?>
                                </td>
                                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap font-bold text-slate-900 text-base">
                                    <?= htmlspecialchars($nama_kelas_lengkap); ?>
                                </td>
                                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-3 font-bold text-xs">
                                            <?= substr(htmlspecialchars($row['nama_wali_kelas'] ?? 'U'), 0, 1); ?>
                                        </div>
                                        <span class="font-medium text-slate-800"><?= htmlspecialchars($row['nama_wali_kelas'] ?? 'Belum Diatur'); ?></span>
                                    </div>
                                </td>
                                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap text-center">
                                    <button type="button" onclick="event.stopPropagation(); editKelas(<?= $row['id_kelas']; ?>, '<?= addslashes($tingkatan_kategori); ?>', '<?= addslashes($tingkatan_angka); ?>', '<?= addslashes($row['nama_rombel'] ?? ''); ?>', '<?= addslashes($row['id_wali_kelas'] ?? ''); ?>')" class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 p-2.5 rounded-xl inline-flex items-center transition-colors mr-2 shadow-sm" title="Edit">
                                        <i class="ri-edit-line text-lg"></i>
                                    </button>
                                    <a hx-get="hapus_kelas.php?id=<?= $row['id_kelas']; ?>&csrf_token=<?= generate_csrf_token(); ?>" hx-target="closest tr" hx-swap="outerHTML swap:1s" hx-confirm="Yakin ingin menghapus kelas ini? Tindakan ini tidak dapat dibatalkan." class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2.5 rounded-xl inline-flex items-center transition-colors shadow-sm cursor-pointer" onclick="event.stopPropagation();" title="Hapus">
                                        <i class="ri-delete-bin-line text-lg"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
</div>

<!-- ================= MODAL OFFCANVAS TAMBAH KELAS ================= -->
<div id="overlay-tambah" class="fixed inset-0 bg-gray-900/60 z-40 hidden backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeOffcanvas('offcanvas-tambah')"></div>

<div id="offcanvas-tambah" class="fixed inset-y-0 right-0 z-50 w-full md:w-[450px] bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    
    <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50 flex justify-between items-center">
        <div>
            <h3 class="text-xl font-extrabold text-gray-800">Tambah Kelas Baru</h3>
            <p class="text-sm text-gray-500 mt-1">Isi formulir untuk membuat rombel</p>
        </div>
        <button type="button" onclick="closeOffcanvas('offcanvas-tambah')" class="text-gray-400 bg-white hover:bg-gray-100 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center shadow-sm">
            <i class="ri-close-line text-2xl"></i>
        </button>
    </div>

    <div class="p-6 flex-1 overflow-y-auto">
        <form action="proses_tambah_kelas" method="POST" id="formTambahKelas" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
            
            <div class="p-4 bg-indigo-50/50 rounded-xl border border-indigo-100 mb-6">
                <label class="block mb-2 text-xs font-bold text-indigo-700 uppercase tracking-wide">Preview Nama Kelas</label>
                <div id="preview_nama_kelas_tambah" class="text-2xl font-extrabold text-indigo-900 break-words">-</div>
            </div>

            <div>
                <label class="block mb-2 text-sm font-bold text-gray-700">Tingkat Madrasah <span class="text-red-500">*</span></label>
                <select id="tingkatan_tambah" name="tingkatan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors cursor-pointer" required onchange="updatePreviewTambah()">
                    <option value="" disabled selected>Pilih Tingkatan</option>
                    <?php foreach ($tingkat_master as $tm): ?>
                        <option value="<?= htmlspecialchars($tm['nama_tingkat']) ?>"><?= htmlspecialchars($tm['nama_tingkat']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block mb-2 text-sm font-bold text-gray-700">Tingkat Kelas <span class="text-red-500">*</span></label>
                <select id="angka_kelas_tambah" name="angka_kelas" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors cursor-pointer" required onchange="updatePreviewTambah()">
                    <option value="" disabled selected>Pilih Kelas</option>
                    <option value="1">Kelas 1</option>
                    <option value="2">Kelas 2</option>
                    <option value="3">Kelas 3</option>
                </select>
            </div>

            <div>
                <label class="block mb-2 text-sm font-bold text-gray-700">Rombongan Belajar (Rombel) <span class="text-red-500">*</span></label>
                <select id="rombel_tambah" name="rombel" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors cursor-pointer" required onchange="updatePreviewTambah()">
                    <option value="" disabled selected>Pilih Rombel</option>
                    <option value="-">Tanpa Rombel</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                    <option value="E">E</option>
                </select>
            </div>

            <div>
                <label class="block mb-2 text-sm font-bold text-gray-700">Wali Kelas <span class="text-red-500">*</span></label>
                <select name="id_wali_kelas" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors cursor-pointer" required>
                    <option value="">-- Pilih Wali Kelas --</option>
                    <?php foreach ($gurus as $g): ?>
                        <option value="<?= $g['id_pengguna']; ?>"><?= htmlspecialchars($g['nama']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Hidden field for final name to ensure processing doesn't fail if we need it -->
            <input type="hidden" id="nama_kelas_final_tambah" name="nama_kelas_lengkap" value="">
        </form>
    </div>
    
    <div class="p-6 border-t border-gray-100 bg-gray-50">
        <button type="button" onclick="document.getElementById('formTambahKelas').submit();" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-xl text-lg px-5 py-4 text-center transition-all shadow-lg shadow-blue-500/30 flex items-center justify-center">
            <i class="ri-save-line mr-2"></i> Simpan Kelas Baru
        </button>
    </div>
</div>

<!-- ================= MODAL OFFCANVAS EDIT KELAS ================= -->
<div id="overlay-edit" class="fixed inset-0 bg-gray-900/60 z-40 hidden backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeOffcanvas('offcanvas-edit')"></div>

<div id="offcanvas-edit" class="fixed inset-y-0 right-0 z-50 w-full md:w-[450px] bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    
    <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50 flex justify-between items-center">
        <div>
            <h3 class="text-xl font-extrabold text-gray-800">Edit Data Kelas</h3>
            <p class="text-sm text-gray-500 mt-1">Perbarui informasi rombel</p>
        </div>
        <button type="button" onclick="closeOffcanvas('offcanvas-edit')" class="text-gray-400 bg-white hover:bg-gray-100 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center shadow-sm">
            <i class="ri-close-line text-2xl"></i>
        </button>
    </div>

    <div class="p-6 flex-1 overflow-y-auto">
        <form action="proses_edit_kelas" method="POST" id="formEditKelas" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
            <input type="hidden" name="id_kelas" id="id_kelas_edit" value="">
            
            <div class="p-4 bg-amber-50/50 rounded-xl border border-amber-100 mb-6">
                <label class="block mb-2 text-xs font-bold text-amber-700 uppercase tracking-wide">Preview Nama Kelas</label>
                <div id="preview_nama_kelas_edit" class="text-2xl font-extrabold text-amber-900 break-words">-</div>
            </div>

            <div>
                <label class="block mb-2 text-sm font-bold text-gray-700">Tingkat Madrasah <span class="text-red-500">*</span></label>
                <select id="tingkatan_edit" name="tingkatan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-amber-500 focus:border-amber-500 block w-full p-3 transition-colors cursor-pointer" required onchange="updatePreviewEdit()">
                    <option value="" disabled>Pilih Tingkatan</option>
                    <?php foreach ($tingkat_master as $tm): ?>
                        <option value="<?= htmlspecialchars($tm['nama_tingkat']) ?>"><?= htmlspecialchars($tm['nama_tingkat']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block mb-2 text-sm font-bold text-gray-700">Tingkat Kelas <span class="text-red-500">*</span></label>
                <select id="angka_kelas_edit" name="angka_kelas" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-amber-500 focus:border-amber-500 block w-full p-3 transition-colors cursor-pointer" required onchange="updatePreviewEdit()">
                    <option value="" disabled>Pilih Kelas</option>
                    <option value="1">Kelas 1</option>
                    <option value="2">Kelas 2</option>
                    <option value="3">Kelas 3</option>
                </select>
            </div>

            <div>
                <label class="block mb-2 text-sm font-bold text-gray-700">Rombongan Belajar (Rombel) <span class="text-red-500">*</span></label>
                <select id="rombel_edit" name="rombel" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-amber-500 focus:border-amber-500 block w-full p-3 transition-colors cursor-pointer" required onchange="updatePreviewEdit()">
                    <option value="" disabled>Pilih Rombel</option>
                    <option value="-">Tanpa Rombel</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                    <option value="E">E</option>
                </select>
            </div>

            <div>
                <label class="block mb-2 text-sm font-bold text-gray-700">Wali Kelas <span class="text-red-500">*</span></label>
                <select name="id_wali_kelas" id="id_wali_kelas_edit" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-amber-500 focus:border-amber-500 block w-full p-3 transition-colors cursor-pointer" required>
                    <option value="">-- Pilih Wali Kelas --</option>
                    <?php foreach ($gurus as $g): ?>
                        <option value="<?= $g['id_pengguna']; ?>"><?= htmlspecialchars($g['nama']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="hidden" id="nama_kelas_final_edit" name="nama_kelas_lengkap" value="">
        </form>
    </div>
    
    <div class="p-6 border-t border-gray-100 bg-gray-50">
        <button type="button" onclick="document.getElementById('formEditKelas').submit();" class="w-full text-white bg-amber-600 hover:bg-amber-700 focus:ring-4 focus:outline-none focus:ring-amber-300 font-bold rounded-xl text-lg px-5 py-4 text-center transition-all shadow-lg shadow-amber-500/30 flex items-center justify-center">
            <i class="ri-save-line mr-2"></i> Simpan Perubahan
        </button>
    </div>
</div>


<!-- Scripts for Offcanvas -->
<script>
    // Toggle Offcanvas functions
    function openOffcanvas(id) {
        const offcanvas = document.getElementById(id);
        const overlayId = id.replace('offcanvas-', 'overlay-');
        const overlay = document.getElementById(overlayId);
        
        overlay.classList.remove('hidden');
        // Force reflow
        void overlay.offsetWidth;
        overlay.classList.remove('opacity-0');
        
        offcanvas.classList.remove('translate-x-full');
        
        if(id === 'offcanvas-tambah') {
            updatePreviewTambah();
        }
    }

    function closeOffcanvas(id) {
        const offcanvas = document.getElementById(id);
        const overlayId = id.replace('offcanvas-', 'overlay-');
        const overlay = document.getElementById(overlayId);
        
        offcanvas.classList.add('translate-x-full');
        
        overlay.classList.add('opacity-0');
        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 300);
    }

    // Logic to update live preview Tambah
    function updatePreviewTambah() {
        const tingkatan = document.getElementById('tingkatan_tambah').value || '';
        const angka = document.getElementById('angka_kelas_tambah').value || '';
        const rombel = document.getElementById('rombel_tambah').value || '';
        
        let full = "-";
        if(tingkatan && angka && rombel) {
            if (rombel === '-') {
                full = `${angka} ${tingkatan}`;
            } else {
                full = `${angka} ${rombel} ${tingkatan}`;
            }
        }
        
        document.getElementById('preview_nama_kelas_tambah').innerText = full;
        document.getElementById('nama_kelas_final_tambah').value = full;
    }

    // Logic to update live preview Edit
    function updatePreviewEdit() {
        const tingkatan = document.getElementById('tingkatan_edit').value || '';
        const angka = document.getElementById('angka_kelas_edit').value || '';
        const rombel = document.getElementById('rombel_edit').value || '';
        
        let full = "-";
        if(tingkatan && angka && rombel) {
            if (rombel === '-') {
                full = `${angka} ${tingkatan}`;
            } else {
                full = `${angka} ${rombel} ${tingkatan}`;
            }
        }
        
        document.getElementById('preview_nama_kelas_edit').innerText = full;
        document.getElementById('nama_kelas_final_edit').value = full;
    }

    // Populate Edit Form
    function editKelas(id, tingkatan, angka, rombel, id_wali) {
        document.getElementById('id_kelas_edit').value = id;
        
        // Coba set value select, jika ada option-nya. Jika tidak ada, di-ignore.
        if (tingkatan) document.getElementById('tingkatan_edit').value = tingkatan;
        if (angka) document.getElementById('angka_kelas_edit').value = angka;
        
        // Rombel text normally is just A/B/C
        let cleanedRombel = rombel.trim();
        // Since we explicitly passed A, B, C it should be fine.
        if (cleanedRombel) document.getElementById('rombel_edit').value = cleanedRombel;
        
        document.getElementById('id_wali_kelas_edit').value = id_wali;
        
        openOffcanvas('offcanvas-edit');
        updatePreviewEdit();
    }
</script>

<?php include 'import_kelas_ui.php'; ?>
<?php include 'import_kelas_js.php'; ?>

<?php include 'include/footer.php'; ?>