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
  </style>
  <script src="https://unpkg.com/htmx.org@1.9.11"></script>
</head>

<body hx-boost="true" class="text-gray-800 antialiased selection:bg-emerald-200 selection:text-emerald-900">

<!-- Custom 4-Dots Loader untuk perpindahan halaman -->
<div id="page-loader" class="fixed inset-0 z-[10000] flex items-center justify-center bg-[#f8f9fa] opacity-0 pointer-events-none transition-opacity duration-300">
    <div style="position: relative; width: 36px; height: 36px; animation: spin-4-dots 1.2s linear infinite;">
        <div style="position: absolute; top: 0; left: 0; width: 14px; height: 14px; background-color: #6ee7b7; border-radius: 50%;"></div>
        <div style="position: absolute; top: 0; right: 0; width: 14px; height: 14px; background-color: #a7f3d0; border-radius: 50%;"></div>
        <div style="position: absolute; bottom: 0; right: 0; width: 14px; height: 14px; background-color: #d1fae5; border-radius: 50%;"></div>
        <div style="position: absolute; bottom: 0; left: 0; width: 14px; height: 14px; background-color: #22c55e; border-radius: 50%;"></div>
    </div>
    <style>
        @keyframes spin-4-dots {
            100% { transform: rotate(360deg); }
        }
        #page-loader.htmx-request {
            opacity: 1 !important;
            pointer-events: auto !important;
        }
    </style>
</div>