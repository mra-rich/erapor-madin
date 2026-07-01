
<!-- Overlay Import Guru -->
<div id="overlay-import-guru" class="fixed inset-0 bg-gray-900/60 z-40 hidden backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeOffcanvas('offcanvas-import-guru')"></div>

<!-- Offcanvas Import Guru -->
<div id="offcanvas-import-guru" class="fixed inset-y-0 right-0 z-50 w-full bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col" style="max-width: 450px;">
    <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-teal-50">
        <div>
            <h5 class="text-xl font-bold text-gray-800">Import Data Guru</h5>
            <p class="text-sm text-emerald-600 font-medium mt-1">Upload via file Excel (.xlsx)</p>
        </div>
        <button type="button" onclick="closeOffcanvas('offcanvas-import-guru')" class="text-gray-400 bg-white hover:bg-gray-100 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center shadow-sm">
            <i class="ri-close-line text-2xl"></i>
        </button>
    </div>

    <!-- Step 1: Upload Form -->
    <div class="p-6 flex-1 overflow-y-auto flex flex-col items-center" id="import-guru-step-1">
        <a href="download_template_guru" target="_blank" hx-disable hx-boost="false" class="w-full inline-flex items-center justify-center px-4 py-3 mb-6 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-medium rounded-xl transition-colors duration-200 shadow-sm border border-emerald-200" download>
            <i class="ri-download-cloud-2-line mr-2 text-xl"></i>
            Download Template Excel
        </a>
        <form id="formImportGuru" class="w-full flex flex-col items-center">
            <div class="w-full">
                <label class="block mb-2 text-sm font-bold text-gray-700">Pilih File Excel <span class="text-red-500">*</span></label>
                <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2.5 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700" id="file_excel_guru" name="file_excel_guru" type="file" accept=".xlsx, .xls">
                <p class="mt-2 text-xs text-gray-500">Hanya menerima file format .xlsx</p>
            </div>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        </form>
    </div>

    <!-- Step 2: Preview Area -->
    <div class="p-6 flex-1 overflow-y-auto hidden flex-col w-full" id="import-guru-step-2">
        <h4 class="font-bold text-gray-800 mb-4 flex items-center"><i class="ri-search-eye-line mr-2 text-emerald-500"></i>Preview Data</h4>
        
        <div id="preview-guru-loading" class="hidden flex-col items-center justify-center py-8">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-emerald-600 mb-3"></div>
            <p class="text-sm text-gray-500 font-medium">Membaca file Excel...</p>
        </div>

        <div id="preview-guru-error" class="hidden p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200"></div>

        <div id="preview-guru-content" class="hidden w-full">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-emerald-50 p-3 rounded-lg border border-emerald-100">
                    <p class="text-xs text-emerald-600 font-bold mb-1">DATA VALID</p>
                    <p class="text-2xl font-black text-emerald-700" id="count-guru-valid">0</p>
                </div>
                <div class="bg-red-50 p-3 rounded-lg border border-red-100">
                    <p class="text-xs text-red-600 font-bold mb-1">DATA ERROR</p>
                    <p class="text-2xl font-black text-red-700" id="count-guru-error">0</p>
                </div>
            </div>
            
            <div id="guru-error-list" class="hidden mt-4">
                <p class="text-sm font-bold text-red-600 mb-2">Daftar Error:</p>
                <div class="max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                    <ul class="space-y-2 text-sm" id="guru-error-items"></ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="p-6 border-t border-gray-100 bg-gray-50" id="import-guru-footer">
        <button type="button" id="btn-preview-guru-import" onclick="previewImportGuru()" class="w-full text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-bold rounded-xl text-lg px-5 py-4 text-center transition-all shadow-lg shadow-emerald-500/30 flex items-center justify-center">
            <i class="ri-eye-line mr-2"></i> Preview Data
        </button>
        <button type="button" id="btn-confirm-guru-import" onclick="confirmImportGuru()" class="hidden w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-xl text-lg px-5 py-4 text-center transition-all shadow-lg shadow-blue-500/30 flex items-center justify-center">
            <i class="ri-save-line mr-2"></i> Import Sekarang
        </button>
    </div>
</div>
