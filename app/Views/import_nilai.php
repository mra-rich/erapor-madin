<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_GRADES);

include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="page-shell">
  <div class="page-inner">

    <!-- Page Header -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="page-title">Import Nilai</h1>
        <p class="page-subtitle">Unduh template dan unggah data nilai santri secara massal</p>
      </div>
      <a href="data_nilai" class="btn btn-secondary btn-sm shrink-0">
        <i class="ri-arrow-left-line"></i> Kembali
      </a>
    </div>

    <!-- Wizard Steps Indicator -->
    <div class="mb-8">
      <div class="flex items-center justify-between max-w-lg mx-auto">
        <!-- Step 1 Ind -->
        <div class="flex flex-col items-center flex-1">
          <div id="ind-step-1" class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold ring-4 ring-emerald-100">1</div>
          <span class="text-[11px] font-semibold text-slate-700 mt-1">Unduh Template</span>
        </div>
        <!-- Connector -->
        <div id="conn-1" class="h-0.5 bg-slate-200 flex-1 -mt-4"></div>
        <!-- Step 2 Ind -->
        <div class="flex flex-col items-center flex-1">
          <div id="ind-step-2" class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold">2</div>
          <span class="text-[11px] font-semibold text-slate-400 mt-1">Unggah & Preview</span>
        </div>
      </div>
    </div>

    <!-- WIZARD STEP 1: Download Template -->
    <div id="step-1-card" class="ui-card max-w-xl mx-auto">
      <div class="ui-card-header">
        <h3 class="font-bold text-slate-800">Langkah 1: Unduh Template Excel</h3>
      </div>
      <div class="ui-card-body space-y-4">
        <p class="text-sm text-slate-500">Pilih kelas terlebih dahulu untuk mengunduh template Excel khusus berisi daftar santri kelas tersebut.</p>
        <form action="template_import_excel" method="GET" target="_blank" hx-boost="false" class="space-y-4">
          <?php 
          $no_autosubmit = true;
          $id_kelas_selected = 0; 
          include 'include/filter_kelas.php'; 
          ?>
          <button type="submit" class="btn btn-primary w-full py-3" hx-disable>
            <i class="ri-download-cloud-2-line"></i> Unduh Template Excel
          </button>
        </form>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
          <button type="button" onclick="goToStep(2)" class="btn btn-secondary btn-sm">
            Lanjut ke Unggah <i class="ri-arrow-right-line"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- WIZARD STEP 2: Upload & Preview -->
    <div id="step-2-card" class="ui-card max-w-2xl mx-auto hidden">
      <div class="ui-card-header">
        <h3 class="font-bold text-slate-800">Langkah 2: Unggah & Preview Data</h3>
      </div>
      <div class="ui-card-body space-y-4">
        
        <!-- Upload Form -->
        <div id="upload-zone" class="space-y-4">
          <p class="text-sm text-slate-500">Pilih file Excel template nilai yang telah Anda isi.</p>
          <form id="formImportNilai" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="ui-label">Semester</label>
                <select name="semester" class="ui-select" required>
                  <option value="1" <?= ($_SESSION['semester'] == '1') ? 'selected' : '' ?>>Semester 1 (Ganjil)</option>
                  <option value="2" <?= ($_SESSION['semester'] == '2') ? 'selected' : '' ?>>Semester 2 (Genap)</option>
                </select>
              </div>
              <div>
                <label class="ui-label">File Excel (.xlsx)</label>
                <input type="file" name="file_excel" id="file_excel" accept=".xlsx" class="ui-input py-2" required onchange="onFileSelected()">
              </div>
            </div>
            <button type="button" id="btn-preview" onclick="previewNilai()" class="btn btn-primary w-full py-3">
              <i class="ri-eye-line"></i> Preview & Validasi Data
            </button>
          </form>
        </div>

        <!-- Preview Results Container (Hidden by default) -->
        <div id="preview-container" class="hidden space-y-4">
          <div class="flex items-center justify-between border-b pb-2">
            <h4 class="font-bold text-slate-700">Preview Data Nilai</h4>
            <div id="preview-summary" class="text-xs font-semibold"></div>
          </div>

          <!-- Invalid records list -->
          <div id="invalid-records-wrap" class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
            <h5 class="text-xs font-bold text-red-700 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="ri-error-warning-line"></i> Data Error / Ditolak</h5>
            <ul id="invalid-records-list" class="text-xs text-red-600 list-disc pl-5 space-y-1 max-h-32 overflow-y-auto"></ul>
          </div>

          <!-- Valid records preview table -->
          <div id="valid-records-wrap" class="hidden space-y-2">
            <h5 class="text-xs font-bold text-emerald-700 uppercase tracking-wider flex items-center gap-1.5"><i class="ri-checkbox-circle-line"></i> Data Valid Siap Import</h5>
            <div class="table-scroll-wrap max-h-64 overflow-y-auto">
              <table class="ui-table">
                <thead>
                  <tr>
                    <th>Santri</th>
                    <th>Mapel</th>
                    <th class="text-center w-20">Nilai</th>
                  </tr>
                </thead>
                <tbody id="valid-records-tbody"></tbody>
              </table>
            </div>
          </div>

          <!-- Action buttons for final submit -->
          <div class="pt-4 border-t border-slate-100 flex gap-2">
            <button type="button" onclick="resetUpload()" class="btn btn-secondary flex-1">Ulangi Unggah</button>
            <button type="button" id="btn-submit" onclick="submitImport()" class="btn btn-primary flex-1">
              <i class="ri-save-3-fill"></i> Simpan ke Database
            </button>
          </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-between">
          <button type="button" onclick="goToStep(1)" class="btn btn-secondary btn-sm">
            <i class="ri-arrow-left-line"></i> Kembali ke Langkah 1
          </button>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
let validCache = [];

function goToStep(step) {
  if (step === 1) {
    document.getElementById('step-1-card').classList.remove('hidden');
    document.getElementById('step-2-card').classList.add('hidden');
    // indicators
    document.getElementById('ind-step-2').classList.replace('bg-emerald-600', 'bg-slate-200');
    document.getElementById('ind-step-2').classList.replace('text-white', 'text-slate-500');
    document.getElementById('ind-step-2').classList.remove('ring-4', 'ring-emerald-100');
    document.getElementById('conn-1').classList.replace('bg-emerald-600', 'bg-slate-200');
  } else {
    document.getElementById('step-1-card').classList.add('hidden');
    document.getElementById('step-2-card').classList.remove('hidden');
    // indicators
    document.getElementById('ind-step-2').classList.replace('bg-slate-200', 'bg-emerald-600');
    document.getElementById('ind-step-2').classList.replace('text-slate-500', 'text-white');
    document.getElementById('ind-step-2').classList.add('ring-4', 'ring-emerald-100');
    document.getElementById('conn-1').classList.replace('bg-slate-200', 'bg-emerald-600');
  }
}

function onFileSelected() {}

function previewNilai() {
  const input = document.getElementById('file_excel');
  if (input.files.length === 0) {
    alert('Silakan pilih file Excel terlebih dahulu.');
    return;
  }

  const btn = document.getElementById('btn-preview');
  btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i> Memvalidasi...';
  btn.disabled = true;

  const form = document.getElementById('formImportNilai');
  const formData = new FormData(form);

  fetch('preview_import_nilai.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    btn.innerHTML = '<i class="ri-eye-line"></i> Preview & Validasi Data';
    btn.disabled = false;

    if (data.status === 'error') {
      alert(data.message);
      return;
    }

    validCache = data.valid || [];
    
    // Hide upload zone, show preview
    document.getElementById('upload-zone').classList.add('hidden');
    document.getElementById('preview-container').classList.remove('hidden');

    document.getElementById('preview-summary').innerHTML = 
      '<span class="text-emerald-600 font-bold">' + validCache.length + ' Valid</span> | ' +
      '<span class="text-red-600 font-bold">' + (data.invalid || []).length + ' Error</span>';

    // Render Invalid
    const invalidWrap = document.getElementById('invalid-records-wrap');
    const invalidList = document.getElementById('invalid-records-list');
    if (data.invalid && data.invalid.length > 0) {
      invalidWrap.classList.remove('hidden');
      invalidList.innerHTML = data.invalid.map(item => 
        '<li>Baris ' + item.baris + ': ' + (item.nama || 'Tanpa Nama') + ' (' + (item.mapel || 'Tanpa Mapel') + ') &rarr; <b>' + item.alasan + '</b></li>'
      ).join('');
    } else {
      invalidWrap.classList.add('hidden');
    }

    // Render Valid
    const validWrap = document.getElementById('valid-records-wrap');
    const validTbody = document.getElementById('valid-records-tbody');
    if (validCache.length > 0) {
      validWrap.classList.remove('hidden');
      validTbody.innerHTML = validCache.map(item => 
        '<tr>' +
          '<td>' +
            '<p class="font-semibold text-slate-800 text-xs">' + item.nama + '</p>' +
            '<p class="text-[10px] text-slate-400 font-mono">' + item.nomor_santri + '</p>' +
          '</td>' +
          '<td class="text-xs text-slate-600 font-medium">' + item.nama_mapel + '</td>' +
          '<td class="text-center font-bold text-slate-800">' + item.nilai_angka + '</td>' +
        '</tr>'
      ).join('');
      document.getElementById('btn-submit').disabled = false;
    } else {
      validWrap.classList.add('hidden');
      document.getElementById('btn-submit').disabled = true;
    }
  })
  .catch(err => {
    alert('Terjadi kesalahan sistem.');
    btn.innerHTML = '<i class="ri-eye-line"></i> Preview & Validasi Data';
    btn.disabled = false;
  });
}

function resetUpload() {
  document.getElementById('upload-zone').classList.remove('hidden');
  document.getElementById('preview-container').classList.add('hidden');
  document.getElementById('formImportNilai').reset();
  validCache = [];
}

function submitImport() {
  if (validCache.length === 0) return;

  const btn = document.getElementById('btn-submit');
  btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i> Menyimpan...';
  btn.disabled = true;

  const csrfToken = document.querySelector('input[name="csrf_token"]').value;

  fetch('proses_import_nilai.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      csrf_token: csrfToken,
      import_data: validCache,
      semester: document.querySelector('select[name="semester"]').value
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      window.location.href = 'data_nilai.php?status=success&message=' + encodeURIComponent(data.message);
    } else {
      alert(data.message);
      btn.innerHTML = '<i class="ri-save-3-fill"></i> Simpan ke Database';
      btn.disabled = false;
    }
  })
  .catch(() => {
    alert('Terjadi kesalahan saat menyimpan data.');
    btn.innerHTML = '<i class="ri-save-3-fill"></i> Simpan ke Database';
    btn.disabled = false;
  });
}
</script>

<?php include 'include/footer.php'; ?>
