/**
 * ERapor - Main Javascript
 * Reusable components and utilities
 */

// Toggle Password Visibility
function togglePassword(inputId = 'password', iconId = 'eye-icon') {
    const passwordInput = document.getElementById(inputId);
    const eyeIcon = document.getElementById(iconId);
    if (!passwordInput || !eyeIcon) return;
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.className = 'ri-eye-line text-lg';
    } else {
        passwordInput.type = 'password';
        eyeIcon.className = 'ri-eye-off-line text-lg';
    }
}

// Close Toast Notification
function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.remove();
    }
}

// Fullscreen Toggle
function toggleFullScreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
            console.log(`Error attempting to enable fullscreen: ${err.message} (${err.name})`);
        });
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}

// Include SweetAlert2 if not present
if (typeof Swal === 'undefined') {
    const script = document.createElement('script');
    script.src = "https://cdn.jsdelivr.net/npm/sweetalert2@11";
    document.head.appendChild(script);
}

// Intercept HTMX confirms
document.body.addEventListener('htmx:confirm', function(e) {
    if (!e.detail.question) {
        return; // Tidak ada atribut hx-confirm, biarkan request berjalan normal
    }
    
    e.preventDefault();
    Swal.fire({
        title: 'Konfirmasi',
        text: e.detail.question,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Lanjutkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            e.detail.issueRequest(true);
        }
    });
});

// Intercept native HTML5 form validation popups globally
document.addEventListener('invalid', function(e) {
    e.preventDefault(); // Mencegah popup bawaan browser yang merusak full screen
    Swal.fire({
        icon: 'warning',
        title: 'Validasi Gagal',
        text: 'Silakan isi kolom yang diperlukan atau pilih item dari daftar.',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Mengerti'
    }).then(() => {
        if (e.target && e.target.focus) {
            e.target.focus();
        }
    });
}, true); // useCapture = true wajib agar event invalid bisa ditangkap secara global

// Helper for non-HTMX confirms
function sweetConfirm(event, element, message) {
    event.preventDefault();
    if (event.stopPropagation) {
        event.stopPropagation();
    }
    Swal.fire({
        title: 'Konfirmasi',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Lanjutkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            if (element.tagName === 'FORM' || element.type === 'submit') {
                const form = element.closest('form');
                if (form) form.submit();
            } else if (element.href) {
                window.location.href = element.href;
            }
        }
    });
    return false;
}

// Helper to trigger htmx-compatible form submission
function htmxSubmit(form) {
    if (form.reportValidity && !form.reportValidity()) return;
    form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
}

// Update icon based on fullscreen state
document.addEventListener("fullscreenchange", function() {
    const icon = document.getElementById('fullscreen-icon');
    if (icon) {
        if (document.fullscreenElement) {
            icon.classList.replace('ri-fullscreen-line', 'ri-fullscreen-exit-line');
        } else {
            icon.classList.replace('ri-fullscreen-exit-line', 'ri-fullscreen-line');
        }
    }
});

// Clean up Flowbite modal/offcanvas classes and re-initialize after HTMX swaps
document.body.addEventListener('htmx:afterSwap', function() {
    // Hapus class overflow-hidden yang mungkin ditinggalkan oleh offcanvas/modal Flowbite saat navigasi
    document.body.classList.remove('overflow-hidden');
    document.body.style.paddingRight = '';
    
    // Hapus elemen backdrop flowbite jika masih tersisa
    const backdrops = document.querySelectorAll('[modal-backdrop], [drawer-backdrop]');
    backdrops.forEach(el => el.remove());

    if (typeof initFlowbite === 'function') {
        initFlowbite();
    }
});

// Sidebar Toggle Logic
function toggleSidebar() {
    document.body.classList.toggle('sidebar-collapsed');
    const isCollapsed = document.body.classList.contains('sidebar-collapsed');
    localStorage.setItem('sidebar-collapsed', isCollapsed ? 'true' : 'false');
    
    // Toggle icon
    const icon = document.getElementById('sidebar-toggle-icon');
    if (icon) {
        if (isCollapsed) {
            icon.classList.replace('ri-menu-fold-line', 'ri-menu-unfold-line');
        } else {
            icon.classList.replace('ri-menu-unfold-line', 'ri-menu-fold-line');
        }
    }
}

// Restore sidebar state on load — tanpa flicker
document.addEventListener('DOMContentLoaded', () => {
    const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    if (isCollapsed) {
        document.body.classList.add('sidebar-collapsed');
        const icon = document.getElementById('sidebar-toggle-icon');
        if (icon) {
            icon.classList.replace('ri-menu-fold-line', 'ri-menu-unfold-line');
        }
    }
    // Hapus class sementara dari <html> yang mencegah flicker
    document.documentElement.classList.remove('sidebar-collapsed-early');
});
