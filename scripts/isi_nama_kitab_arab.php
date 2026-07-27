<?php
/**
 * Script satu kali: Isi otomatis nama_kitab_arab dari nama_kitab (Latin → Arab)
 * Hanya mengisi yang masih kosong.
 * Cara jalan:
 *   php scripts/isi_nama_kitab_arab.php
 * Atau buka di browser setelah login Admin.
 */

require __DIR__ . '/../config/koneksi.php';

// Jika di browser, butuh login Admin
if (php_sapi_name() !== 'cli') {
    require __DIR__ . '/../app/Controllers/cek_sesi.php';
    if ($_SESSION['peran'] !== 'Admin') {
        die('Hanya Admin');
    }
}

// Cek apakah kolom sudah ada
$cek = mysqli_query($koneksi, "SHOW COLUMNS FROM pengampu_mapel LIKE 'nama_kitab_arab'");
if (mysqli_num_rows($cek) === 0) {
    die("Kolom nama_kitab_arab belum ada. Jalankan SQL: ALTER TABLE pengampu_mapel ADD COLUMN nama_kitab_arab VARCHAR(200) DEFAULT NULL AFTER nama_kitab;");
}

// Ambil semua yang nama_kitab-nya terisi tapi nama_kitab_arab-nya kosong
$query = mysqli_query($koneksi, "SELECT id, nama_kitab FROM pengampu_mapel WHERE nama_kitab IS NOT NULL AND nama_kitab != '' AND (nama_kitab_arab IS NULL OR nama_kitab_arab = '')");

$total = 0;
while ($row = mysqli_fetch_assoc($query)) {
    $arab = latinToArab($row['nama_kitab']);
    if ($arab !== '') {
        mysqli_query($koneksi, "UPDATE pengampu_mapel SET nama_kitab_arab = '" . mysqli_real_escape_string($koneksi, $arab) . "' WHERE id = " . (int)$row['id']);
        $total++;
        echo "✅ [{$row['id']}] {$row['nama_kitab']} → {$arab}\n";
    }
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Selesai! {$total} data terisi.\n";

// ============================================================
// Fungsi transliterasi Latin → Arab (sama dengan JS di frontend)
// ============================================================
function latinToArab($val) {
    $map = [
        'a' => 'ا', 'b' => 'ب', 't' => 'ت', 'th' => 'ث', 'j' => 'ج',
        'h' => 'ح', 'kh' => 'خ', 'd' => 'د', 'dh' => 'ذ', 'r' => 'ر',
        'z' => 'ز', 's' => 'س', 'sh' => 'ش', 'sy' => 'ش', 'S' => 'ص',
        'D' => 'ض', 'T' => 'ط', 'Zh' => 'ظ', 'Z' => 'ظ', "'" => 'ع',
        'gh' => 'غ', 'f' => 'ف', 'q' => 'ق', 'k' => 'ك', 'l' => 'ل',
        'm' => 'م', 'n' => 'ن', 'w' => 'و', 'u' => 'و', 'y' => 'ي',
        'i' => 'ي', 'A' => 'آ',
        // Kata-kata kitab yang umum
        'Lil' => 'لل', 'Al' => 'ال', 'al' => 'ال',
        'Banin' => 'بنين', 'Banat' => 'بنات',
        'Kitab' => 'كتاب',
        'Faidhul' => 'فيض', 'Khobir' => 'خبير',
        'Arbain' => 'الأربعين', 'Nawawi' => 'النووية',
        'Targhib' => 'الترغيب', 'Wat' => 'وال', 'Tarhib' => 'الترهيب',
        'Tahdzib' => 'التهذيب', 'Tahzib' => 'التهذيب',
        'Akhlak' => 'الأخلاق', 'Akhlaq' => 'الأخلاق',
        'Lilbanin' => 'للبنين', 'Lilbanat' => 'للبنات',
        "Ta'lim" => 'تعليم', "Ta'līm" => 'تعليم',
        'Mutaallim' => 'المتعلم', "Muta'allim" => 'المتعلم',
        'Fathul' => 'فتح', 'Qarib' => 'القريب', 'Mujib' => 'المجيب',
        'Safinatun' => 'سفينة', 'Naja' => 'النجا',
        "Mabadi'" => 'مبادئ', 'Fiqih' => 'الفقه', 'Fiqh' => 'الفقه',
        'Aqidatul' => 'العقيدة', 'Awam' => 'العوام',
        'Imrithi' => 'الإمريطي', 'Nahwu' => 'النحو',
        'Shorof' => 'الصرف', 'Sharaf' => 'الصرف',
        'Jurumiyah' => 'الآجرومية', 'Ajrumiyah' => 'الآجرومية',
        'Kailani' => 'الكيلاني', 'Bana' => 'بناء',
        'Matan' => 'متن', 'Jazariyah' => 'الجزري',
        'Tijan' => 'تيجان', 'Darori' => 'الدراري',
        'Hidayatul' => 'هداية', 'Mustafid' => 'المستفيد',
        'Taqrib' => 'التقريب',
        'Mushtollah' => 'مصطلح', 'Hadits' => 'الحديث', 'Hadis' => 'الحديث',
        'Ilmu' => 'علم', 'Tafsir' => 'التفسير',
        'Arbain' => 'الأربعين',
        'Sulam' => 'سلم', 'Taufiq' => 'التوفيق',
        'Tausyah' => 'التوشيح',
        ' ' => ' '
    ];

    $val = trim($val);
    if (!$val) return '';

    $words = preg_split('/\s+/', $val);
    $result = [];
    foreach ($words as $w) {
        // Coba kata utuh dulu
        if (isset($map[$w])) {
            $result[] = $map[$w];
            continue;
        }
        // Fallback: transliterasi huruf per huruf
        $arab = '';
        $len = strlen($w);
        for ($i = 0; $i < $len; $i++) {
            $char = $w[$i];
            $next = ($i + 1 < $len) ? $w[$i + 1] : '';
            $digraph = $char . $next;
            // Cek 2 huruf dulu
            if (isset($map[$digraph])) {
                $arab .= $map[$digraph];
                $i++;
            } elseif (isset($map[$char])) {
                $arab .= $map[$char];
            } else {
                $arab .= $char;
            }
        }
        $result[] = $arab;
    }
    return implode(' ', $result);
}