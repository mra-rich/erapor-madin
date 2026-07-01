<script>
    let validKelasDataCache = [];
    
    function previewImportKelas() {
        const fileInput = document.getElementById('file_excel_kelas');
        if (!fileInput.files.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Silakan pilih file Excel terlebih dahulu!',
                confirmButtonColor: '#10b981'
            });
            return;
        }

        const formData = new FormData(document.getElementById('formImportKelas'));
        const btn = document.getElementById('btn-preview-kelas-import');
        
        btn.innerHTML = '<i class="ri-loader-4-line animate-spin mr-2"></i> Memproses...';
        btn.disabled = true;

        document.getElementById('import-kelas-step-1').classList.add('hidden');
        document.getElementById('import-kelas-step-2').classList.remove('hidden');
        document.getElementById('import-kelas-step-2').classList.add('flex');
        
        document.getElementById('preview-kelas-loading').classList.remove('hidden');
        document.getElementById('preview-kelas-loading').classList.add('flex');
        document.getElementById('preview-kelas-error').classList.add('hidden');
        document.getElementById('preview-kelas-content').classList.add('hidden');
        document.getElementById('kelas-error-list').classList.add('hidden');
        
        document.getElementById('btn-preview-kelas-import').classList.add('hidden');
        document.getElementById('btn-confirm-kelas-import').classList.remove('hidden');
        document.getElementById('btn-confirm-kelas-import').disabled = true;

        fetch('preview_import_kelas.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('preview-kelas-loading').classList.add('hidden');
            document.getElementById('preview-kelas-loading').classList.remove('flex');
            
            if (data.status === 'success') {
                document.getElementById('preview-kelas-content').classList.remove('hidden');
                
                document.getElementById('count-kelas-valid').textContent = data.valid_count;
                document.getElementById('count-kelas-error').textContent = data.invalid_count;
                
                validKelasDataCache = data.valid_data;
                
                if (data.invalid_count > 0) {
                    const errorList = document.getElementById('kelas-error-items');
                    errorList.innerHTML = '';
                    data.invalid_data.forEach(item => {
                        errorList.innerHTML += `<li class="flex items-start"><i class="ri-error-warning-fill text-red-500 mr-2 mt-0.5"></i> <span>Baris ${item.baris}: Kelas ${item.kelas} ${item.rombel} - ${item.alasan}</span></li>`;
                    });
                    document.getElementById('kelas-error-list').classList.remove('hidden');
                }
                
                if (data.valid_count > 0) {
                    document.getElementById('btn-confirm-kelas-import').disabled = false;
                }
            } else {
                document.getElementById('preview-kelas-error').classList.remove('hidden');
                document.getElementById('preview-kelas-error').textContent = data.message;
                document.getElementById('btn-confirm-kelas-import').disabled = true;
            }
        })
        .catch(error => {
            document.getElementById('preview-kelas-loading').classList.add('hidden');
            document.getElementById('preview-kelas-error').classList.remove('hidden');
            document.getElementById('preview-kelas-error').textContent = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        })
        .finally(() => {
            btn.innerHTML = '<i class="ri-eye-line mr-2"></i> Preview Data';
            btn.disabled = false;
        });
    }

    function confirmImportKelas() {
        if (validKelasDataCache.length === 0) {
            Swal.fire('Error', 'Tidak ada data valid untuk diimport', 'error');
            return;
        }

        const btn = document.getElementById('btn-confirm-kelas-import');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="ri-loader-4-line animate-spin mr-2"></i> Menyimpan...';
        btn.disabled = true;

        fetch('proses_import_kelas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                csrf_token: document.querySelector('input[name="csrf_token"]').value,
                import_data: validKelasDataCache
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    confirmButtonColor: '#3085d6',
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message,
                    confirmButtonColor: '#d33',
                });
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            Swal.fire('Error', 'Terjadi kesalahan sistem saat menyimpan data', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // Extending closeOffcanvas to handle our kelas modal reset
    const originalCloseOffcanvasKelas = window.closeOffcanvas;
    window.closeOffcanvas = function(id) {
        if (originalCloseOffcanvasKelas) originalCloseOffcanvasKelas(id);
        else {
            document.getElementById(id).classList.add('translate-x-full');
            document.getElementById('overlay-' + id.replace('offcanvas-', '')).classList.add('opacity-0');
            setTimeout(() => {
                document.getElementById('overlay-' + id.replace('offcanvas-', '')).classList.add('hidden');
            }, 300);
        }
        
        if (id === 'offcanvas-import-kelas') {
            document.getElementById('import-kelas-step-1').classList.remove('hidden');
            document.getElementById('import-kelas-step-2').classList.add('hidden');
            document.getElementById('btn-preview-kelas-import').classList.remove('hidden');
            document.getElementById('btn-confirm-kelas-import').classList.add('hidden');
            document.getElementById('file_excel_kelas').value = '';
            validKelasDataCache = [];
        }
    };
    
    // Polyfill for openOffcanvas if not exist globally
    if (typeof window.openOffcanvas === 'undefined') {
        window.openOffcanvas = function(id) {
            document.getElementById('overlay-' + id.replace('offcanvas-', '')).classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('overlay-' + id.replace('offcanvas-', '')).classList.remove('opacity-0');
                document.getElementById(id).classList.remove('translate-x-full');
            }, 10);
        };
    }
</script>
