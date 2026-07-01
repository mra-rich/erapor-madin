<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_SUPER_ADMIN); // Hanya Admin yang boleh melihat halaman ini
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64">
  <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-14">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
      <div>
        <h2 class="text-xl font-bold text-gray-800 mb-2 md:mb-0">
          <i class="fas fa-history mr-2 text-indigo-600"></i> Log Aktivitas (Audit Trail)
        </h2>
        <p class="text-sm text-gray-500 mt-1">Pantau semua pergerakan dan perubahan data di dalam sistem E-Rapor secara real-time.</p>
      </div>
      <div class="text-sm font-medium text-gray-800">
        <?php
        $dayList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $monthList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        echo $dayList[date('w')] . ', ' . date('j') . ' ' . $monthList[date('n') - 1] . ' ' . date('Y');
        ?>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
      <table id="logTable" class="w-full text-sm text-left text-slate-600 border-collapse border border-slate-300">
        <thead class="text-xs text-slate-700 uppercase bg-slate-50">
          <tr>
            <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300 w-16">No</th>
            <th scope="col" class="py-4 px-6 font-bold border border-slate-300 w-48">Waktu (WIB)</th>
            <th scope="col" class="py-4 px-6 font-bold border border-slate-300 w-48">Aktor (Pengguna)</th>
            <th scope="col" class="py-4 px-6 font-bold border border-slate-300">Deskripsi Aktivitas</th>
            <th scope="col" class="py-4 px-6 font-bold text-center border border-slate-300 w-32">IP Address</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // Mengambil log aktivitas terbaru (maksimal 1000 baris terakhir agar tidak berat)
          $query = "SELECT l.*, p.nama, p.peran 
                    FROM log_aktivitas l
                    LEFT JOIN pengguna p ON l.id_pengguna = p.id_pengguna
                    ORDER BY l.timestamp DESC LIMIT 1000";
          $result = mysqli_query($koneksi, $query);
          $no = 1;

          if ($result) :
            while ($row = mysqli_fetch_assoc($result)) :
                // Memberikan warna khusus pada aksi tertentu (contoh: merah untuk Hapus)
                $aksi = htmlspecialchars($row['aksi']);
                $badge_class = "text-gray-900";
                
                if (stripos($aksi, 'hapus') !== false || stripos($aksi, 'delete') !== false) {
                    $badge_class = "text-red-600 font-bold";
                } elseif (stripos($aksi, 'tambah') !== false || stripos($aksi, 'import') !== false) {
                    $badge_class = "text-green-600 font-medium";
                } elseif (stripos($aksi, 'edit') !== false || stripos($aksi, 'ubah') !== false) {
                    $badge_class = "text-blue-600 font-medium";
                }
                
                // Nama pengguna yang melakukan aksi
                $nama_aktor = $row['nama'] ? htmlspecialchars($row['nama']) . ' <br><span class="text-xs text-gray-400">(' . htmlspecialchars($row['peran']) . ')</span>' : 'Sistem / Tidak Dikenal';
            ?>
              <tr class="hover:bg-slate-50 transition-colors group">
                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap font-medium text-slate-900 text-center"><?= $no++; ?></td>
                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap font-medium text-slate-900">
                  <?= date('d/m/Y', strtotime($row['timestamp'])); ?><br>
                  <span class="text-xs text-slate-500"><?= date('H:i:s', strtotime($row['timestamp'])); ?></span>
                </td>
                <td class="py-2 px-6 border border-slate-300 whitespace-nowrap"><?= $nama_aktor; ?></td>
                <td class="py-2 px-6 border border-slate-300 <?= $badge_class; ?>">
                  <?= $aksi; ?>
                </td>
                <td class="py-2 px-6 border border-slate-300 text-center font-mono text-xs bg-slate-50 rounded">
                  -
                </td>
              </tr>
            <?php 
            endwhile; 
          else:
            echo "<tr><td colspan='5' class='py-2 px-6 text-center text-red-500 border border-slate-300'>Gagal mengambil data dari database.</td></tr>";
          endif;
          ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php include 'include/footer.php'; ?>
