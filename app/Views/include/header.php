<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard E-Rapor</title>
  <?php
    // Base URL untuk asset (relatif terhadap root project)
    // .htaccess sudah handle rewrite: assets/* → public/assets/*, css/* → public/css/*
    // Jadi kita cukup pakai path relatif.
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $assetBase = rtrim($scriptDir, '/');
    if (str_contains($assetBase, '/public')) {
        $assetBase = dirname($assetBase);
    }
    $assetBase = rtrim($assetBase, '/') . '/';
  ?>
  <link rel="icon" type="image/x-icon" href="<?= $assetBase ?>assets/img/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" />
  <link href="<?= $assetBase ?>css/style.css" rel="stylesheet">
  
  <!-- Premium Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Removed inline styles -->

  <!-- Blocking script: baca state sidebar SEBELUM render agar tidak flicker -->
  <script>
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
      document.documentElement.classList.add('sidebar-collapsed-early');
    }
  </script>
  <style>
    /* Terapkan collapse SEBELUM JS DOMContentLoaded berjalan */
    html.sidebar-collapsed-early #logo-sidebar,
    html.sidebar-collapsed-early aside#logo-sidebar {
      width: 5rem !important;
    }
    html.sidebar-collapsed-early .sm\:ml-64,
    html.sidebar-collapsed-early .p-4.sm\:ml-64 {
      margin-left: 5rem !important;
    }

    /* Animasi Loading Global (HTMX) */
    #page-loader.htmx-request {
      opacity: 1 !important;
      pointer-events: auto !important;
    }
  </style>
  <script src="https://unpkg.com/htmx.org@1.9.11"></script>
</head>

<body hx-boost="true" hx-indicator="#page-loader" class="text-gray-800 antialiased selection:bg-emerald-200 selection:text-emerald-900">

<!-- Premium Linear-style Loader -->
<div id="page-loader" class="fixed inset-0 z-[10000] flex items-center justify-center bg-slate-50/30 backdrop-blur-[2px] opacity-0 pointer-events-none transition-opacity duration-300">
    <div class="flex items-center gap-3 bg-white/90 backdrop-blur-xl px-5 py-3 rounded-full shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-slate-100 transform scale-95 transition-transform duration-300" id="loader-pill">
        <!-- SVG Spinner Ultra-Fast -->
        <div style="width: 1.25rem; height: 1.25rem; position: relative;">
            <svg style="animation: premium-spin 0.4s linear infinite; width: 100%; height: 100%;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="#f1f5f9" stroke-width="3"></circle>
                <path d="M12 2a10 10 0 0 1 10 10" stroke="#10b981" stroke-width="3" stroke-linecap="round"></path>
            </svg>
        </div>
        <span class="text-slate-600 font-medium font-outfit text-sm tracking-wide">Memuat...</span>
        <style>
            @keyframes premium-spin {
                to { transform: rotate(360deg); }
            }
            #page-loader.htmx-request #loader-pill {
                transform: scale(1) !important;
            }
        </style>
    </div>
</div>