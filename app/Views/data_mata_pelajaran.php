<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(['Admin', 'Kepala Madrasah']);
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';

$id_kelas = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;
$is_admin = ($_SESSION['peran'] === 'Admin');

// Ambil list guru untuk dropdown
$guru_query = mysqli_query($koneksi, "SELECT p.id_pengguna, p.nama FROM pengguna p WHERE p.peran IN ('Guru', 'Wali Kelas') AND p.status = 'Aktif' ORDER BY p.nama ASC");
$semua_guru = [];
while ($row = mysqli_fetch_assoc($guru_query)) {
    $semua_guru[] = $row;
}
?>

<div class="page-shell">
  <div class="page-inner">

    <!-- Page Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="page-title">Pengaturan Mata Pelajaran</h1>
        <p class="page-subtitle">Kelola mata pelajaran dan pengampu guru</p>
      </div>
      <?php if ($is_admin): ?>
      <div class="flex flex-wrap items-center gap-2">
        <button type="button" onclick="openOffcanvasImport('offcanvas-import-mapel')"
                class="btn btn-secondary btn-sm text-emerald-700 border-emerald-200 bg-emerald-50 hover:bg-emerald-100">
          <i class="ri-file-excel-2-line"></i> Import Excel
        </button>
        <button type="button" onclick="tambahMapelMaster()" class="btn btn-primary btn-sm">
          <i class="ri-add-line"></i> Tambah Master Mapel
        </button>
      </div>
      <?php endif; ?>
    </div>

    <!-- Form Filter Kelas -->
    <div class="ui-card ui-card-body mb-6">
      <form id="formPencarian" class="flex flex-wrap gap-3 items-end">
          <?php
          $no_autosubmit = true;
          $id_kelas_selected = $id_kelas > 0 ? $id_kelas : 0;
          include 'include/filter_kelas.php';
          ?>
          <div class="flex-none w-full lg:w-auto">
              <button type="button" onclick="tampilkanMapel()" class="btn btn-primary w-full lg:w-auto">
                  <i class="ri-search-line"></i> Tampilkan
              </button>
          </div>
      </form>
    </div>

    <!-- State Kosong -->
    <div id="empty-state" class="ui-empty-state my-6 <?= $id_kelas > 0 ? 'hidden' : '' ?>">
      <div class="ui-empty-icon"><i class="ri-book-open-line text-2xl"></i></div>
      <h3 class="text-lg font-bold text-slate-700 mb-1">Pilih Kelas Terlebih Dahulu</h3>
      <p class="text-sm text-slate-400 max-w-md">Pilih tingkat, kelas, dan rombel di atas lalu klik Tampilkan untuk mengelola mata pelajaran kelas.</p>
    </div>

    <!-- Kontainer Tabel Mapel -->
    <div id="tabel-container" class="<?= $id_kelas == 0 ? 'hidden' : '' ?>">
        <input type="hidden" id="id_kelas_active" value="<?= $id_kelas ?>">

        <div class="flex items-center justify-between mb-4">
            <h2 id="label-kelas-aktif" class="text-base font-bold text-gray-700 flex items-center gap-2">
                <i class="ri-filter-3-line text-blue-500"></i>
                <span id="nama-kelas-display">—</span>
            </h2>
        </div>

    <div class="table-scroll-wrap">
      <table class="ui-table">
        <thead>
          <tr>
            <th class="w-12 text-center">No</th>
            <th>Mata Pelajaran</th>
            <th>Nama Kitab</th>
            <th class="w-32 text-center">Status</th>
            <th class="w-1/3">Guru Pengajar</th>
            <?php if ($is_admin): ?>
            <th class="text-center w-24">Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody id="tbody-mapel">
                    <?php if ($id_kelas > 0):
                        $query = "SELECT m.*, pm.id_guru, pm.status AS status_kelas, pm.nama_kitab
                                  FROM mata_pelajaran m
                                  LEFT JOIN pengampu_mapel pm ON m.id_mapel = pm.id_mapel AND pm.id_kelas = $id_kelas
                                  WHERE m.status = 'Aktif'
                                  ORDER BY m.id_mapel ASC";
                        $result = mysqli_query($koneksi, $query);
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)):
                            $is_aktif_kelas = ($row['status_kelas'] == 'Aktif' || !isset($row['status_kelas'])) ? true : false;
                    ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="text-center text-slate-400 text-xs"><?= $no++ ?></td>
                            <td>
                                <?= htmlspecialchars($row['nama_mapel']); ?>
                                <div class="text-sm font-arabic font-normal text-slate-500 mt-1" dir="rtl"><?= htmlspecialchars($row['nama_mapel_arab']); ?></div>
                            </td>
                            <td>
                                <?php if ($is_admin): ?>
                                <div class="relative">
                                    <input type="text" id="nama_kitab_<?= $row['id_mapel']; ?>" value="<?= htmlspecialchars($row['nama_kitab'] ?? ''); ?>" placeholder="Tulis nama kitab..." class="ui-input py-1.5 px-3" <?= !$is_aktif_kelas ? 'disabled' : ''; ?> onchange="autoSaveMapel(<?= $row['id_mapel']; ?>)">
                                </div>
                                <?php else: ?>
                                    <div class="font-medium text-blue-700">
                                        <?php if (!empty($row['nama_kitab'])): ?>
                                            <i class="ri-book-read-line mr-1"></i> <?= htmlspecialchars($row['nama_kitab']); ?>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic font-normal">-</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <select id="status_mapel_<?= $row['id_mapel']; ?>" <?= !$is_admin ? 'disabled' : '' ?> class="ui-select py-1.5 px-3 text-center font-bold <?= $is_aktif_kelas ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-slate-500 bg-slate-50'; ?>" onchange="toggleGuruSelect(this, <?= $row['id_mapel']; ?>); autoSaveMapel(<?= $row['id_mapel']; ?>)">
                                    <option value="Aktif" <?= $is_aktif_kelas ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="Non-Aktif" <?= !$is_aktif_kelas ? 'selected' : ''; ?>>Non-Aktif</option>
                                </select>
                            </td>
                            <td class="py-2 px-6  whitespace-nowrap">
                                <select id="guru_select_<?= $row['id_mapel']; ?>" <?= (!$is_aktif_kelas || !$is_admin) ? 'disabled' : ''; ?> class="ui-select py-1.5 px-3" onchange="autoSaveMapel(<?= $row['id_mapel']; ?>)">
                                    <option value="">-- Kosong (Belum Diatur) --</option>
                                    <?php foreach ($semua_guru as $g): ?>
                                        <option value="<?= $g['id_pengguna']; ?>" <?= ($row['id_guru'] == $g['id_pengguna']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($g['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <?php if ($is_admin): ?>
                            <td class="py-2 px-6  whitespace-nowrap text-center space-x-1">
                                <button type="button" onclick="editMapelMaster(<?= $row['id_mapel']; ?>, '<?= addslashes($row['nama_mapel']); ?>', '<?= addslashes($row['nama_mapel_arab']); ?>', '<?= addslashes($row['nama_kitab'] ?? ''); ?>', <?= $row['kkm']; ?>)" class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg inline-flex items-center transition-colors shadow-sm" title="Edit Master Mapel">
                                    <i class="ri-edit-line text-lg"></i>
                                </button>
                                <button type="button" onclick="hapusMapelMaster(<?= $row['id_mapel']; ?>)" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-lg inline-flex items-center transition-colors shadow-sm" title="Hapus Master Mapel">
                                    <i class="ri-delete-bin-line text-lg"></i>
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="py-2 px-6 text-center text-slate-400 ">Belum ada data. Gunakan filter di atas untuk memuat data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
        </div>
    </div>

  </div>
</div>

<!-- Offcanvas / Slide-over Panel for Master Mapel -->
<div id="mapelOffcanvas" class="fixed inset-y-0 right-0 z-50 w-full bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col" style="max-width: 450px;">
    <!-- Header -->
    <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50" id="offcanvasHeader">
        <div>
            <h3 id="offcanvasTitle" class="text-xl font-bold text-green-800">Tambah Master Mapel</h3>
            <p id="offcanvasSubtitle" class="text-sm text-green-600 mt-1">Tambahkan mapel master baru</p>
        </div>
        <button type="button" onclick="closeOffcanvas()" class="text-gray-400 bg-white hover:bg-gray-100 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center shadow-sm transition-colors">
            <i class="ri-close-line text-2xl"></i>
        </button>
    </div>

    <!-- Body -->
    <div class="p-6 overflow-y-auto flex-1 bg-white">
        <form id="formMapel" action="proses_tambah_mapel" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
            <input type="hidden" name="id_mapel" id="id_mapel">
            <input type="hidden" name="id_kelas_redirect" value="<?= $id_kelas; ?>">
            <input type="hidden" name="status" value="Aktif">

            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-900">Nama Mata Pelajaran <span class="text-red-500">*</span></label>
                <input type="text" name="nama_mapel" id="nama_mapel" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block w-full p-3.5 transition-colors" placeholder="Contoh: Aqidah, Fiqih" required>
            </div>

            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-900">Nama Arab (Otomatis/Manual) <span class="text-red-500">*</span></label>
                <input type="text" name="nama_mapel_arab" id="nama_mapel_arab" dir="rtl" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block w-full p-3.5 transition-colors font-arabic text-right text-xl" required>
            </div>

            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-900">Nama Kitab</label>
                <input type="text" name="nama_kitab" id="nama_kitab" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block w-full p-3.5 transition-colors" placeholder="Contoh: Tijan Ad-Darari">
            </div>

            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-900">Nilai KKM <span class="text-red-500">*</span></label>
                <input type="number" id="kkm" name="kkm" value="65" min="0" max="100" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block w-full p-3.5 transition-colors" required>
            </div>
            
            <div class="pt-6 pb-12 border-t border-gray-100">
                <button type="submit" id="btnSubmit" class="w-full text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-bold rounded-xl text-base px-5 py-4 text-center transition-colors shadow-lg shadow-green-500/30">
                    <i class="ri-save-line mr-2"></i> Simpan Master Mapel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Backdrop Overlay -->
<div id="offcanvasBackdrop" class="fixed inset-0 bg-gray-900/60 z-40 hidden backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeOffcanvas()"></div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

    // Tampilkan tabel setelah klik Tampilkan
    function tampilkanMapel() {
        const rombelSelect = document.querySelector('select[name="kelas"]');
        if (!rombelSelect || !rombelSelect.value) {
            Swal.fire({ icon: 'warning', title: 'Pilih Kelas', text: 'Silakan pilih Tingkat, Kelas, dan Rombel terlebih dahulu!', confirmButtonColor: '#3085d6' });
            return;
        }
        const idKelas = rombelSelect.value;
        // Redirect dengan parameter kelas
        window.location.href = 'data_mata_pelajaran.php?kelas=' + idKelas;
    }

    // Toggle Guru Select based on Status Mapel
    function toggleGuruSelect(selectElement, mapelId) {
        const guruSelect = document.getElementById('guru_select_' + mapelId);
        const kitabInput = document.getElementById('nama_kitab_' + mapelId);
        if (selectElement.value === 'Aktif') {
            selectElement.classList.remove('text-gray-500', 'bg-gray-50');
            selectElement.classList.add('text-green-600', 'bg-green-50', 'border-green-200');
            if (guruSelect) guruSelect.disabled = false;
            if (kitabInput) kitabInput.disabled = false;
        } else {
            selectElement.classList.remove('text-green-600', 'bg-green-50', 'border-green-200');
            selectElement.classList.add('text-gray-500', 'bg-gray-50');
            if (guruSelect) {
                guruSelect.disabled = true;
                guruSelect.value = "";
            }
            if (kitabInput) kitabInput.disabled = true;
        }
    }

    // Auto Save Mapel Settings via AJAX
    function autoSaveMapel(mapelId) {
        const idKelas = document.getElementById('id_kelas_active').value;
        const status = document.getElementById('status_mapel_' + mapelId).value;
        const idGuru = document.getElementById('guru_select_' + mapelId).value;
        const namaKitabInput = document.getElementById('nama_kitab_' + mapelId);
        const namaKitab = namaKitabInput ? namaKitabInput.value : '';

        const formData = new URLSearchParams();
        formData.append('id_kelas', idKelas);
        formData.append('id_mapel', mapelId);
        formData.append('status', status);
        formData.append('id_guru', idGuru);
        formData.append('nama_kitab', namaKitab);

        fetch('api_simpan_mapel_kelas.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(response => response.json())
        .then(data => {
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, timerProgressBar: true });
            if (data.success) {
                Toast.fire({ icon: 'success', title: 'Tersimpan!' });
            } else {
                Toast.fire({ icon: 'error', title: 'Gagal: ' + data.message });
            }
        })
        .catch(() => {
            Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 }).fire({ icon: 'error', title: 'Terjadi kesalahan jaringan' });
        });
    }

    // Toggle Offcanvas UI
    var offcanvas = document.getElementById('mapelOffcanvas');
    var backdrop = document.getElementById('offcanvasBackdrop');
    var formMapel = document.getElementById('formMapel');

    function openOffcanvas() {
        offcanvas.classList.remove('translate-x-full');
        backdrop.classList.remove('hidden');
        setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
        document.body.style.overflow = 'hidden';
    }

    function closeOffcanvas() {
        offcanvas.classList.add('translate-x-full');
        backdrop.classList.add('opacity-0');
        setTimeout(() => backdrop.classList.add('hidden'), 300);
        document.body.style.overflow = '';
    }

    function tambahMapelMaster() {
        formMapel.reset();
        document.getElementById('id_mapel').value = '';
        document.getElementById('nama_kitab').value = '';
        document.getElementById('kkm').value = '65';
        document.getElementById('offcanvasHeader').className = 'flex items-center justify-between p-6 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50';
        document.getElementById('offcanvasTitle').innerText = 'Tambah Master Mapel';
        document.getElementById('offcanvasTitle').className = 'text-xl font-bold text-green-800';
        document.getElementById('btnSubmit').className = 'w-full text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-bold rounded-xl text-base px-5 py-4 text-center transition-colors shadow-lg shadow-green-500/30';
        formMapel.action = 'proses_tambah_mapel.php';
        openOffcanvas();
    }

    function editMapelMaster(idMapel, nama, namaArab, namaKitab, kkm) {
        document.getElementById('id_mapel').value = idMapel;
        document.getElementById('nama_mapel').value = nama;
        document.getElementById('nama_mapel_arab').value = namaArab;
        document.getElementById('nama_kitab').value = namaKitab;
        document.getElementById('kkm').value = kkm;
        document.getElementById('offcanvasHeader').className = 'flex items-center justify-between p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50';
        document.getElementById('offcanvasTitle').innerText = 'Edit Master Mapel';
        document.getElementById('offcanvasTitle').className = 'text-xl font-bold text-blue-800';
        document.getElementById('btnSubmit').className = 'w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-xl text-base px-5 py-4 text-center transition-colors shadow-lg shadow-blue-500/30';
        formMapel.action = 'proses_edit_mapel.php';
        openOffcanvas();
    }

    function hapusMapelMaster(idMapel) {
        Swal.fire({
            title: 'Hapus Mapel dari Kelas Ini?',
            text: "Mata pelajaran ini hanya akan dihapus dari kelas ini saja. Kelas lain tidak terpengaruh.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="ri-delete-bin-line"></i> Ya, Hapus dari Kelas Ini!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const idKelas = document.getElementById('id_kelas_active').value;
                window.location.href = 'proses_hapus_mapel.php?id=' + idMapel + '&kelas=' + idKelas + '&csrf_token=<?= generate_csrf_token(); ?>';
            }
        });
    }

    // Auto Translate for Mapel Arab
    document.getElementById('nama_mapel').addEventListener('change', function() {
        if(document.getElementById('id_mapel').value !== '') return;
        const namaMapel = this.value;
        fetch('get_terjemahan_mapel.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'nama_mapel=' + encodeURIComponent(namaMapel)
        })
        .then(response => response.json())
        .then(data => {
            if (data.terjemahan) {
                document.getElementById('nama_mapel_arab').value = data.terjemahan;
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>

<?php include 'include/footer.php'; ?>

<?php if ($is_admin): ?>
    <?php include 'import_mapel_ui.php'; ?>
    <?php include 'import_mapel_js.php'; ?>
<?php endif; ?>

<script>
    // Offcanvas General Function untuk Import
    function openOffcanvasImport(id) {
        const el = document.getElementById(id);
        const overlay = document.getElementById(id.replace('offcanvas-', 'overlay-'));
        if (el) el.classList.remove('translate-x-full');
        if (overlay) {
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
        }
    }
    function closeOffcanvasImport(id) {
        const el = document.getElementById(id);
        const overlay = document.getElementById(id.replace('offcanvas-', 'overlay-'));
        if (el) el.classList.add('translate-x-full');
        if (overlay) {
            overlay.classList.add('opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    }
</script>