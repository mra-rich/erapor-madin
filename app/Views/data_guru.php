<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// Pagination & Search
$search = trim(isset($_GET['search']) ? $_GET['search'] : '');
$like = '%' . $search . '%';
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$base_where = "WHERE p.peran IN ('Guru', 'Wali Kelas', 'Kepala Madrasah', 'Admin')";
$search_condition = " AND (p.nama LIKE ? OR g.nip LIKE ? OR p.username LIKE ?)";

// Hitung total data
if (!empty($search)) {
    $count_stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) as total FROM pengguna p LEFT JOIN guru g ON p.id_pengguna = g.id_pengguna $base_where$search_condition");
    mysqli_stmt_bind_param($count_stmt, 'sss', $like, $like, $like);
} else {
    $count_stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) as total FROM pengguna p LEFT JOIN guru g ON p.id_pengguna = g.id_pengguna $base_where");
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_data = mysqli_fetch_assoc($count_result)['total'];
mysqli_stmt_close($count_stmt);
$total_pages = ceil($total_data / $limit);

// Ambil data pengguna dan guru dengan limit
$main_sql = "SELECT p.id_pengguna, p.nama, p.username, p.peran, p.status, 
                 g.id_guru, g.nip, g.nama_lengkap, g.jenis_kelamin, g.tempat_lahir, g.tanggal_lahir, g.no_hp, g.alamat
          FROM pengguna p 
          LEFT JOIN guru g ON p.id_pengguna = g.id_pengguna 
          $base_where";
if (!empty($search)) {
    $main_sql .= $search_condition;
}
$main_sql .= " ORDER BY p.nama ASC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($koneksi, $main_sql);
if (!empty($search)) {
    mysqli_stmt_bind_param($stmt, 'sssii', $like, $like, $like, $limit, $offset);
} else {
    mysqli_stmt_bind_param($stmt, 'ii', $limit, $offset);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 rounded-lg mt-14 max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-800 tracking-tight">Data Guru</h2>
                <p class="text-sm text-gray-500 mt-1">Kelola data profil dan akun login guru</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <form class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-search-line text-gray-400"></i>
                        </div>
                        <input type="text" name="search" id="searchInput" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                               class="bg-white border border-gray-200 text-gray-800 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2 py-2 shadow-sm transition-colors" 
                               placeholder="Cari nama, NIP, username..."
                               hx-get="data_guru.php" 
                               hx-trigger="keyup changed delay:500ms, search" 
                               hx-target="body" 
                               hx-push-url="true">
                    </div>
                    <?php if (!empty($_GET['search'])): ?>
                    <a href="data_guru" hx-get="data_guru.php" hx-target="body" hx-push-url="true" class="inline-flex justify-center items-center p-2 bg-gray-50 hover:bg-red-50 text-gray-500 hover:text-red-600 border border-gray-200 hover:border-red-200 rounded-lg transition-colors shadow-sm" title="Reset Pencarian">
                        <i class="ri-close-line text-lg leading-none"></i>
                    </a>
                    <?php endif; ?>
                </form>

                <a href="export_guru<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>" hx-disable="true" hx-boost="false" class="inline-flex justify-center items-center px-4 py-2.5 text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 focus:ring-2 focus:ring-indigo-300 font-medium rounded-lg text-sm transition-colors shadow-sm">
                    <i class="ri-file-download-line mr-2 text-lg"></i>
                    Export
                </a>
                <button type="button" onclick="openOffcanvas('offcanvas-import-guru')" class="inline-flex justify-center items-center px-4 py-2.5 text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 focus:ring-2 focus:ring-emerald-300 font-medium rounded-lg text-sm transition-colors shadow-sm">
                    <i class="ri-file-excel-2-line mr-2 text-lg"></i>
                    Import
                </button>
                <button type="button" onclick="tambahGuru()" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-xl text-sm px-6 py-2.5 text-center inline-flex items-center shadow-lg shadow-blue-500/30 transition-all duration-200 whitespace-nowrap">
                    <i class="ri-add-line mr-2 text-lg"></i> Tambah Guru
                </button>
            </div>
        </div>



        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600 border-collapse border border-slate-300">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                        <tr>
                            <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300 w-16">No</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">NIP / NIK</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap min-w-[200px]">Nama Lengkap</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">L/P</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">No. HP</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Peran</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Username</th>
                            <th scope="col" class="py-4 px-6 font-bold border border-slate-300 whitespace-nowrap">Status</th>
                            <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                <tbody>
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)): 
                        ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-2 px-6 border border-slate-300 font-medium text-slate-900 whitespace-nowrap text-center"><?php echo $no++; ?></td>
                                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap"><?php echo $row['nip'] ? htmlspecialchars($row['nip']) : '-'; ?></td>
                                <td class="py-2 px-6 border border-slate-300 font-bold text-slate-800 text-base whitespace-nowrap"><?php echo $row['nama_lengkap'] ? htmlspecialchars($row['nama_lengkap']) : htmlspecialchars($row['nama']); ?></td>
                                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap"><?php echo ($row['jenis_kelamin'] == 'L') ? 'Laki-laki' : (($row['jenis_kelamin'] == 'P') ? 'Perempuan' : '-'); ?></td>
                                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap"><?php echo $row['no_hp'] ? htmlspecialchars($row['no_hp']) : '-'; ?></td>
                                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap"><span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full"><?php echo htmlspecialchars($row['peran']); ?></span></td>
                                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap"><?php echo htmlspecialchars($row['username']); ?></td>
                                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap">
                                    <?php if ($row['status'] == 'Aktif'): ?>
                                        <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full border border-green-200 shadow-sm">Aktif</span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-800 text-xs font-bold px-3 py-1 rounded-full border border-red-200 shadow-sm">Dihapus</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-6 border border-slate-300 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" onclick="editGuru(<?= $row['id_pengguna']; ?>, '<?= $row['id_guru'] ? $row['id_guru'] : ''; ?>', '<?= htmlspecialchars(addslashes($row['nip'] ?? ''), ENT_QUOTES); ?>', '<?= htmlspecialchars(addslashes($row['nama_lengkap'] ?? $row['nama']), ENT_QUOTES); ?>', '<?= $row['jenis_kelamin'] ?? 'L'; ?>', '<?= htmlspecialchars(addslashes($row['tempat_lahir'] ?? ''), ENT_QUOTES); ?>', '<?= $row['tanggal_lahir'] ?? ''; ?>', '<?= htmlspecialchars(addslashes($row['no_hp'] ?? ''), ENT_QUOTES); ?>', '<?= htmlspecialchars(addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $row['alamat'] ?? '')), ENT_QUOTES); ?>', '<?= htmlspecialchars(addslashes($row['username']), ENT_QUOTES); ?>', '<?= htmlspecialchars(addslashes($row['peran']), ENT_QUOTES); ?>')" class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg inline-flex items-center transition-colors shadow-sm" title="Edit">
                                            <i class="ri-edit-line text-lg"></i>
                                        </button>
                                        <button type="button" onclick="confirmDelete(<?php echo $row['id_pengguna']; ?>, '<?php echo htmlspecialchars(addslashes($row['nama_lengkap'] ?? $row['nama'])); ?>')" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-lg inline-flex items-center transition-colors shadow-sm" title="Hapus">
                                            <i class="ri-delete-bin-line text-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        } else {
                            echo '<tr><td colspan="9" class="py-2 px-6 text-center text-slate-500 border border-slate-300">Belum ada data guru.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="flex flex-col md:flex-row justify-between items-center mt-6 space-y-3 md:space-y-0">
                <span class="text-sm text-gray-700 dark:text-gray-400">
                    Menampilkan <span class="font-semibold text-gray-900"><?= $offset + 1 ?></span> sampai <span class="font-semibold text-gray-900"><?= min($offset + $limit, $total_data) ?></span> dari <span class="font-semibold text-gray-900"><?= $total_data ?></span> data
                </span>
                <nav aria-label="Page navigation">
                    <ul class="inline-flex items-center -space-x-px">
                        <?php if ($page > 1): ?>
                        <li>
                            <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="block px-3 py-2 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">
                                <span class="sr-only">Previous</span>
                                <i class="ri-arrow-left-s-line"></i>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li>
                            <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-2 leading-tight <?= $i == $page ? 'text-blue-600 border border-blue-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700' : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                        <li>
                            <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="block px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">
                                <span class="sr-only">Next</span>
                                <i class="ri-arrow-right-s-line"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>

    </div>
</div>

<!-- Offcanvas / Slide-over Panel for Guru -->
<div id="guruOffcanvas" class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <!-- Header -->
    <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50" id="offcanvasHeader">
        <div>
            <h3 id="offcanvasTitle" class="text-xl font-bold text-blue-800">Tambah Guru Baru</h3>
            <p id="offcanvasSubtitle" class="text-sm text-blue-600 mt-1">Masukkan biodata dan info login</p>
        </div>
        <button type="button" onclick="closeOffcanvas()" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center transition-colors">
            <i class="ri-close-line text-2xl"></i>
        </button>
    </div>

    <!-- Body -->
    <div class="p-6 overflow-y-auto flex-1 bg-white">
        <form id="formGuru" action="proses_input_guru" method="POST" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="id_pengguna" id="id_pengguna">
            <input type="hidden" name="id_guru" id="id_guru">
            
            <h4 class="font-bold text-gray-900 border-b pb-2">Informasi Pengguna</h4>

            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-900">NIP / NIK (Opsional)</label>
                <input type="text" name="nip" id="nip" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors">
            </div>

            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-900">Peran <span class="text-red-500">*</span></label>
                <select name="peran" id="peran" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors" required>
                    <option value="Guru">Guru</option>
                    <option value="Wali Kelas">Wali Kelas</option>
                    <option value="Kepala Madrasah">Kepala Madrasah</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>

            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-900">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors" required>
            </div>

            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-900">Jenis Kelamin <span class="text-red-500">*</span></label>
                <select name="jenis_kelamin" id="jenis_kelamin" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors" required>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-900">Tempat Lahir (Opsional)</label>
                    <input type="text" name="tempat_lahir" id="tempat_lahir" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-900">Tanggal Lahir (Opsional)</label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors">
                </div>
            </div>

            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-900">No. HP / WhatsApp (Opsional)</label>
                <input type="text" name="no_hp" id="no_hp" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors">
            </div>

            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-900">Alamat (Opsional)</label>
                <textarea name="alamat" id="alamat" rows="2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors"></textarea>
            </div>

            <h4 class="font-bold text-gray-900 border-b pb-2 pt-4">Informasi Akun Login</h4>

            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-900">Username Login <span class="text-red-500">*</span></label>
                <input type="text" name="username" id="username" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors" required>
            </div>

            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-900">Password <span id="passwordRequiredLabel" class="text-red-500">*</span></label>
                <input type="password" name="password" id="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 transition-colors" required>
                <p id="passwordHelpText" class="mt-1 text-xs text-gray-500">Gunakan password yang mudah diingat.</p>
            </div>

            <div class="pt-6 pb-12 border-t border-gray-100">
                <button type="submit" id="btnSubmit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-xl text-base px-5 py-4 text-center transition-colors shadow-lg shadow-blue-500/30">
                    <i class="ri-save-line mr-2"></i> Simpan Data Guru
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Backdrop Overlay -->
<div id="offcanvasBackdrop" class="fixed inset-0 bg-gray-900/60 z-40 hidden backdrop-blur-sm transition-opacity duration-300" onclick="closeOffcanvas()"></div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var offcanvas = document.getElementById('guruOffcanvas');
    var backdrop = document.getElementById('offcanvasBackdrop');
    var formGuru = document.getElementById('formGuru');

    function openOffcanvas(id) {
        if (id) {
            document.getElementById(id).classList.remove('translate-x-full');
            const overlayId = 'overlay-' + id.replace('offcanvas-', '');
            const overlayEl = document.getElementById(overlayId);
            if(overlayEl) {
                overlayEl.classList.remove('hidden');
                setTimeout(() => {
                    overlayEl.classList.remove('opacity-0');
                }, 10);
            }
            document.body.style.overflow = 'hidden';
            return;
        }
        offcanvas.classList.remove('translate-x-full');
        backdrop.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeOffcanvas(id) {
        if (id && typeof id === 'string') {
            const el = document.getElementById(id);
            if(el) el.classList.add('translate-x-full');
            const overlayId = 'overlay-' + id.replace('offcanvas-', '');
            const overlayEl = document.getElementById(overlayId);
            if(overlayEl) {
                overlayEl.classList.add('opacity-0');
                setTimeout(() => {
                    overlayEl.classList.add('hidden');
                }, 300);
            }
            document.body.style.overflow = '';
            
            if (id === 'offcanvas-import-guru') {
                document.getElementById('import-guru-step-1').classList.remove('hidden');
                document.getElementById('import-guru-step-2').classList.add('hidden');
                document.getElementById('btn-preview-guru-import').classList.remove('hidden');
                document.getElementById('btn-confirm-guru-import').classList.add('hidden');
                document.getElementById('file_excel_guru').value = '';
                if(typeof validGuruDataCache !== 'undefined') validGuruDataCache = [];
            }
            return;
        }
        offcanvas.classList.add('translate-x-full');
        backdrop.classList.add('hidden');
        document.body.style.overflow = '';
        formGuru.reset();
    }

    function tambahGuru() {
        formGuru.reset();
        document.getElementById('id_pengguna').value = '';
        document.getElementById('id_guru').value = '';
        document.getElementById('peran').value = 'Guru';
        document.getElementById('tempat_lahir').value = '';
        document.getElementById('tanggal_lahir').value = '';
        
        document.getElementById('offcanvasHeader').className = 'flex items-center justify-between p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50';
        document.getElementById('offcanvasTitle').innerText = 'Tambah Guru Baru';
        document.getElementById('offcanvasTitle').className = 'text-xl font-bold text-blue-800';
        document.getElementById('offcanvasSubtitle').innerText = 'Masukkan biodata dan info login';
        document.getElementById('offcanvasSubtitle').className = 'text-sm text-blue-600 mt-1';
        document.getElementById('btnSubmit').className = 'w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-xl text-base px-5 py-4 text-center transition-colors shadow-lg shadow-blue-500/30';
        
        document.getElementById('password').required = true;
        document.getElementById('passwordRequiredLabel').style.display = 'inline';
        document.getElementById('passwordHelpText').innerText = 'Gunakan password yang mudah diingat.';
        
        formGuru.action = 'proses_input_guru.php';
        openOffcanvas();
    }

    function editGuru(idPengguna, idGuru, nip, nama, jk, tempatLahir, tanggalLahir, nohp, alamat, username, peran) {
        formGuru.reset();
        document.getElementById('id_pengguna').value = idPengguna;
        document.getElementById('id_guru').value = idGuru;
        document.getElementById('nip').value = nip;
        document.getElementById('nama_lengkap').value = nama;
        document.getElementById('jenis_kelamin').value = jk;
        document.getElementById('tempat_lahir').value = tempatLahir;
        document.getElementById('tanggal_lahir').value = tanggalLahir;
        document.getElementById('no_hp').value = nohp;
        document.getElementById('alamat').value = alamat;
        document.getElementById('username').value = username;
        document.getElementById('peran').value = peran;
        
        document.getElementById('offcanvasHeader').className = 'flex items-center justify-between p-6 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-teal-50';
        document.getElementById('offcanvasTitle').innerText = 'Edit Data Guru';
        document.getElementById('offcanvasTitle').className = 'text-xl font-bold text-emerald-800';
        document.getElementById('offcanvasSubtitle').innerText = 'Perbarui biodata dan info login';
        document.getElementById('offcanvasSubtitle').className = 'text-sm text-emerald-600 mt-1';
        document.getElementById('btnSubmit').className = 'w-full text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-bold rounded-xl text-base px-5 py-4 text-center transition-colors shadow-lg shadow-emerald-500/30';
        
        document.getElementById('password').required = false;
        document.getElementById('passwordRequiredLabel').style.display = 'none';
        document.getElementById('passwordHelpText').innerText = 'Kosongkan jika tidak ingin mengubah password.';
        
        formGuru.action = 'proses_edit_guru.php';
        openOffcanvas();
    }

    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Hapus Data Guru?',
            text: 'Apakah Anda yakin ingin menghapus ' + nama + '? Akun loginnya juga akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'hapus_guru.php';
                var fId = document.createElement('input'); fId.type='hidden'; fId.name='id'; fId.value=id; form.appendChild(fId);
                var fKon = document.createElement('input'); fKon.type='hidden'; fKon.name='konfirmasi'; fKon.value='ya'; form.appendChild(fKon);
                var fCsrf = document.createElement('input'); fCsrf.type='hidden'; fCsrf.name='csrf_token'; fCsrf.value='<?php echo generate_csrf_token(); ?>'; form.appendChild(fCsrf);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

<?php include 'import_guru_ui.php'; ?>
<?php include 'import_guru_js.php'; ?>

<?php include 'include/footer.php'; ?>
