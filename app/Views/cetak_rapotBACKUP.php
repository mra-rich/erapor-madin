<?php
require 'koneksi.php';
require 'cek_sesi.php';
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
    }

    .header-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        border: 1px solid black;
        padding: 10px;
        margin-bottom: 10px;
    }

    .info-item {
        display: flex;
        margin-bottom: 5px;
    }

    .info-label {
        width: 120px;
        font-weight: bold;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }

    th,
    td {
        border: 1px solid black;
        padding: 8px;
        text-align: center;
    }

    th {
        background-color: #f2f2f2;
    }

    .section-header {
        font-weight: bold;
        background-color: #f2f2f2;
    }

    .arabic {
        font-family: "Traditional Arabic", Arial, sans-serif;
        direction: rtl;
    }

    .footer {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 20px;
    }

    .box {
        border: 1px solid black;
        padding: 10px;
    }

    .box-title {
        text-align: center;
        font-weight: bold;
        margin-bottom: 10px;
        padding-bottom: 5px;
    }

    .catatan {
        margin-top: 20px;
        font-style: italic;
    }

    @media (max-width: 600px) {

        .header-info,
        .footer {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .container,
        .container * {
            visibility: visible;
        }

        .container {
            position: absolute;
            left: 0;
            top: 0;
        }
    }
</style>
<div class="p-4 sm:ml-64">
    <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-14">
        <!-- Form Pencarian Siswa -->
        <div class="mb-4">
            <form id="formPencarian" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kelas</label>
                    <select name="kelas" id="kelas"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Pilih Kelas</option>
                        <?php
                        $query_kelas = "SELECT * FROM kelas ORDER BY nama_kelas ASC";
                        $result_kelas = mysqli_query($koneksi, $query_kelas);
                        while ($kelas = mysqli_fetch_assoc($result_kelas)) {
                            echo "<option value='{$kelas['id_kelas']}'>{$kelas['nama_kelas']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Siswa</label>
                    <select name="siswa" id="siswa"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Pilih Siswa</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="cariSiswa()"
                        class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                        Cari Siswa
                    </button>
                </div>
            </form>
        </div>

        <div class="container" id="laporan-container" style="display: none;">
            <h1 class="text-2xl font-bold">LAPORAN HASIL BELAJAR</h1>


            <div class="header-info">
                <div>
                    <div class="info-item">
                        <div class="info-label">Nama Santri</div>
                        <div class="nama-santri">: Ahmad Riansyah</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nomor Induk</div>
                        <div class="nomor-induk">: 2363</div>
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <div class="info-label">Kelas</div>
                        <div class="kelas">: 3 MDTA</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tahun Pelajaran</div>
                        <div class="tahun-pelajaran">: 2020/2021</div>
                    </div>
                </div>
            </div>
            <!--  -->
            <table>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Mata Pelajaran</th>
                    <th colspan="2">Hasil Tes</th>
                    <th colspan="2" class="arabic"> نتائج التمرين الأول </th>
                    <th class="arabic" rowspan="2">الفنون</th>
                    <th class="arabic" rowspan="2">الرقم</th>
                </tr>
                <tr>
                    <th>Angka</th>
                    <th>Huruf</th>
                    <th class="arabic">اللفظ</th>
                    <th class="arabic">الرقم</th>
                </tr>
                <tr class="section-header">
                    <td>A</td>
                    <td>TES TULIS</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>الكتابة</td>
                    <td></td>
                </tr>
                <tbody id="tes-tulis-container">
                    <tr class="tes_tulis">
                        <td class="nomor"></td>
                        <td class="nama-mapel"></td>
                        <td class="nilai-angka"></td>
                        <td class="output-indonesia"></td>
                        <td class="output-arab"></td>
                        <td class="output-angka-arab"></td>
                        <td class="output-mapel-arab"></td>
                        <td class="output-nomor-arab"></td>
                    </tr>
                </tbody>
                <tr class="section-header">
                    <td>B</td>
                    <td>HAFALAN</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="arabic">أهل الحفظ</td>
                    <td></td>
                </tr>
                <tbody id="hafalan-container">
                    <!-- Nilai hafalan akan ditampilkan di sini -->
                </tbody>
                <tr class="section-header">
                    <td>C</td>
                    <td>TES BACA</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="arabic">أختبار القراءة</td>
                    <td></td>
                </tr>
                <tbody id="tes-baca-container">
                    <!-- Nilai tes baca akan ditampilkan di sini -->
                </tbody>
                <tr>
                    <td colspan="2">JUMLAH</td>
                    <td id="jumlah-nilai"></td>
                    <td></td>
                    <td></td>
                    <td class="arabic"></td>
                    <td colspan="2" class="arabic">الجملة</td>
                </tr>
                <tr>
                    <td colspan="2">RANGKING</td>
                    <td>1</td>
                    <td>Satu</td>

                    <td></td>
                    <td class="arabic">١</td>
                    <td colspan="2" class="arabic">المقام/ة</td>
                </tr>
            </table>

            <div class="footer">
                <div>
                    <table>
                        <tr>
                            <td colspan="4" class="font-bold">Kepribadian</td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>Kelakuan</td>
                            <td class="kelakuan"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Kerajinan</td>
                            <td class="kerajinan"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Kerapian</td>
                            <td class="kerapian"></td>
                            <td></td>
                        </tr>
                    </table>
                </div>
                <div>
                    <table>
                        <tr>
                            <td colspan="2" class="font-bold">Absensi</td>
                            <td colspan="2" class="arabic">الغياب</td>
                        </tr>
                        <tr>
                            <th>Sakit</th>
                            <td class="sakit">0</td>
                            <td class="arabic">٠</td>
                            <td><span class="arabic">مريض</span></td>
                        </tr>
                        <tr>
                            <th>Izin</th>
                            <td class="izin">0</td>
                            <td class="arabic">٠</td>
                            <td><span class="arabic">إذن</span></td>
                        </tr>
                        <tr>
                            <th>Tanpa Keterangan</th>
                            <td class="tanpa_keterangan">0</td>
                            <td class="arabic">٠</td>
                            <td><span class="arabic">غائب</span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="catatan">
                <p>Catatan Wali Kelas:</p>
                <p>Tingkatkan belajar jangan terlalu banyak main hp.</p>
            </div>
        </div>
    </div>
</div>
<div style="text-align: center; margin-top: 20px;">
    <button
        style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;"
        onclick="cetakPDF()">Cetak PDF <i class="ri-printer-line"></i></button>
    <button
        style="background-color: #00b894; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;"
        onclick="cetakWord()">Cetak Word <i class="ri-file-word-line"></i></button>
</div>

<script>
    let rowCount = 1;

    function angkaKeHuruf(angka) {
        const satuan = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan"];
        const belasan = ["Sepuluh", "Sebelas", "Dua Belas", "Tiga Belas", "Empat Belas", "Lima Belas", "Enam Belas", "Tujuh Belas", "Delapan Belas", "Sembilan Belas"];
        const puluhan = ["", "", "Dua Puluh", "Tiga Puluh", "Empat Puluh", "Lima Puluh", "Enam Puluh", "Tujuh Puluh", "Delapan Puluh", "Sembilan Puluh"];
        const ratusan = ["", "Seratus", "Dua Ratus", "Tiga Ratus", "Empat Ratus", "Lima Ratus", "Enam Ratus", "Tujuh Ratus", "Delapan Ratus", "Sembilan Ratus"];

        if (angka < 10) return satuan[angka];
        else if (angka < 20) return belasan[angka - 10];
        else if (angka < 100) {
            let puluh = Math.floor(angka / 10);
            let sisa = angka % 10;
            return puluhan[puluh] + (sisa ? " " + satuan[sisa] : "");
        } else if (angka < 1000) {
            let ratus = Math.floor(angka / 100);
            let sisa = angka % 100;
            return ratusan[ratus] + (sisa ? " " + angkaKeHuruf(sisa) : "");
        } else if (angka == 1000) {
            return "Seribu";
        } else {
            return "undefined";
        }
    }

    function angkaKeHurufArab(angka) {
        const satuanArab = ["", "واحد", "اثنان", "ثلاثة", "أربعة", "خمسة", "ستة", "سبعة", "ثمانية", "تسعة"];
        const belasanArab = ["عشرة", "أحد عشر", "اثنا عشر", "ثلاثة عشر", "أربعة عشر", "خمسة عشر", "ستة عشر", "سبعة عشر", "ثمانية عشر", "تسعة عشر"];
        const puluhanArab = ["", "", "عشرون", "ثلاثون", "أربعون", "خمسون", "ستون", "سبعون", "ثمانون", "تسعون"];
        const ratusanArab = ["", "مائة", "مائتان", "ثلاثمائة", "أربعمائة", "خمسمائة", "ستمائة", "سبعمائة", "ثمانمائة", "تسعمائة"];

        if (angka < 10) return satuanArab[angka];
        else if (angka < 20) return belasanArab[angka - 10];
        else if (angka < 100) {
            let puluh = Math.floor(angka / 10);
            let sisa = angka % 10;
            return puluhanArab[puluh] + (sisa ? " و" + satuanArab[sisa] : "");
        } else {
            let ratus = Math.floor(angka / 100);
            let sisa = angka % 100;
            return ratusanArab[ratus] + (sisa ? " و" + angkaKeHurufArab(sisa) : "");
        }
    }

    function angkaKeArab(angka) {
        const angkaArab = ["٠", "١", "٢", "٣", "٤", "٥", "٦", "٧", "٨", "٩"];
        return angka.toString().split('').map(num => angkaArab[num]).join('');
    }

    function konversiNilaiKeHuruf(nilai) {
        const satuan = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan"];
        const belasan = ["Sepuluh", "Sebelas", "Dua Belas", "Tiga Belas", "Empat Belas", "Lima Belas", "Enam Belas", "Tujuh Belas", "Delapan Belas", "Sembilan Belas"];
        const puluhan = ["", "", "Dua Puluh", "Tiga Puluh", "Empat Puluh", "Lima Puluh", "Enam Puluh", "Tujuh Puluh", "Delapan Puluh", "Sembilan Puluh"];
        const ratusan = ["", "Seratus", "Dua Ratus", "Tiga Ratus", "Empat Ratus", "Lima Ratus", "Enam Ratus", "Tujuh Ratus", "Delapan Ratus", "Sembilan Ratus"];

        if (nilai < 10) return satuan[nilai];
        else if (nilai < 20) return belasan[nilai - 10];
        else if (nilai < 100) {
            let puluh = Math.floor(nilai / 10);
            let sisa = nilai % 10;
            return puluhan[puluh] + (sisa ? " " + satuan[sisa] : "");
        } else {
            let ratus = Math.floor(nilai / 100);
            let sisa = nilai % 100;
            return ratusan[ratus] + (sisa ? " " + konversiNilaiKeHuruf(sisa) : "");
        }
    }

    function getMapelArab(namaMapel) {
        return new Promise((resolve, reject) => {
            // Normalisasi nama mata pelajaran
            fetch('get_terjemahan_mapel.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'nama_mapel=' + encodeURIComponent(namaMapel)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error('Error from server:', data.error);
                        resolve(namaMapel); // Jika ada error, kembalikan nama asli
                    } else if (data.terjemahan) {
                        resolve(data.terjemahan);
                    } else {
                        resolve(namaMapel); // Jika tidak ada terjemahan, kembalikan nama asli
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    resolve(namaMapel); // Jika terjadi error, kembalikan nama asli
                });
        
    }

    // Fungsi untuk menampilkan nilai dengan terjemahan Arab
    async function tampilkanNilai(nilai, container) {
        const row = document.createElement('tr');
        row.className = 'tes_tulis';

        // Dapatkan terjemahan Arab untuk nama mata pelajaran
        const terjemahanArab = await getMapelArab(nilai.nama_mapel);

        // Tentukan class berdasarkan kategori
        const arabicClass = container.id === 'hafalan-container' || container.id === 'tes-baca-container' ? 'arabic' : '';

        row.innerHTML = `
            <td class="nomor"></td>
            <td class="nama-mapel">${nilai.nama_mapel}</td>
            <td class="nilai-angka">${nilai.nilai_angka}</td>
            <td class="output-indonesia">${konversiNilaiKeHuruf(nilai.nilai_angka)}</td>
            <td class="${arabicClass}">${angkaKeHurufArab(nilai.nilai_angka)}</td>
            <td class="${arabicClass}">${angkaKeArab(nilai.nilai_angka)}</td>
            <td class="output-mapel-arab arabic">${terjemahanArab}</td>
            <td class="output-nomor-arab ${arabicClass}">١</td>
        `;
        container.appendChild(row);
    }

    function updateKonversi(nilai) {
        let angka = parseInt(nilai) || 0;
        let row = document.querySelector('.tes_tulis');
        const outputIndonesia = row.querySelector(".output-indonesia");
        const outputArab = row.querySelector(".output-arab");
        const outputAngkaArab = row.querySelector(".output-angka-arab");

        if (outputIndonesia) outputIndonesia.textContent = angkaKeHuruf(angka);
        if (outputArab) outputArab.textContent = angkaKeHurufArab(angka);
        if (outputAngkaArab) outputAngkaArab.textContent = angkaKeArab(angka);
    }

    function updateNomor() {
        document.querySelectorAll(".nomor").forEach((td, index) => {
            let nomor = index + 1;
            td.textContent = nomor;
            const outputNomorArab = td.closest("tr").querySelector(".output-nomor-arab");
            if (outputNomorArab) {
                outputNomorArab.textContent = angkaKeArab(nomor);
            }
        });
    }

    function tambahBaris() {
        const container = document.getElementById("tes-tulis-container");
        if (!container) return;

        const row = document.createElement("tr");
        row.className = "tes_tulis";
        row.innerHTML = `
            <td class="nomor"></td>
            <td class="nama-mapel"></td>
            <td class="nilai-angka"></td>
            <td class="output-indonesia"></td>
            <td class="output-arab"></td>
            <td class="output-angka-arab"></td>
            <td class="output-mapel-arab"></td>
            <td class="output-nomor-arab"></td>
        `;
        container.appendChild(row);
        updateNomor();
    }

    // Panggil updateNomor setelah DOM selesai dimuat
    // DOMContentLoaded removed for SPA compatibility

        updateNomor();
    });

    // Fungsi untuk mengambil data siswa berdasarkan kelas
    function getSiswa() {
        const kelas = document.getElementById('kelas').value;
        if (kelas) {
            fetch('get_siswa_rapot.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'kelas=' + kelas
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    document.getElementById('siswa').innerHTML = data.html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data siswa');
                });
        } else {
            document.getElementById('siswa').innerHTML = '<option value="">Pilih Siswa</option>';
        }
    }

    // Event listener untuk perubahan kelas
    document.getElementById('kelas').addEventListener('change', getSiswa);

    // Fungsi untuk mencari siswa
    async function cariSiswa() {
        const siswa = document.getElementById('siswa').value;
        if (siswa) {
            console.log('Mencari data siswa dengan ID:', siswa);

            try {
                // Ambil data siswa
                const response = await fetch('get_siswa_rapot.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'siswa=' + siswa
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();
                console.log('Data siswa:', data);

                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Update informasi siswa di header
                const namaSantri = document.querySelector('.nama-santri');
                const nomorInduk = document.querySelector('.nomor-induk');
                const kelas = document.querySelector('.kelas');
                const tahunPelajaran = document.querySelector('.tahun-pelajaran');

                if (namaSantri) namaSantri.textContent = ': ' + data.nama;
                if (nomorInduk) nomorInduk.textContent = ': ' + data.nis;
                if (kelas) kelas.textContent = ': ' + data.kelas;
                if (tahunPelajaran) tahunPelajaran.textContent = ': ' + data.tahun_ajaran;

                // Ambil data nilai
                const nilaiResponse = await fetch('get_nilai_siswa.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'siswa=' + siswa
                });

                if (!nilaiResponse.ok) {
                    throw new Error('Network response was not ok');
                }

                const nilaiData = await nilaiResponse.json();
                console.log('Data nilai:', nilaiData);

                if (nilaiData.error) {
                    alert(nilaiData.error);
                    return;
                }

                // Reset nilai sebelumnya
                const tesTulisContainer = document.getElementById('tes-tulis-container');
                const hafalanContainer = document.getElementById('hafalan-container');
                const tesBacaContainer = document.getElementById('tes-baca-container');

                tesTulisContainer.innerHTML = '';
                hafalanContainer.innerHTML = '';
                tesBacaContainer.innerHTML = '';

                // Tampilkan nilai sesuai kategori
                if (nilaiData.nilai && nilaiData.nilai.length > 0) {
                    // Kelompokkan nilai berdasarkan kategori
                    const nilaiTertulis = nilaiData.nilai.filter(n => n.kategori === 'TES TERTULIS');
                    const nilaiHafalan = nilaiData.nilai.filter(n => n.kategori === 'HAFALAN');
                    const nilaiBaca = nilaiData.nilai.filter(n => n.kategori === 'TES BACA');

                    // Tampilkan nilai dengan Promise.all untuk menunggu semua terjemahan selesai
                    await Promise.all([
                        ...nilaiTertulis.map(nilai => tampilkanNilai(nilai, tesTulisContainer)),
                        ...nilaiHafalan.map(nilai => tampilkanNilai(nilai, hafalanContainer)),
                        ...nilaiBaca.map(nilai => tampilkanNilai(nilai, tesBacaContainer))
                    ]);

                    // Update nomor urut setelah semua nilai ditampilkan
                    updateNomor();
                }

                // Update data kepribadian
                if (nilaiData.kepribadian) {
                    const kepribadianTable = document.querySelector('.footer table:first-child');
                    if (kepribadianTable) {
                        const rows = kepribadianTable.querySelectorAll('tr');
                        rows.forEach(row => {
                            const cells = row.querySelectorAll('td');
                            cells.forEach(cell => {
                                if (cell.textContent === 'Kelakuan') {
                                    const nextCell = cell.nextElementSibling;
                                    if (nextCell) {
                                        nextCell.textContent = nilaiData.kepribadian.kelakuan || '-';
                                        const deskripsiCell = nextCell.nextElementSibling;
                                        if (deskripsiCell) {
                                            deskripsiCell.textContent = getKepribadianDeskripsi(nilaiData.kepribadian.kelakuan);
                                        }
                                    }
                                }
                                if (cell.textContent === 'Kerajinan') {
                                    const nextCell = cell.nextElementSibling;
                                    if (nextCell) {
                                        nextCell.textContent = nilaiData.kepribadian.kerajinan || '-';
                                        const deskripsiCell = nextCell.nextElementSibling;
                                        if (deskripsiCell) {
                                            deskripsiCell.textContent = getKepribadianDeskripsi(nilaiData.kepribadian.kerajinan);
                                        }
                                    }
                                }
                                if (cell.textContent === 'Kerapian') {
                                    const nextCell = cell.nextElementSibling;
                                    if (nextCell) {
                                        nextCell.textContent = nilaiData.kepribadian.kerapian || '-';
                                        const deskripsiCell = nextCell.nextElementSibling;
                                        if (deskripsiCell) {
                                            deskripsiCell.textContent = getKepribadianDeskripsi(nilaiData.kepribadian.kerapian);
                                        }
                                    }
                                }
                            });
                        
                    }
                }

                // Update data absensi
                if (nilaiData.absensi) {
                    console.log('Data absensi:', nilaiData.absensi);

                    // Update nilai sakit
                    const sakitCell = document.querySelector('.sakit');
                    if (sakitCell) {
                        sakitCell.textContent = nilaiData.absensi.sakit;
                        const sakitArabCell = sakitCell.nextElementSibling;
                        if (sakitArabCell) {
                            sakitArabCell.textContent = angkaKeArab(nilaiData.absensi.sakit);
                        }
                    }

                    // Update nilai izin
                    const izinCell = document.querySelector('.izin');
                    if (izinCell) {
                        izinCell.textContent = nilaiData.absensi.izin;
                        const izinArabCell = izinCell.nextElementSibling;
                        if (izinArabCell) {
                            izinArabCell.textContent = angkaKeArab(nilaiData.absensi.izin);
                        }
                    }

                    // Update nilai tanpa keterangan
                    const tanpaKeteranganCell = document.querySelector('.tanpa_keterangan');
                    if (tanpaKeteranganCell) {
                        tanpaKeteranganCell.textContent = nilaiData.absensi.alpa;
                        const tanpaKeteranganArabCell = tanpaKeteranganCell.nextElementSibling;
                        if (tanpaKeteranganArabCell) {
                            tanpaKeteranganArabCell.textContent = angkaKeArab(nilaiData.absensi.alpa);
                        }
                    }
                } else {
                    console.log('Data absensi tidak ada');
                }

                // Update catatan wali
                if (nilaiData.catatan) {
                    const catatanElement = document.querySelector('.catatan p:last-child');
                    if (catatanElement) {
                        catatanElement.textContent = nilaiData.catatan;
                    }
                }

                // Hitung total nilai
                hitungTotalNilai();

                // Tampilkan container rapor
                const laporanContainer = document.getElementById('laporan-container');
                if (laporanContainer) {
                    laporanContainer.style.display = 'block';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengambil data. Silakan coba lagi.');
            }
        } else {
            alert('Silakan pilih siswa terlebih dahulu');
        }
    }

    // Fungsi untuk mendapatkan deskripsi kepribadian
    function getKepribadianDeskripsi(nilai) {
        switch (nilai) {
            case 'A':
                return 'Sangat Baik';
            case 'B':
                return 'Baik';
            case 'C':
                return 'Cukup';
            case 'D':
                return 'Kurang';
            default:
                return '-';
        }
    }

    // Fungsi untuk menghitung total nilai
    function hitungTotalNilai() {
        let total = 0;

        // Hitung nilai tes tulis
        document.querySelectorAll('#tes-tulis-container .tes_tulis .nilai-angka').forEach(td => {
            if (td.textContent && !isNaN(td.textContent)) {
                total += parseInt(td.textContent);
            }
        });

        // Hitung nilai hafalan
        document.querySelectorAll('#hafalan-container .tes_tulis .nilai-angka').forEach(td => {
            if (td.textContent && !isNaN(td.textContent)) {
                total += parseInt(td.textContent);
            }
        });

        // Hitung nilai tes baca
        document.querySelectorAll('#tes-baca-container .tes_tulis .nilai-angka').forEach(td => {
            if (td.textContent && !isNaN(td.textContent)) {
                total += parseInt(td.textContent);
            }
        });

        // Update total di tabel
        const jumlahNilai = document.getElementById('jumlah-nilai');
        if (jumlahNilai) {
            jumlahNilai.textContent = total;

            // Update terjemahan huruf
            const totalHuruf = jumlahNilai.nextElementSibling;
            if (totalHuruf) {
                totalHuruf.textContent = konversiNilaiKeHuruf(total);
            }

            // Update terjemahan Arab
            const totalArab = totalHuruf.nextElementSibling;
            if (totalArab) {
                totalArab.textContent = angkaKeHurufArab(total);
            }

            // Update angka Arab
            const angkaArab = totalArab.nextElementSibling;
            if (angkaArab) {
                angkaArab.textContent = angkaKeArab(total);
            }
        }
    }
</script>

<?php include 'include/footer.php'; ?>