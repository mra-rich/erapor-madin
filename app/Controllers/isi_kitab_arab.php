<?php
/**
 * Akses via browser: https://domain.com/isi_kitab_arab.php
 * Hanya untuk Admin. Isi otomatis nama_kitab_arab yang kosong.
 */
session_start();
require_once __DIR__ . '/../../config/koneksi.php';

// Cek login & admin
if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran'] !== 'Admin') {
    die('<h2 style="color:red;">Akses ditolak. Harus login sebagai Admin.</h2>');
}

// Cek kolom
$cek = mysqli_query($koneksi, "SHOW COLUMNS FROM pengampu_mapel LIKE 'nama_kitab_arab'");
if (mysqli_num_rows($cek) === 0) {
    die("<h2 style='color:red;'>Kolom nama_kitab_arab belum ada. Jalankan SQL:</h2>
         <pre>ALTER TABLE pengampu_mapel ADD COLUMN nama_kitab_arab VARCHAR(200) DEFAULT NULL AFTER nama_kitab;</pre>");
}

echo '<h2>Mengisi nama_kitab_arab...</h2><pre>';

$query = mysqli_query($koneksi, "SELECT id, nama_kitab FROM pengampu_mapel WHERE nama_kitab IS NOT NULL AND nama_kitab != '' AND (nama_kitab_arab IS NULL OR nama_kitab_arab = '')");
$total = 0;
while ($row = mysqli_fetch_assoc($query)) {
    $arab = latinToArab($row['nama_kitab']);
    if ($arab !== '') {
        mysqli_query($koneksi, "UPDATE pengampu_mapel SET nama_kitab_arab = '" . mysqli_real_escape_string($koneksi, $arab) . "' WHERE id = " . (int)$row['id']);
        $total++;
        echo "✅ {$row['nama_kitab']} → {$arab}\n";
    }
}

echo "</pre><hr><b>Selesai! {$total} data terisi.</b>";
echo '<br><br><a href="data_mata_pelajaran">← Kembali ke Data Mata Pelajaran</a>';

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
        'Lil' => 'لل', 'Al' => 'ال', 'al' => 'ال',
        'Banin' => 'بنين', 'Banat' => 'بنات',
        'Kitab' => 'كتاب',
        'Faidhul' => 'فيض', 'Khobir' => 'خبير',
        'Arbain' => 'الأربعين', 'Nawawi' => 'النووية',
        'Targhib' => 'الترغيب', 'Wat' => 'وال', 'Tarhib' => 'الترهيب',
        'Akhlak' => 'الأخلاق', 'Akhlaq' => 'الأخلاق',
        'Lilbanin' => 'للبنين', 'Lilbanat' => 'للبنات',
        "Ta'lim" => 'تعليم', 'Mutaallim' => 'المتعلم',
        'Fathul' => 'فتح', 'Qarib' => 'القريب', 'Qorib' => 'القريب',
        'Safinatun' => 'سفينة', 'Naja' => 'النجا',
        "Mabadi'" => 'مبادئ', 'Fiqih' => 'الفقه',
        'Aqidatul' => 'العقيدة', 'Awam' => 'العوام',
        'Imrithi' => 'الإمريطي', 'Nahwu' => 'النحو',
        'Shorof' => 'الصرف', 'Jurumiyah' => 'الآجرومية',
        'Bana' => 'بناء', 'Matan' => 'متن',
        'Tijan' => 'تيجان', 'Darori' => 'الدراري',
        'Hidayatul' => 'هداية', 'Mustafid' => 'المستفيد',
        'Taqrib' => 'التقريب',
        'Mushtollah' => 'مصطلح', 'Hadits' => 'الحديث',
        'Ilmu' => 'علم', 'Tafsir' => 'التفسير',
        'Sulam' => 'سلم', 'Taufiq' => 'التوفيق',
        'Jawahirul' => 'جواهر', 'Kalamiyah' => 'الكلامية',
        'Washoya' => 'الوصايا',
        'Nadhom' => 'نظم', 'Maqshud' => 'المقصود',
        'Alfiyah' => 'ألفية', 'Ibnu' => 'ابن', 'Malik' => 'مالك',
        'Fathul' => 'فتح', 'Muin' => 'المعين',
        'Syarah' => 'شرح', 'Waroqot' => 'الورقات',
        'Ibnu' => 'ابن', 'Aqil' => 'عقيل',
        'Tarekh' => 'تاريخ', 'Tasyre' => 'التشريع',
        'Hujjah' => 'حجة', 'Ahlis' => 'أهل', 'Sunnah' => 'السنة',
        'Kifayatul' => 'كفاية',
        'Madarijuddurus' => 'مدارج الدروس',
        'Khulasoh' => 'خلاصة', 'Nurul' => 'نور', 'Yaqin' => 'اليقين',
        'Juz' => 'جزء', 'Fasholatan' => 'فصولاتان',
        'Jami' => 'جامع', 'Shoghir' => 'الصغير',
        'Taqrir' => 'تقرير', 'Maknun' => 'المكنون',
        'Qowaidul' => 'قواعد', 'I\'lal' => 'الإعلال',
        'Fiqhiyah' => 'الفقهية', 'Jus' => 'جزء',
        'Madarijuddurus' => 'مدارج الدروس',
        'Amtsilatusrifiyyah' => 'أمثلة التصريفية',
        'Nahwu' => 'النحو', 'Wadlih' => 'الواضح',
        'Belajar' => 'تعلم',
        'Sanusi' => 'السنوسي',
        'Mutammimah' => 'المتممة',
        'Unwanudz' => 'عنوان', 'Dzorfi' => 'الظرفي',
        'Durusut' => 'دروس', 'Tarikh' => 'التاريخ',
        'Qowaidul' => 'قواعد', 'Fiqhiyah' => 'الفقهية',
        'Mahfudzot' => 'محفوظات',
        'Tuhfatul' => 'تحفة', 'Atfal' => 'الأطفال',
        'I\'lalus' => 'الإعلال', 'Shorfi' => 'الصرفي',
        'Mabadiul' => 'مبادئ',
        'Ahlaqul' => 'الأخلاق',
        ' ' => ' '
    ];

    $val = trim($val);
    if (!$val) return '';

    $words = preg_split('/\s+/', $val);
    $result = [];
    foreach ($words as $w) {
        if (isset($map[$w])) { $result[] = $map[$w]; continue; }
        $arab = '';
        $len = strlen($w);
        for ($i = 0; $i < $len; $i++) {
            $char = $w[$i];
            $next = ($i + 1 < $len) ? $w[$i + 1] : '';
            $digraph = $char . $next;
            if (isset($map[$digraph])) { $arab .= $map[$digraph]; $i++; }
            elseif (isset($map[$char])) { $arab .= $map[$char]; }
            else { $arab .= $char; }
        }
        $result[] = $arab;
    }
    return implode(' ', $result);
}