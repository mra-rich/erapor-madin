<?php
require 'koneksi.php';
require 'cek_sesi.php';
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64">
  <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-14">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
      <div class="text-xl font-bold text-gray-800 mb-2 md:mb-0">
        Data Mata Pelajaran
      </div>
      <div class="text-sm font-medium text-gray-800">
        <?php
        $dayList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $monthList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        echo $dayList[date('w')] . ', ' . date('j') . ' ' . $monthList[date('n') - 1] . ' ' . date('Y');
        ?>
      </div>
    </div>

    <!-- Notifikasi Status -->
    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
      <div class="mb-4 p-4 rounded-lg <?php echo ($_GET['status'] == 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo htmlspecialchars($_GET['message']); ?>
      </div>
    <?php endif; ?>

    <!-- Tombol Tambah Mata Pelajaran -->
    <div class="mb-4">
      <a href="tambah_mata_pelajaran.php" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
        <i class="fas fa-plus mr-2"></i> Tambah Mata Pelajaran
      </a>
    </div>

    <!-- DataTable -->
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
      <table id="santriTable" class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 ">
          <tr>
            <th scope="col" class="px-6 py-3">No</th>
            <th scope="col" class="px-6 py-3">ID Mapel</th>
            <th scope="col" class="px-6 py-3">Nama Mapel</th>
            <th scope="col" class="px-6 py-3">Nama Mapel (Arab)</th>
            <th scope="col" class="px-6 py-3">Kategori</th>
            <th scope="col" class="px-6 py-3">Kelas</th>
            <th scope="col" class="px-6 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $query = "SELECT mp.*, k.nama_kelas 
                    FROM mata_pelajaran mp 
                    JOIN kelas k ON mp.id_kelas = k.id_kelas 
                    ORDER BY mp.id_mapel ASC";
          $result = mysqli_query($koneksi, $query);
          $no = 1;

          while ($row = mysqli_fetch_assoc($result)) :
          ?>
            <tr class="bg-white border-b">
              <td class="px-6 py-4"><?= $no++; ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['id_mapel']); ?></td>
              <td class="px-6 py-4 font-medium"><?= htmlspecialchars($row['nama_mapel']); ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['nama_mapel_arab']); ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['kategori']); ?></td>
              <td class="px-6 py-4"><?= htmlspecialchars($row['nama_kelas']); ?></td>
              <td class="px-6 py-4 text-center">
                <a href="edit_mapel.php?id=<?= $row['id_mapel']; ?>" class="text-blue-600 dark:text-blue-400 hover:underline mr-2">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <a href="hapus_mapel.php?id=<?= $row['id_mapel']; ?>" class="text-red-600 dark:text-red-400 hover:underline" onclick="return confirm('Yakin ingin menghapus data ini?');">
                  <i class="fas fa-trash-alt"></i> Hapus
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- DataTables CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Custom DataTable Initialization -->
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const dataTable = new simpleDatatables.DataTable("#santriTable", {
      paging: true,
      perPage: 10,
      perPageSelect: [5, 10, 15, 20, 25],
      searchable: true,
      sortable: true
    });

    // Custom styling untuk memindahkan search box ke kiri
    document.querySelector('.dataTable-top').style.display = 'flex';
    document.querySelector('.dataTable-search').style.marginLeft = '0px';
  });
</script>

<?php include 'include/footer.php'; ?>