<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_VIEW_REPORTS);

// Jika Wali Kelas, ambil kelas sendiri untuk auto-select
$wali_kelas_id = null;
if ($_SESSION['peran'] === 'Wali Kelas') {
    $id_pengguna_wali = $_SESSION['id_pengguna'];
    $q_wali = mysqli_query($koneksi, "SELECT id_kelas FROM kelas WHERE id_wali_kelas = '$id_pengguna_wali' LIMIT 1");
    if ($q_wali && $row_wali = mysqli_fetch_assoc($q_wali)) {
        $wali_kelas_id = $row_wali['id_kelas'];
    }
}

// Ambil semester aktif dari pengaturan
$q_peng = mysqli_query($koneksi, "SELECT semester FROM pengaturan LIMIT 1");
$peng_aktif = mysqli_fetch_assoc($q_peng);
$semester_aktif_rapot = intval($peng_aktif['semester'] ?? 1);

include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 border-2 border-transparent mt-14">
        
        <!-- Header Halaman -->
        <div class="mb-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Cetak Dokumen & Rapor</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola dan cetak sampul, biodata, rapor, serta leger nilai santri.</p>
            </div>
            
            <!-- Filter Kanan -->
            <form id="formPencarian" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <?php if ($_SESSION['peran'] !== 'Wali Kelas'): ?>
                    <?php 
                    $no_autosubmit = true;
                    $id_kelas_selected = isset($id_kelas) ? $id_kelas : (isset($kelas_aktif) ? $kelas_aktif : 0); 
                    include 'include/filter_kelas.php'; 
                    ?>
                <?php else: ?>
                <input type="hidden" name="kelas" id="kelas" value="<?= $wali_kelas_id ?>">
                <?php endif; ?>
                
                <div class="flex-1 w-full min-w-[150px]">
                    <label class="block mb-2 text-sm font-bold text-gray-700">Semester</label>
                    <select name="semester" id="semester"
                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 shadow-sm font-semibold cursor-pointer">
                        <option value="1">Ganjil</option>
                        <option value="2">Genap</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- State Kosong / Petunjuk Awal -->
        <div id="empty-state" class="border border-blue-200 bg-blue-50/50 rounded-xl p-12 flex flex-col items-center justify-center text-center">
            <div class="text-blue-500 mb-4">
                <i class="ri-search-eye-line text-6xl"></i>
            </div>
            <h3 class="text-xl font-bold text-blue-800 mb-3">Pilih Kelas dan Semester</h3>
            <p class="text-blue-600 max-w-lg text-sm leading-relaxed">
                Silakan pilih <b>Kelas</b> dan <b>Semester</b> dari menu dropdown di atas, lalu klik tombol <b class="font-semibold text-blue-700">Tampilkan Daftar Santri</b> untuk mulai memuat daftar santri dan mencetak dokumen rapor.
            </p>
        </div>

        <!-- Tabel Daftar Siswa -->
        <div id="tabel-container" class="hidden">
            
            <!-- Toolbar Cetak Kelas & Pencarian -->
            <div class="mb-4 flex flex-wrap gap-2 justify-end items-center bg-gray-50 p-3 rounded-lg border">
                
                <div class="mr-auto my-auto flex flex-wrap items-center gap-4">
                    <div class="relative w-96">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="ri-search-line text-gray-400"></i>
                        </div>
                        <input type="text" id="searchInput" class="bg-white border border-gray-200 text-gray-800 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2 py-1.5 shadow-sm transition-colors" placeholder="Cari santri...">
                    </div>
                </div>

                <div class="w-px h-8 bg-gray-200 mx-2"></div>
                <span class="font-semibold text-gray-700 ml-2 mr-2"><i class="ri-printer-fill text-blue-600 mr-1"></i> Cetak Semua:</span>

                <button onclick="bukaCetakKelas('cetak_sampul_kelas.php')" class="text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 hover:border-blue-300 focus:ring-2 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center transition-colors shadow-sm" title="Cetak Semua Sampul">
                    <i class="ri-book-line mr-1.5"></i> Sampul
                </button>
                <button onclick="bukaCetakKelas('cetak_biodata_kelas.php')" class="text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100 hover:border-amber-300 focus:ring-2 focus:ring-amber-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center transition-colors shadow-sm" title="Cetak Semua Identitas">
                    <i class="ri-user-line mr-1.5"></i> Identitas
                </button>
                <button onclick="bukaCetakKelas('preview_rapot_kelas.php')" class="text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 hover:border-emerald-300 focus:ring-2 focus:ring-emerald-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center transition-colors shadow-sm" title="Cetak Semua Rapor">
                    <i class="ri-file-text-line mr-1.5"></i> Rapor
                </button>
                <button onclick="bukaCetakKelas('preview_leger.php')" class="text-gray-700 bg-gray-50 border border-gray-200 hover:bg-gray-100 hover:border-gray-300 focus:ring-2 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center inline-flex items-center transition-colors shadow-sm" title="Preview Leger Nilai">
                    <i class="ri-table-2 mr-1.5"></i> Leger
                </button>
                <button onclick="bukaCetakKelas('cetak_semua_kelas.php')" class="text-white bg-blue-600 border border-blue-600 hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center shadow-md transition-all" title="Cetak Seluruh Laporan Sekaligus">
                    <i class="ri-printer-line mr-1.5"></i> Semua
                </button>
            </div>

            <div class="overflow-x-auto relative shadow-md sm:rounded-lg border border-gray-200">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400 border-b">
                        <tr>
                            <th scope="col" class="py-3 px-6 text-center w-16">No</th>
                            <th scope="col" class="py-3 px-6 w-48">NIS / NISN</th>
                            <th scope="col" class="py-3 px-6">Nama Santri</th>
                            <th scope="col" class="py-3 px-6">Tempat, Tgl Lahir</th>
                            <?php if ($semester_aktif_rapot == 2): ?>
                            <th scope="col" class="py-3 px-6">Status Kenaikan</th>
                            <?php endif; ?>
                            <th scope="col" class="py-3 px-6 text-center">Aksi Dokumen</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-siswa">
                        <!-- Data siswa akan di-render di sini -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
    async function tampilkanSiswa() {
        const selectKelas = document.querySelector('select[name="kelas"]');
        const hiddenKelas = document.getElementById('kelas');
        const kelas = selectKelas ? selectKelas.value : (hiddenKelas ? hiddenKelas.value : '');
        const semester = document.getElementById('semester').value;

        if (!kelas) {
            alert('Silakan pilih kelas terlebih dahulu!');
            return;
        }

        const tbody = document.getElementById('tbody-siswa');
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8"><i class="ri-loader-4-line animate-spin text-4xl text-blue-500"></i><p class="mt-2 text-gray-500">Memuat data santri...</p></td></tr>';
        
        // Sembunyikan empty state, tampilkan tabel
        document.getElementById('empty-state').classList.add('hidden');
        document.getElementById('tabel-container').classList.remove('hidden');

        try {
            const response = await fetch('get_siswa_rapot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'kelas=' + encodeURIComponent(kelas)
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            
            if (data.status === 'success') {
                tbody.innerHTML = '';
                
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-6 text-gray-500">Tidak ada data siswa di kelas ini.</td></tr>';
                    return;
                }

                data.data.forEach((siswa, index) => {
                    const tr = document.createElement('tr');
                    tr.className = 'bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-blue-50 transition duration-150';
                    
                    tr.innerHTML = `
                        <td class="py-3 px-6 text-center font-medium text-gray-900">${index + 1}</td>
                        <td class="py-3 px-6 text-gray-700 whitespace-nowrap">
                            ${siswa.nis} <span class="text-gray-400">/</span> ${siswa.nisn}
                        </td>
                        <td class="py-3 px-6 font-bold text-gray-900 uppercase">${siswa.nama}</td>
                        <td class="py-3 px-6 text-gray-700">${siswa.tempat_lahir}, ${siswa.tanggal_lahir}</td>
                        <?php if ($semester_aktif_rapot == 2): ?>
                        <td class="py-3 px-6">
                            <span class="${siswa.status_kenaikan === 'Naik' ? 'bg-emerald-100 text-emerald-800' : (siswa.status_kenaikan === 'Tidak' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')} text-xs font-medium px-2.5 py-0.5 rounded-full border border-gray-200">
                                ${siswa.status_kenaikan === 'Tidak' ? 'Tidak Naik' : siswa.status_kenaikan}
                            </span>
                        </td>
                        <?php endif; ?>
                        <td class="py-3 px-6">
                            <div class="flex justify-center gap-2">
                                <button onclick="bukaCetak('cetak_sampul.php', ${siswa.id_siswa})" class="text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 hover:border-blue-300 focus:ring-2 focus:ring-blue-300 font-medium rounded-md text-xs px-2.5 py-1.5 text-center inline-flex items-center transition-colors" title="Cetak Sampul">
                                    <i class="ri-book-line mr-1"></i> Sampul
                                </button>
                                <button onclick="bukaCetak('cetak_biodata.php', ${siswa.id_siswa})" class="text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100 hover:border-amber-300 focus:ring-2 focus:ring-amber-300 font-medium rounded-md text-xs px-2.5 py-1.5 text-center inline-flex items-center transition-colors" title="Cetak Identitas">
                                    <i class="ri-user-line mr-1"></i> Identitas
                                </button>
                                <button onclick="bukaCetak('preview_rapot.php', ${siswa.id_siswa})" class="text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 hover:border-emerald-300 focus:ring-2 focus:ring-emerald-300 font-medium rounded-md text-xs px-2.5 py-1.5 text-center inline-flex items-center transition-colors" title="Preview Rapor">
                                    <i class="ri-file-text-line mr-1"></i> Rapor
                                </button>
                                <button onclick="bukaCetak('cetak_semua.php', ${siswa.id_siswa})" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 font-medium rounded-md text-xs px-3 py-1.5 text-center inline-flex items-center shadow-sm transition-colors" title="Cetak Semua Sekaligus">
                                    <i class="ri-printer-line mr-1"></i> Semua
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-6 text-red-500"><i class="ri-error-warning-line mr-2"></i> Error: ${data.error || 'Gagal memuat data'}</td></tr>`;
            }

        } catch (error) {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-6 text-red-500"><i class="ri-error-warning-line mr-2"></i> Terjadi kesalahan jaringan saat mengambil data santri.</td></tr>';
        }
    }

    function bukaCetak(url, idSiswa) {
        const semester = document.getElementById('semester').value;
        let fullUrl = url + '?id=' + idSiswa;
        if (semester) {
            fullUrl += '&smt=' + semester;
        }
        window.open(fullUrl, '_blank', 'width=900,height=600');
    }

    function bukaCetakKelas(url) {
        const selectKelas = document.querySelector('select[name="kelas"]');
        const hiddenKelas = document.getElementById('kelas');
        const kelas = selectKelas ? selectKelas.value : (hiddenKelas ? hiddenKelas.value : '');
        const semester = document.getElementById('semester').value;

        if (!kelas) {
            alert('Silakan pilih kelas terlebih dahulu!');
            return;
        }
        
        // Membuka file cetak asli tapi menggunakan parameter kelas=... 
        let fileAsli = url.replace('_kelas', '');
        let fullUrl = fileAsli + '?kelas=' + kelas;
        if (semester) {
            fullUrl += '&smt=' + semester;
        }
        window.open(fullUrl, '_blank', 'width=900,height=600');
    }

    // Fitur Live Search Santri
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#tbody-siswa tr');
        let hasVisibleRow = false;
        
        rows.forEach(row => {
            // Abaikan row "Tidak ada data siswa" atau "Loading" jika ada
            if(row.querySelector('td').colSpan > 1) return;

            const nama = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
            const nis = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            
            if (nama.includes(searchValue) || nis.includes(searchValue)) {
                row.style.display = '';
                hasVisibleRow = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Menampilkan pesan jika tidak ada data yang cocok
        const existingNoDataRow = document.getElementById('no-search-result-row');
        if (!hasVisibleRow && rows.length > 0 && !(rows.length === 1 && rows[0].querySelector('td').colSpan > 1)) {
            if (!existingNoDataRow) {
                const noDataRow = document.createElement('tr');
                noDataRow.id = 'no-search-result-row';
                noDataRow.innerHTML = '<td colspan="5" class="text-center py-6 text-gray-500"><i class="ri-file-search-line text-xl mr-2 align-middle"></i> Tidak ada santri yang cocok dengan pencarian Anda.</td>';
                document.getElementById('tbody-siswa').appendChild(noDataRow);
            } else {
                existingNoDataRow.style.display = '';
            }
        } else if (existingNoDataRow) {
            existingNoDataRow.style.display = 'none';
        }
    });

    // Auto-load data jika kelas sudah terpilih (khususnya untuk Wali Kelas)
    (function() {
        const selectKelas = document.querySelector('select[name="kelas"]');
        const hiddenKelas = document.getElementById('kelas');
        const selectSemester = document.getElementById('semester');
        
        const kelasVal = selectKelas ? selectKelas.value : (hiddenKelas ? hiddenKelas.value : '');
        
        if (kelasVal) {
            // beri sedikit delay agar elemen DOM benar-benar siap
            setTimeout(tampilkanSiswa, 100);
        }

        // Trigger pencarian otomatis saat dropdown berubah
        if (selectKelas) {
            selectKelas.addEventListener('change', tampilkanSiswa);
        }
        if (selectSemester) {
            selectSemester.addEventListener('change', tampilkanSiswa);
        }
    })();
</script>

<?php include 'include/footer.php'; ?>