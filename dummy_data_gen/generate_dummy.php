<?php
require '../koneksi.php';
require '../vendor/autoload.php';

// Array Nama Islami
$first_names_l = ['Ahmad', 'Muhammad', 'Ali', 'Umar', 'Usman', 'Hasan', 'Husain', 'Zaid', 'Khalid', 'Tariq', 'Ibrahim', 'Ismail', 'Yusuf', 'Sulaiman', 'Daud', 'Yahya', 'Isa', 'Musa', 'Harun', 'Syuaib'];
$first_names_p = ['Fatimah', 'Khadijah', 'Aisyah', 'Zainab', 'Ruqayyah', 'Ummu', 'Salma', 'Safiyyah', 'Hafsah', 'Maimunah', 'Juwairiyah', 'Ramlah', 'Hindun', 'Asma', 'Maryam', 'Hajar', 'Sarah', 'Aminah', 'Halimah', 'Salwa'];
$last_names = ['Abdullah', 'Abdurrahman', 'Al-Fatih', 'Al-Ayyubi', 'Ash-Shiddiq', 'Al-Faruq', 'An-Nur', 'Al-Hafiz', 'As-Syafi\'i', 'Al-Ghazali', 'Al-Bukhari', 'Muslim', 'Tirmidzi', 'Nasa\'i', 'Abu Daud', 'Ibnu Majah', 'Ahmad', 'Malik', 'Hanafi', 'Hambali'];

function getRandomName($gender) {
    global $first_names_l, $first_names_p, $last_names;
    $first = $gender == 'L' ? $first_names_l[array_rand($first_names_l)] : $first_names_p[array_rand($first_names_p)];
    $last = $last_names[array_rand($last_names)];
    return $first . ' ' . $last;
}

// 1. GENERATE DATA SANTRI
$santri_header = ['NO', 'NISN', 'NOMOR_SANTRI', 'NAMA', 'TEMPAT_LAHIR', 'TANGGAL_LAHIR', 'JENIS_KELAMIN', 'STATUS_KELUARGA', 'ANAK_KE', 'ALAMAT', 'ASAL_SEKOLAH', 'DITERIMA_KELAS', 'TANGGAL_DITERIMA', 'ID_KELAS', 'TAHUN_AJARAN', 'NAMA_AYAH', 'NAMA_IBU', 'KERJA_AYAH', 'KERJA_IBU', 'ALAMAT_ORTU', 'NAMA_WALI', 'KERJA_WALI', 'NO_HP'];

$santri_data = [$santri_header];
$kelas_query = mysqli_query($koneksi, "SELECT id_kelas, nama_kelas, id_tingkat FROM kelas ORDER BY id_tingkat, nama_kelas");

$no_santri = 1;
while ($k = mysqli_fetch_assoc($kelas_query)) {
    // We only generate for Ibtida 1-3 (Tingkat 1, id 1-3), Tsanawiyah 1-3 (Tingkat 2, id 1-3), Aliyah 1-3 (Tingkat 3, id 1-3)
    if ($k['id_tingkat'] == 1 && $k['nama_kelas'] > 3) continue; // Skip Ibtida > 3
    
    for ($i = 1; $i <= 20; $i++) {
        $gender = rand(0, 1) ? 'L' : 'P';
        $nama = getRandomName($gender);
        $nisn = '00' . rand(10000000, 99999999);
        $no_induk = '23' . str_pad($no_santri, 4, '0', STR_PAD_LEFT);
        
        $santri_data[] = [
            $no_santri,
            $nisn,
            $no_induk,
            $nama,
            'Jakarta',
            '2010-01-01',
            $gender,
            'Anak Kandung',
            rand(1, 3),
            'Jl. Pesantren No. ' . rand(1, 100),
            'SDIT Al Fatih',
            $k['nama_kelas'],
            '2023-07-15',
            $k['id_kelas'],
            '2023/2024',
            'Bapak ' . $last_names[array_rand($last_names)],
            'Ibu ' . $first_names_p[array_rand($first_names_p)],
            'Wiraswasta',
            'Ibu Rumah Tangga',
            'Jl. Pesantren No. ' . rand(1, 100),
            '', // Wali
            '',
            '0812' . rand(10000000, 99999999)
        ];
        $no_santri++;
    }
}
$xlsx_santri = \Shuchkin\SimpleXLSXGen::fromArray($santri_data);
$xlsx_santri->saveAs('Data_Santri_Dummy.xlsx');

// 2. GENERATE DATA GURU
$guru_header = ['NO', 'NIP', 'NAMA_LENGKAP', 'JENIS_KELAMIN', 'TEMPAT_LAHIR', 'TANGGAL_LAHIR', 'ALAMAT', 'NO_HP', 'PENDIDIKAN_TERAKHIR', 'JABATAN', 'STATUS_GURU'];
$guru_data = [$guru_header];

for ($i = 1; $i <= 50; $i++) {
    $gender = rand(0, 1) ? 'L' : 'P';
    $nama = getRandomName($gender);
    $gelar = $gender == 'L' ? ', S.Pd.I' : ', S.Pd';
    $nip = '198' . rand(0, 9) . rand(10000000000, 99999999999);
    
    $guru_data[] = [
        $i,
        $nip,
        $nama . $gelar,
        $gender,
        'Bandung',
        '198' . rand(0, 9) . '-0' . rand(1, 9) . '-1' . rand(0, 9),
        'Jl. Guru No. ' . $i,
        '0856' . rand(10000000, 99999999),
        'S1',
        'Guru Mata Pelajaran',
        'Tetap'
    ];
}
$xlsx_guru = \Shuchkin\SimpleXLSXGen::fromArray($guru_data);
$xlsx_guru->saveAs('Data_Guru_Dummy.xlsx');

// 3. GENERATE DATA NILAI (For an example class, let's take id_kelas = 1 or the first one)
$mapel_headers = [
    "NILAI_1_AL-QURAN HADITS",
    "NILAI_2_AQIDAH AKHLAK",
    "NILAI_3_FIKIH",
    "NILAI_4_SEJARAH KEBUDAYAAN ISLAM",
    "NILAI_5_BAHASA ARAB",
    "NILAI_6_BAHASA INDONESIA",
    "NILAI_7_MATEMATIKA",
    "NILAI_8_ILMU PENGETAHUAN ALAM",
    "NILAI_9_ILMU PENGETAHUAN SOSIAL"
];

$nilai_header = ['ID_SISWA', 'NOMOR_SANTRI', 'NAMA_SANTRI', 'IZIN', 'SAKIT', 'ALPA', 'KELAKUAN', 'KERAJINAN', 'KERAPIAN', 'CATATAN', 'PRAMUKA', 'PMR', 'PASKIBRA'];
$nilai_header = array_merge($nilai_header, $mapel_headers);
$nilai_data = [$nilai_header];

// Generate fake values for 20 students (assuming we imported them)
for ($i = 1; $i <= 20; $i++) {
    $row = [
        $i, // id_siswa dummy (might not match db, but good for structure)
        '23000' . $i,
        getRandomName('L'),
        rand(0, 2), // Izin
        rand(0, 2), // Sakit
        rand(0, 1), // Alpa
        ['A', 'B'][rand(0, 1)], // Kelakuan
        ['A', 'B'][rand(0, 1)],
        ['A', 'B'][rand(0, 1)],
        'Tingkatkan belajarmu!', // Catatan
        ['A', 'B', ''][rand(0, 2)], // Pramuka
        ['A', 'B', ''][rand(0, 2)], // PMR
        ['A', 'B', ''][rand(0, 2)]  // Paskibra
    ];
    
    // Fill mapel scores
    for ($j = 0; $j < count($mapel_headers); $j++) {
        $row[] = rand(75, 98);
    }
    
    $nilai_data[] = $row;
}
$xlsx_nilai = \Shuchkin\SimpleXLSXGen::fromArray($nilai_data);
$xlsx_nilai->saveAs('Data_Nilai_Dummy.xlsx');

echo "Semua file Excel Dummy berhasil dibuat!";
?>
