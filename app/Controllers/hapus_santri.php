<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_STUDENTS);

// Cek apakah ada ID yang dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    if (isset($_SERVER['HTTP_HX_REQUEST'])) {
        echo "<script>alert('Terjadi kesalahan');</script>";
        exit;
    }
    header("Location: data_santri.php");
    exit();
}

$id_siswa = intval($_GET['id']);

// Cek apakah siswa ada
$cek_query = "SELECT siswa.*, kelas.nama_kelas 
              FROM siswa 
              LEFT JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
              WHERE siswa.id_siswa = ?";
$stmt_cek = mysqli_prepare($koneksi, $cek_query);
mysqli_stmt_bind_param($stmt_cek, "i", $id_siswa);
mysqli_stmt_execute($stmt_cek);
$result_cek = mysqli_stmt_get_result($stmt_cek);

if (mysqli_num_rows($result_cek) == 0) {
    if (isset($_SERVER['HTTP_HX_REQUEST'])) {
        echo "<script>alert('Data siswa tidak ditemukan');</script>";
        exit;
    }
    header("Location: data_santri.php?status=error&message=Data siswa tidak ditemukan");
    exit();
}

$siswa = mysqli_fetch_assoc($result_cek);

// Jika konfirmasi sudah dilakukan
if (isset($_GET['konfirmasi']) && $_GET['konfirmasi'] == 'ya') {
    // CSRF Check for actual deletion
    if (!isset($_GET['csrf_token']) || !verify_csrf_token($_GET['csrf_token'])) {
        die("Aksi diblokir karena token keamanan tidak valid (Potensi serangan CSRF).");
    }

    // Hapus data siswa
    $query = "UPDATE siswa SET status = 'Dihapus' WHERE id_siswa = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_siswa);

    if (mysqli_stmt_execute($stmt)) {
        // Catat ke Log Aktivitas
        require_once 'logger.php';
        catat_log($koneksi, $_SESSION['id_pengguna'], 'Hapus Siswa', "Menghapus data siswa: {$siswa['nama']} (NISN: {$siswa['nisn']})");

        if (isset($_SERVER['HTTP_HX_REQUEST'])) {
            echo ""; // Return empty for HTMX so the row disappears
            exit;
        }
        // Redirect ke halaman data santri dengan pesan sukses
        if (isset($_SERVER['HTTP_HX_REQUEST'])) {
            echo "<script>alert('Data siswa berhasil dihapus');</script>";
            exit;
        }
        header("Location: data_santri.php?status=success&message=Data siswa berhasil dihapus");
    } else {
        // Redirect ke halaman data santri dengan pesan error
        if (isset($_SERVER['HTTP_HX_REQUEST'])) {
            echo "<script>alert('Gagal menghapus data siswa');</script>";
            exit;
        }
        header("Location: data_santri.php?status=error&message=Gagal menghapus data siswa");
    }

    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);
    exit();
}

// Jika belum konfirmasi, tampilkan halaman konfirmasi
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-14">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div class="text-xl font-bold text-gray-800 mb-2 md:mb-0">
                Konfirmasi Hapus Data Siswa
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Apakah Anda yakin ingin menghapus data siswa berikut?</h3>
                <div class="bg-gray-50 p-4 rounded-md">
                    <p class="mb-2"><span class="font-medium">Nama:</span> <?php echo htmlspecialchars($siswa['nama']); ?></p>
                    <p class="mb-2"><span class="font-medium">NISN:</span> <?php echo htmlspecialchars($siswa['nisn']); ?></p>
                    <p class="mb-2"><span class="font-medium">No. Induk Santri:</span> <?php echo htmlspecialchars($siswa['nomor_santri']); ?></p>
                    <p class="mb-2"><span class="font-medium">Kelas:</span> <?php echo htmlspecialchars($siswa['nama_kelas']); ?></p>
                    <p class="mb-2"><span class="font-medium">Tahun Ajaran:</span> <?php echo htmlspecialchars($siswa['tahun_ajaran']); ?></p>
                </div>
            </div>

            <div class="flex space-x-4">
                <a href="hapus_santri.php?id=<?php echo $id_siswa; ?>&konfirmasi=ya&csrf_token=<?php echo isset($_GET['csrf_token']) ? htmlspecialchars($_GET['csrf_token']) : generate_csrf_token(); ?>" class="px-4 py-2 bg-red-600 text-white font-medium rounded hover:bg-red-700 transition-colors duration-200">
                    Ya, Hapus Data
                </a>
                <a href="data_santri.php" class="px-4 py-2 bg-gray-500 text-white font-medium rounded hover:bg-gray-600 transition-colors duration-200">
                    Batal
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'include/footer.php'; ?>