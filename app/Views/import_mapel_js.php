<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnPreviewMapel = document.getElementById('btn-preview-mapel');
    const btnProcessMapel = document.getElementById('btn-process-mapel');
    const fileInputMapel = document.getElementById('file_excel_mapel');
    let importedMapelData = null;

    if (btnPreviewMapel) {
        btnPreviewMapel.addEventListener('click', function() {
            if (!fileInputMapel.files || fileInputMapel.files.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Oops...', text: 'Pilih file Excel terlebih dahulu!' });
                return;
            }

            const formData = new FormData(document.getElementById('formImportMapel'));
            
            // UI State Change
            document.getElementById('import-mapel-step-1').classList.add('hidden');
            document.getElementById('import-mapel-step-2').classList.remove('hidden');
            document.getElementById('import-mapel-step-2').classList.add('flex');
            document.getElementById('preview-mapel-loading').classList.remove('hidden');
            document.getElementById('preview-mapel-loading').classList.add('flex');
            document.getElementById('preview-mapel-content').classList.add('hidden');
            document.getElementById('preview-mapel-error').classList.add('hidden');
            btnPreviewMapel.disabled = true;

            fetch('preview_import_mapel.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('preview-mapel-loading').classList.add('hidden');
                document.getElementById('preview-mapel-loading').classList.remove('flex');
                
                if (data.success) {
                    importedMapelData = data.data;
                    document.getElementById('preview-mapel-content').classList.remove('hidden');
                    document.getElementById('count-mapel-total').textContent = data.total;
                    document.getElementById('count-mapel-invalid').textContent = data.invalid_count;
                    
                    const invalidContainer = document.getElementById('mapel-invalid-container');
                    const invalidList = document.getElementById('mapel-invalid-list');
                    
                    if (data.invalid_count > 0) {
                        invalidContainer.classList.remove('hidden');
                        invalidList.innerHTML = '';
                        data.invalid_rows.forEach(row => {
                            invalidList.innerHTML += `<li>Baris ${row.row}: ${row.message}</li>`;
                        });
                        
                        // Disable process button if there are invalid rows
                        btnPreviewMapel.classList.add('hidden');
                        btnProcessMapel.classList.add('hidden');
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Tidak Valid',
                            text: 'Ada nama guru yang tidak sesuai dengan database. Silakan perbaiki file Excel Anda dan upload ulang.',
                        }).then(() => {
                            resetImportMapelUI();
                        });
                    } else {
                        invalidContainer.classList.add('hidden');
                        btnPreviewMapel.classList.add('hidden');
                        btnProcessMapel.classList.remove('hidden');
                    }
                } else {
                    document.getElementById('preview-mapel-error').classList.remove('hidden');
                    document.getElementById('preview-mapel-error').textContent = data.message;
                    btnPreviewMapel.disabled = false;
                }
            })
            .catch(error => {
                document.getElementById('preview-mapel-loading').classList.add('hidden');
                document.getElementById('preview-mapel-loading').classList.remove('flex');
                document.getElementById('preview-mapel-error').classList.remove('hidden');
                document.getElementById('preview-mapel-error').textContent = 'Terjadi kesalahan sistem.';
                btnPreviewMapel.disabled = false;
            });
        });
    }

    if (btnProcessMapel) {
        btnProcessMapel.addEventListener('click', function() {
            if (!importedMapelData || importedMapelData.length === 0) return;

            document.getElementById('import-mapel-step-2').classList.add('hidden');
            document.getElementById('import-mapel-step-2').classList.remove('flex');
            document.getElementById('import-mapel-step-3').classList.remove('hidden');
            document.getElementById('import-mapel-step-3').classList.add('flex');
            document.getElementById('mapel-modal-footer').classList.add('hidden');

            const totalData = importedMapelData.length;
            let currentIdx = 0;
            const batchSize = 20;

            function processBatchMapel() {
                const batch = importedMapelData.slice(currentIdx, currentIdx + batchSize);
                if (batch.length === 0) {
                    // Selesai
                    document.getElementById('mapel-progress-bar').style.width = '100%';
                    document.getElementById('mapel-progress-percentage').textContent = '100%';
                    document.getElementById('mapel-progress-text').textContent = 'Selesai!';
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data pengaturan mapel berhasil diimport!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                    return;
                }

                const formData = new FormData();
                formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
                formData.append('batch_data', JSON.stringify(batch));

                fetch('proses_import_mapel.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentIdx += batchSize;
                        const percentage = Math.min(100, Math.round((currentIdx / totalData) * 100));
                        document.getElementById('mapel-progress-bar').style.width = percentage + '%';
                        document.getElementById('mapel-progress-percentage').textContent = percentage + '%';
                        
                        setTimeout(processBatchMapel, 300); // delay dikit biar animasi keliatan
                    } else {
                        Swal.fire('Error', data.message, 'error');
                        document.getElementById('mapel-modal-footer').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    document.getElementById('mapel-modal-footer').classList.remove('hidden');
                });
            }

            processBatchMapel();
        });
    }
});

function resetImportMapelUI() {
    document.getElementById('formImportMapel').reset();
    document.getElementById('import-mapel-step-1').classList.remove('hidden');
    document.getElementById('import-mapel-step-2').classList.add('hidden');
    document.getElementById('import-mapel-step-2').classList.remove('flex');
    document.getElementById('import-mapel-step-3').classList.add('hidden');
    document.getElementById('import-mapel-step-3').classList.remove('flex');
    
    document.getElementById('btn-preview-mapel').classList.remove('hidden');
    document.getElementById('btn-preview-mapel').disabled = false;
    document.getElementById('btn-process-mapel').classList.add('hidden');
    document.getElementById('mapel-modal-footer').classList.remove('hidden');
}
</script>
