
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
    <a href="dashboard" hx-get="dashboard" hx-target="body" hx-push-url="true" hx-indicator="#page-loader" class="flex flex-col items-center justify-center w-full py-1 rounded-full transition-all <?= $tab_active == 'dashboard' ? 'text-emerald-700 bg-emerald-50/80 scale-105 shadow-sm' : 'text-slate-400 hover:text-slate-600' ?>">
        <i class="<?= $tab_active == 'dashboard' ? 'ri-dashboard-fill' : 'ri-dashboard-line' ?> text-xl mb-0.5"></i>
        <span class="text-[9px] font-bold tracking-wide">Home</span>
    </a>

    <!-- Tab: Data (Khusus Admin/Wali) -->
    <?php if (in_array($peran, ['Admin', 'Kepala Madrasah', 'Wali Kelas'])): ?>
    <a href="data_santri" hx-get="data_santri" hx-target="body" hx-push-url="true" hx-indicator="#page-loader" class="flex flex-col items-center justify-center w-full py-1 rounded-full transition-all <?= $tab_active == 'data' ? 'text-emerald-700 bg-emerald-50/80 scale-105 shadow-sm' : 'text-slate-400 hover:text-slate-600' ?>">
        <i class="<?= $tab_active == 'data' ? 'ri-folder-user-fill' : 'ri-folder-user-line' ?> text-xl mb-0.5"></i>
        <span class="text-[9px] font-bold tracking-wide">Data</span>
    </a>
    <?php endif; ?>

    <!-- Tab: Nilai -->
    <a href="penilaian_mapel" hx-get="penilaian_mapel" hx-target="body" hx-push-url="true" hx-indicator="#page-loader" class="flex flex-col items-center justify-center w-full py-1 rounded-full transition-all <?= $tab_active == 'nilai' ? 'text-emerald-700 bg-emerald-50/80 scale-105 shadow-sm' : 'text-slate-400 hover:text-slate-600' ?>">
        <i class="<?= $tab_active == 'nilai' ? 'ri-edit-box-fill' : 'ri-edit-box-line' ?> text-xl mb-0.5"></i>
        <span class="text-[9px] font-bold tracking-wide">Nilai</span>
    </a>

    <!-- Tab: Evaluasi/Rapor (Khusus Admin/Wali) -->
    <?php if (in_array($peran, ['Admin', 'Wali Kelas'])): ?>
    <a href="evaluasi_wali" hx-get="evaluasi_wali" hx-target="body" hx-push-url="true" hx-indicator="#page-loader" class="flex flex-col items-center justify-center w-full py-1 rounded-full transition-all <?= $tab_active == 'evaluasi' ? 'text-emerald-700 bg-emerald-50/80 scale-105 shadow-sm' : 'text-slate-400 hover:text-slate-600' ?>">
        <i class="<?= $tab_active == 'evaluasi' ? 'ri-survey-fill' : 'ri-survey-line' ?> text-xl mb-0.5"></i>
        <span class="text-[9px] font-bold tracking-wide">Evaluasi</span>
    </a>
    <?php endif; ?>

    <!-- Tab: Rapor (Khusus Admin/Wali) -->
    <?php if (in_array($peran, ['Admin', 'Kepala Madrasah', 'Wali Kelas'])): ?>
    <a href="cetak_rapot" hx-get="cetak_rapot" hx-target="body" hx-push-url="true" hx-indicator="#page-loader" class="flex flex-col items-center justify-center w-full py-1 rounded-full transition-all <?= $tab_active == 'rapor' ? 'text-emerald-700 bg-emerald-50/80 scale-105 shadow-sm' : 'text-slate-400 hover:text-slate-600' ?>">
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

<!-- PWA & Install Prompt Logic -->
<div id="pwa-install-modal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
  <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-slate-100 flex flex-col items-center text-center">
    <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center border border-emerald-100 mb-4 shadow-sm">
      <img src="/assets/img/logo.png" alt="Logo" class="w-10 h-10 object-contain">
    </div>
    <h3 class="text-lg font-bold text-slate-800">Pasang Aplikasi E-Rapor</h3>
    <p class="text-xs text-slate-500 mt-2 leading-relaxed">Instal aplikasi untuk mempermudah akses pengisian nilai dan data santri langsung dari layar HP Anda.</p>
    
    <button type="button" id="btn-pwa-install" class="w-full mt-5 py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 transition-all flex items-center justify-center gap-2">
      <i class="ri-download-cloud-2-line text-lg"></i> Instal Sekarang
    </button>
    <button type="button" id="btn-pwa-close" class="mt-2 text-xs text-slate-400 hover:text-slate-600 font-semibold py-2">
      Nanti Saja
    </button>
  </div>
</div>

<script>
    // 1. Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/service-worker.js')
                .then(reg => console.log('SW Registered', reg))
                .catch(err => console.log('SW Reg Failed', err));
        });
    }

    // 2. Install Prompt Handler
    (function() {
        let deferredPrompt;
        const modal = document.getElementById('pwa-install-modal');
        const btnInstall = document.getElementById('btn-pwa-install');
        const btnClose = document.getElementById('btn-pwa-close');

        if (!modal || !btnInstall || !btnClose) return;

        // Cek apakah user menolak/menutup modal sebelumnya dalam sesi ini
        const isDismissed = sessionStorage.getItem('pwa_install_dismissed');

        // Cek display-mode standalone (sudah di-install)
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;

        if (isStandalone) {
            console.log('App is running in standalone mode (already installed).');
            return;
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent Chrome 67 and earlier from automatically showing the prompt
            e.preventDefault();
            // Stash the event so it can be triggered later.
            deferredPrompt = e;

            // Jika belum di-install dan tidak ada dismissed tag, tampilkan modal
            if (!isDismissed) {
                modal.classList.remove('hidden');
            }
        });

        btnInstall.addEventListener('click', async () => {
            if (!deferredPrompt) return;
            modal.classList.add('hidden');
            // Show the install prompt
            deferredPrompt.prompt();
            // Wait for the user to respond to the prompt
            const { outcome } = await deferredPrompt.userChoice;
            console.log(`User response to install: ${outcome}`);
            deferredPrompt = null;
        });

        btnClose.addEventListener('click', () => {
            modal.classList.add('hidden');
            // Simpan state dismissed di sessionStorage (jika browser di-refresh tetap ingat di sesi ini, tidak mengganggu terus)
            // Tapi jika browser ditutup lalu dibuka lagi nanti, prompt akan muncul lagi.
            sessionStorage.setItem('pwa_install_dismissed', '1');
        });
    })();
</script>

</body>
</html>
