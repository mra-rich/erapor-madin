<?php
// Variabel input yang diharapkan sebelum include:
// $id_kelas_selected = (opsional) id kelas yang sedang aktif
// $filter_prefix = (opsional) prefix untuk ID HTMX agar tidak konflik jika ada >1 di satu halaman
// $filter_name = (opsional) atribut name untuk select rombel (default 'kelas')

$id_tingkat_selected = 0;
$nama_kelas_selected = '';
$rombel_selected = 0;

$pfx = isset($filter_prefix) ? $filter_prefix : 'f1';
$fname = isset($filter_name) ? $filter_name : 'kelas';

if (isset($id_kelas_selected) && $id_kelas_selected > 0) {
    $rombel_selected = $id_kelas_selected;
    $q_info = mysqli_query($koneksi, "SELECT id_tingkat, nama_kelas FROM kelas WHERE id_kelas = $id_kelas_selected");
    if ($info = mysqli_fetch_assoc($q_info)) {
        $id_tingkat_selected = $info['id_tingkat'];
        $nama_kelas_selected = $info['nama_kelas'];
    }
}
?>
<!-- Dropdown Tingkat -->
<div class="flex-1 min-w-[150px]">
    <?php if (!isset($hide_labels)): ?><label class="block mb-2 text-sm font-bold text-gray-700">Tingkat</label><?php endif; ?>
    <select <?= isset($form_id) ? 'form="'.$form_id.'"' : '' ?> id="<?= $pfx ?>_tingkat" name="<?= $pfx ?>_id_tingkat" hx-get="api_get_kelas.php?action=get_kelas&pfx=<?= $pfx ?>" hx-target="#<?= $pfx ?>_kelas" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full min-w-[160px] py-3 pl-3 pr-10 shadow-sm transition-colors cursor-pointer" required>
        <option value="">-- Pilih Tingkat --</option>
        <?php 
        $q_tingkat = mysqli_query($koneksi, "SELECT * FROM tingkat_kelas ORDER BY id_tingkat ASC");
        while($t = mysqli_fetch_assoc($q_tingkat)): 
            $sel = ($t['id_tingkat'] == $id_tingkat_selected) ? 'selected' : '';
        ?>
            <option value="<?= $t['id_tingkat'] ?>" <?= $sel ?>><?= htmlspecialchars($t['nama_tingkat']) ?></option>
        <?php endwhile; ?>
    </select>
</div>

<!-- Dropdown Kelas -->
<div class="flex-1 min-w-[150px]">
    <?php if (!isset($hide_labels)): ?><label class="block mb-2 text-sm font-bold text-gray-700">Kelas</label><?php endif; ?>
    <select <?= isset($form_id) ? 'form="'.$form_id.'"' : '' ?> id="<?= $pfx ?>_kelas" name="<?= $pfx ?>_nama_kelas" hx-get="api_get_kelas.php?action=get_rombel&pfx=<?= $pfx ?>" hx-target="#<?= $pfx ?>_rombel" hx-include="#<?= $pfx ?>_tingkat" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full min-w-[160px] py-3 pl-3 pr-10 shadow-sm transition-colors cursor-pointer" required>
        <option value="">-- Pilih Tingkat Dulu --</option>
        <?php
        if ($id_tingkat_selected > 0) {
            $q_k = mysqli_query($koneksi, "SELECT DISTINCT nama_kelas FROM kelas WHERE id_tingkat = $id_tingkat_selected AND status = 'Aktif' ORDER BY CAST(nama_kelas AS UNSIGNED) ASC, nama_kelas ASC");
            while ($k = mysqli_fetch_assoc($q_k)) {
                $sel = ($k['nama_kelas'] == $nama_kelas_selected) ? 'selected' : '';
                echo "<option value=\"".htmlspecialchars($k['nama_kelas'])."\" $sel>".htmlspecialchars($k['nama_kelas'])."</option>";
            }
        }
        ?>
    </select>
</div>

<!-- Dropdown Rombel -->
<div class="flex-1 min-w-[150px]">
    <?php if (!isset($hide_labels)): ?><label class="block mb-2 text-sm font-bold text-gray-700">Rombel</label><?php endif; ?>
    <select <?= isset($form_id) ? 'form="'.$form_id.'"' : '' ?> id="<?= $pfx ?>_rombel" name="<?= $fname ?>" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full min-w-[160px] py-3 pl-3 pr-10 shadow-sm transition-colors cursor-pointer" required>
        <option value="">-- Pilih Kelas Dulu --</option>
        <?php
        if ($id_tingkat_selected > 0 && $nama_kelas_selected !== '') {
            $q_r = mysqli_query($koneksi, "SELECT id_kelas, nama_rombel FROM kelas WHERE id_tingkat = $id_tingkat_selected AND nama_kelas = '$nama_kelas_selected' AND status = 'Aktif' ORDER BY nama_rombel ASC");
            while ($r = mysqli_fetch_assoc($q_r)) {
                $sel = ($r['id_kelas'] == $rombel_selected) ? 'selected' : '';
                echo "<option value=\"{$r['id_kelas']}\" $sel>".htmlspecialchars($r['nama_rombel'])."</option>";
            }
        }
        ?>
    </select>
</div>

<!-- Tombol Tampilkan Default (Jika bukan halaman khusus yg punya tombol sendiri) -->
<?php if (!isset($no_autosubmit) || !$no_autosubmit): ?>
<div class="flex-none w-full sm:w-auto">
    <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-colors flex items-center justify-center font-bold shadow-sm">
        <i class="ri-search-line mr-2"></i> Tampilkan
    </button>
</div>
<?php endif; ?>
