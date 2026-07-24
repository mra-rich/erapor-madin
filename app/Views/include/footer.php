
<?php
// Tentukan halaman aktif untuk Bottom Navigation
$curr = $_GET['route'] ?? basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
$curr = str_replace('.php', '', $curr);
if ($curr === '' || $curr === 'index') $curr = 'dashboard';

$peran = $_SESSION['peran'] ?? '';

// Mapping tab aktif
$tab_active = 'dashboard';
if (in_array($curr, ['data_santri', 'data_guru', 'data_kelas', 'data_arsip_santri'])) $tab_active = 'data';
elseif (in_array($curr, ['penilaian_mapel', 'data_nilai', 'input_nilai_massal'])) $tab_active = 'nilai';
elseif (in_array($curr, ['evaluasi_wali', 'kenaikan_kelas'])) $tab_active = 'evaluasi';
elseif ($curr === 'cetak_rapot') $tab_active = 'rapor';
?>

<!-- MOBILE BOTTOM NAVIGATION (MODERN PILL) -->
<nav class="sm:hidden fixed bottom-4 left-4 right-4 z-50 px-2 py-2 bg-white/90 backdrop-blur-md border border-white/50 shadow-[0_8px_30px_rgb(0,0,0,0.08)] rounded-full flex justify-between items-center transition-all">
    
    <!-- Tab: Dashboard -->
    <a href="dashboard" hx-get="dashboard" hx-target="body" hx-push-url="true" class="flex flex-col items-center justify-center w-full py-1 rounded-full transition-all <?= $tab_active == 'dashboard' ? 'text-emerald-700 bg-emerald-50/80 scale-105 shadow-sm' : 'text-slate-400 hover:text-slate-600' ?>">
        <i class="<?= $tab_active == 'dashboard' ? 'ri-dashboard-fill' : 'ri-dashboard-line' ?> text-xl mb-0.5"></i>
        <span class="text-[9px] font-bold tracking-wide">Home</span>
    </a>

    <!-- Tab: Data (Khusus Admin/Wali) -->
    <?php if (in_array($peran, ['Admin', 'Kepala Madrasah', 'Wali Kelas'])): ?>
    <a href="data_santri" hx-get="data_santri" hx-target="body" hx-push-url="true" class="flex flex-col items-center justify-center w-full py-1 rounded-full transition-all <?= $tab_active == 'data' ? 'text-emerald-700 bg-emerald-50/80 scale-105 shadow-sm' : 'text-slate-400 hover:text-slate-600' ?>">
        <i class="<?= $tab_active == 'data' ? 'ri-folder-user-fill' : 'ri-folder-user-line' ?> text-xl mb-0.5"></i>
        <span class="text-[9px] font-bold tracking-wide">Data</span>
    </a>
    <?php endif; ?>

    <!-- Tab: Nilai -->
    <a href="penilaian_mapel" hx-get="penilaian_mapel" hx-target="body" hx-push-url="true" class="flex flex-col items-center justify-center w-full py-1 rounded-full transition-all <?= $tab_active == 'nilai' ? 'text-emerald-700 bg-emerald-50/80 scale-105 shadow-sm' : 'text-slate-400 hover:text-slate-600' ?>">
        <i class="<?= $tab_active == 'nilai' ? 'ri-edit-box-fill' : 'ri-edit-box-line' ?> text-xl mb-0.5"></i>
        <span class="text-[9px] font-bold tracking-wide">Nilai</span>
    </a>

    <!-- Tab: Evaluasi/Rapor (Khusus Admin/Wali) -->
    <?php if (in_array($peran, ['Admin', 'Wali Kelas'])): ?>
    <a href="evaluasi_wali" hx-get="evaluasi_wali" hx-target="body" hx-push-url="true" class="flex flex-col items-center justify-center w-full py-1 rounded-full transition-all <?= $tab_active == 'evaluasi' ? 'text-emerald-700 bg-emerald-50/80 scale-105 shadow-sm' : 'text-slate-400 hover:text-slate-600' ?>">
        <i class="<?= $tab_active == 'evaluasi' ? 'ri-survey-fill' : 'ri-survey-line' ?> text-xl mb-0.5"></i>
        <span class="text-[9px] font-bold tracking-wide">Evaluasi</span>
    </a>
    <?php endif; ?>

    <!-- Tab: Rapor (Khusus Admin/Wali) -->
    <?php if (in_array($peran, ['Admin', 'Kepala Madrasah', 'Wali Kelas'])): ?>
    <a href="cetak_rapot" hx-get="cetak_rapot" hx-target="body" hx-push-url="true" class="flex flex-col items-center justify-center w-full py-1 rounded-full transition-all <?= $tab_active == 'rapor' ? 'text-emerald-700 bg-emerald-50/80 scale-105 shadow-sm' : 'text-slate-400 hover:text-slate-600' ?>">
        <i class="<?= $tab_active == 'rapor' ? 'ri-printer-fill' : 'ri-printer-line' ?> text-xl mb-0.5"></i>
        <span class="text-[9px] font-bold tracking-wide">Rapor</span>
    </a>
    <?php endif; ?>
</nav>

<!-- Padding bottom adjustment for mobile so content doesn't hide behind floating nav -->
<style>
    @media (max-width: 640px) {
        body { padding-bottom: 5rem !important; }
        .page-shell { padding-bottom: 2rem !important; }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= $assetBase ?? '' ?>assets/js/main.js"></script>
<?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
<script>
    if (typeof Swal !== 'undefined') {
        <?php if ($_GET['status'] === 'success' || $_GET['status'] === 'sukses'): ?>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: 'success',
            title: '<?php echo addslashes(htmlspecialchars($_GET['message'])); ?>'
        });
        <?php else: ?>
        Swal.fire({
            icon: 'error',
            title: 'Data Tidak Valid',
            text: '<?php echo addslashes(htmlspecialchars($_GET['message'])); ?>',
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#10B981'
        });
        <?php endif; ?>
    } else {
        alert('<?php echo addslashes(htmlspecialchars($_GET['message'])); ?>');
    }
</script>
<?php endif; ?>

<!-- Instant.page for just-in-time preloading on hover -->
<script src="//instant.page/5.2.0" type="module" crossorigin="anonymous"></script>

</body>
</html>
