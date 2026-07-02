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

<!-- Overlay Loading Layar Penuh -->
<div id="page-loader" class="fixed inset-0 z-[10000] flex items-center justify-center bg-white/70 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
    <div class="flex flex-col items-center bg-white px-8 py-6 rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] border border-emerald-100" style="border-radius: 1rem; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
        <div style="margin-bottom: 1rem;">
            <!-- Self-contained SVG Spinner (Guaranteed to spin) -->
            <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" stroke="#059669">
                <g fill="none" fill-rule="evenodd">
                    <g transform="translate(1 1)" stroke-width="4">
                        <circle stroke-opacity=".2" cx="24" cy="24" r="24"/>
                        <path d="M48 24c0-13.255-10.745-24-24-24">
                            <animateTransform attributeName="transform" type="rotate" from="0 24 24" to="360 24 24" dur="0.6s" repeatCount="indefinite"/>
                        </path>
                    </g>
                </g>
            </svg>
        </div>
        <p class="text-emerald-800 font-semibold font-outfit tracking-wide" style="animation: htmx-pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;">Memuat data...</p>
        <style>
            @keyframes htmx-pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: .5; }
            }
        </style>
    </div>
</div>