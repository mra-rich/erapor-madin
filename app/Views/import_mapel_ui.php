<!-- Overlay Import Mapel -->
<div id="overlay-import-mapel" class="fixed inset-0 bg-gray-900/60 z-40 hidden backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeOffcanvasImport('offcanvas-import-mapel')"></div>

<!-- Offcanvas Import Mapel -->
<div id="offcanvas-import-mapel" class="fixed inset-y-0 right-0 z-50 w-full bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col" style="max-width: 500px;">
    <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-teal-50">
        <div>
            <h5 class="text-xl font-bold text-gray-800">Import Pengaturan Mapel</h5>
            <p class="text-sm text-emerald-600 font-medium mt-1">Upload via file Excel (.xlsx)</p>
        </div>
        <button type="button" onclick="closeOffcanvasImport('offcanvas-import-mapel')" class="text-gray-400 bg-white hover:bg-gray-100 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center shadow-sm">
            <i class="ri-close-line text-2xl"></i>
        </button>
    </div>

    <!-- Step 1: Upload Form -->
    <div class="p-6 flex-1 overflow-y-auto flex flex-col items-center" id="import-mapel-step-1">
        <a href="download_template_mapel" target="_blank" hx-disable hx-boost="false" class="w-full inline-flex items-center justify-center px-4 py-3 mb-6 text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 font-medium rounded-xl transition-colors duration-200 shadow-sm" download>
            <i class="ri-download-cloud-2-line mr-2 text-xl"></i>
            Download Template (Terisi Data Saat Ini)
        </a>
        <form id="formImportMapel" class="w-full flex flex-col items-center">
            <div class="w-full">
                <label class="block mb-2 text-sm font-bold text-gray-700">Pilih File Excel <span class="text-red-500">*</span></label>
                <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2.5 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700" id="file_excel_mapel" name="file_excel_mapel" type="file" accept=".xlsx, .xls">
                <p class="mt-2 text-xs text-gray-500">Isi Nama Kitab & Nama Guru. Jika kosong otomatis Non-Aktif.</p>
            </div>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        </form>
    </div>

    <!-- Step 2: Preview Area -->
    <div class="p-6 flex-1 overflow-y-auto hidden flex-col w-full" id="import-mapel-step-2">
        <h4 class="font-bold text-gray-800 mb-4 flex items-center"><i class="ri-search-eye-line mr-2 text-blue-500"></i>Preview Data</h4>
        
        <div id="preview-mapel-loading" class="hidden flex-col items-center justify-center py-8">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600 mb-3"></div>
            <p class="text-sm text-gray-500 font-medium">Membaca file Excel...</p>
        </div>

        <div id="preview-mapel-error" class="hidden p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200"></div>

        <div id="preview-mapel-content" class="hidden w-full">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                    <p class="text-xs text-blue-600 font-bold mb-1">TOTAL DATA</p>
                    <p class="text-2xl font-black text-blue-700" id="count-mapel-total">0</p>
                </div>
                <div class="bg-red-50 p-3 rounded-lg border border-red-100">
                    <p class="text-xs text-red-600 font-bold mb-1">TIDAK VALID (GURU SALAH)</p>
                    <p class="text-2xl font-black text-red-700" id="count-mapel-invalid">0</p>
                </div>
            </div>

            <div class="mb-4 hidden" id="mapel-invalid-container">
                <p class="text-sm font-bold text-red-600 mb-2">Daftar Baris Tidak Valid:</p>
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-3 max-h-40 overflow-y-auto">
                    <ul class="text-sm text-gray-600 space-y-2" id="mapel-invalid-list"></ul>
                </div>
            </div>
            
            <p class="text-sm text-gray-600 mb-2">Apakah Anda yakin ingin memproses data ini? <br>Data yang kosong (Guru/Kitab tidak diisi) otomatis di-set <b>Non-Aktif</b>.</p>
        </div>
    </div>

    <!-- Step 3: Progress Area -->
    <div class="p-6 flex-1 overflow-y-auto hidden flex-col w-full justify-center items-center" id="import-mapel-step-3">
        <div class="w-20 h-20 mb-4 bg-blue-100 rounded-full flex items-center justify-center">
            <i class="ri-upload-cloud-2-line text-4xl text-blue-600 animate-bounce"></i>
        </div>
        <h4 class="font-bold text-gray-800 text-lg mb-2">Memproses Data...</h4>
        <p class="text-sm text-gray-500 mb-6 text-center" id="mapel-progress-text">Mohon tunggu, jangan tutup halaman ini.</p>
        
        <div class="w-full bg-gray-200 rounded-full h-3 mb-2 overflow-hidden">
            <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" id="mapel-progress-bar" style="width: 0%"></div>
        </div>
        <p class="text-xs font-bold text-blue-600" id="mapel-progress-percentage">0%</p>
    </div>

    <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 rounded-bl-3xl" id="mapel-modal-footer">
        <button type="button" onclick="closeOffcanvasImport('offcanvas-import-mapel')" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:ring-4 focus:ring-gray-200 transition-colors">
            Batal
        </button>
        <button type="button" id="btn-preview-mapel" class="px-5 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-colors shadow-sm">
            Preview Data
        </button>
        <button type="button" id="btn-process-mapel" class="hidden px-5 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-colors shadow-sm">
            Mulai Import
        </button>
    </div>
</div>
