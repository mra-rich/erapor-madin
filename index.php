<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem E-Rapor | Login</title>
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: "Plus Jakarta Sans", sans-serif;
        }
        h1, h2, h3, h4, h5, h6, .brand-font {
            font-family: "Outfit", sans-serif;
        }
        
        /* Premium Background Gradient Animation */
        .bg-mesh {
            background: linear-gradient(125deg, #ecfeff 0%, #dcfce7 40%, #f3e8ff 80%, #fdf4ff 100%);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Glassmorphism Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05), 0 0 0 1px rgba(255,255,255,0.8) inset;
        }

        .input-glass {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .input-glass:focus {
            background: rgba(255, 255, 255, 0.95);
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .floating-shape {
            position: absolute;
            filter: blur(60px);
            z-index: 0;
            opacity: 0.6;
            animation: float 10s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }
    </style>
</head>

<body class="bg-mesh min-h-screen relative overflow-hidden flex items-center justify-center">
    
    <!-- Abstract Decorative Shapes -->
    <div class="floating-shape w-96 h-96 bg-emerald-300 rounded-full top-[-10%] left-[-10%] mix-blend-multiply"></div>
    <div class="floating-shape w-96 h-96 bg-cyan-300 rounded-full bottom-[-10%] right-[-5%] mix-blend-multiply" style="animation-delay: 2s;"></div>
    <div class="floating-shape w-80 h-80 bg-purple-300 rounded-full top-[20%] right-[10%] mix-blend-multiply" style="animation-delay: 4s;"></div>

    <div class="w-full max-w-md px-6 relative z-10">
        <!-- Logo Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-xl shadow-emerald-500/10 mb-5 border border-emerald-50 transform hover:scale-105 transition-transform duration-300">
                <img src="assets/img/logo.png" alt="Logo" class="w-14 h-14 object-contain" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3003/3003511.png'" />
            </div>
            <h1 class="text-3xl font-bold text-gray-800 brand-font tracking-tight">E-Rapor</h1>
            <p class="text-sm font-medium text-emerald-600 mt-2 tracking-wide uppercase">MDTA Asshiddiqiyah</p>
        </div>

        <!-- Login Card -->
        <div class="glass-card rounded-3xl p-8 sm:p-10 relative overflow-hidden">
            <!-- decorative shine -->
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/40 to-transparent pointer-events-none"></div>

            <div class="relative">
                <div class="mb-8 text-center">
                    <h2 class="text-2xl font-bold text-gray-800 brand-font">Selamat Datang 👋</h2>
                    <p class="text-gray-500 text-sm mt-2">Silakan masuk ke akun Anda</p>
                </div>

                <form action="proses_login.php" method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="ri-user-3-line text-gray-400 text-lg"></i>
                            </div>
                            <input type="text" name="username" id="username" class="input-glass w-full pl-11 pr-4 py-3.5 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none" placeholder="Masukkan username" required />
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="ri-lock-2-line text-gray-400 text-lg"></i>
                            </div>
                            <input type="password" name="password" id="password" class="input-glass w-full pl-11 pr-12 py-3.5 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none" placeholder="••••••••" required />
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-emerald-500 transition-colors">
                                <i id="eye-icon" class="ri-eye-off-line text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white font-semibold py-4 rounded-xl shadow-lg shadow-gray-900/20 transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 group mt-8">
                        <span>Masuk Sekarang</span>
                        <i class="ri-arrow-right-line group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Footer info -->
        <p class="text-center text-gray-500 text-xs mt-8 font-medium">
            &copy; <?= date('Y') ?> MDTA Asshiddiqiyah. All rights reserved.
        </p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <!-- Modern Toast Notification -->
        <div id="toast-error" class="fixed bottom-5 right-5 flex items-center w-full max-w-xs p-4 space-x-3 text-gray-500 bg-white rounded-2xl shadow-2xl glass-card animate-bounce" role="alert">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-10 h-10 text-red-500 bg-red-100 rounded-xl">
                <i class="ri-error-warning-fill text-xl"></i>
            </div>
            <div class="ms-3 text-sm font-medium text-gray-800">
                <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES) ?>
            </div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 transition-colors" onclick="document.getElementById('toast-error').remove();" aria-label="Close">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.className = 'ri-eye-line text-lg';
            } else {
                passwordInput.type = 'password';
                eyeIcon.className = 'ri-eye-off-line text-lg';
            }
        }
    </script>
</body>
</html>