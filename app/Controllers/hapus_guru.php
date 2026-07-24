<?php
require 'koneksi.php';
require 'cek_sesi.php';
require_once 'csrf.php';
restrict_roles(RBAC_MANAGE_MASTER_DATA);

// Cek apakah ada ID yang dikirim (GET untuk initial load, POST untuk konfirmasi)
$id_raw = $_POST['id'] ?? $_GET['id'] ?? '';
if (empty($id_raw)) {
    if (isset($_SERVER['HTTP_HX_REQUEST'])) {
        echo "<script>window.location.href='data_guru.php?status=error&message=Terjadi kesalahan';</script>";
        exit;
    }
    header("Location: data_guru.php?status=error&message=Terjadi kesalahan");
    exit();
}

$id_pengguna = intval($id_raw);

// Cek apakah guru ada
$cek_query = "SELECT p.*, g.nip FROM pengguna p LEFT JOIN guru g ON p.id_pengguna = g.id_pengguna WHERE p.id_pengguna = ?";
$stmt_cek = mysqli_prepare($koneksi, $cek_query);
mysqli_stmt_bind_param($stmt_cek, "i", $id_pengguna);
mysqli_stmt_execute($stmt_cek);
$result_cek = mysqli_stmt_get_result($stmt_cek);

if (mysqli_num_rows($result_cek) == 0) {
    if (isset($_SERVER['HTTP_HX_REQUEST'])) {
        echo "<script>window.location.href='data_guru.php?status=error&message=Data guru tidak ditemukan';</script>";
        exit;
    }
    header("Location: data_guru.php?status=error&message=Data guru tidak ditemukan");
    exit();
}

$guru = mysqli_fetch_assoc($result_cek);

// Jangan izinkan hapus akun sendiri (opsional tapi disarankan)
if ($id_pengguna == $_SESSION['id_pengguna']) {
    if (isset($_SERVER['HTTP_HX_REQUEST'])) {
        echo "<script>window.location.href='data_guru.php?status=error&message=Anda tidak bisa menghapus akun Anda sendiri!';</script>";
        exit;
    }
    header("Location: data_guru.php?status=error&message=Anda tidak bisa menghapus akun Anda sendiri!");
    exit;
}

// Jika konfirmasi sudah dilakukan (POST only — CSRF token harus dari form POST)
if (isset($_POST['konfirmasi']) && $_POST['konfirmasi'] == 'ya') {
    // CSRF Check — token harus dari $_POST, tidak dari GET URL
    $csrf = $_POST['csrf_token'] ?? '';
    if (empty($csrf) || !verify_csrf_token($csrf)) {
        die("Aksi diblokir karena token keamanan tidak valid (Potensi serangan CSRF).");
    }

    // Hapus data guru (soft delete)
    $query = "UPDATE pengguna SET status = 'Dihapus' WHERE id_pengguna = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_pengguna);

    if (mysqli_stmt_execute($stmt)) {
        // Catat ke Log Aktivitas
        require_once 'logger.php';
        catat_log($koneksi, $_SESSION['id_pengguna'], 'Hapus Pengguna', "Menghapus (soft delete) pengguna: {$guru['nama']} (ID: {$id_pengguna})");

        if (isset($_SERVER['HTTP_HX_REQUEST'])) {
            echo ""; // Return empty for HTMX so the row disappears
            exit;
        }
        // Redirect ke halaman data guru dengan pesan sukses
        if (isset($_SERVER['HTTP_HX_REQUEST'])) {
            echo "<script>window.location.href='data_guru.php?status=success&message=Data guru berhasil dihapus';</script>";
            exit;
        }
        header("Location: data_guru.php?status=success&message=Data guru berhasil dihapus");
    } else {
        // Redirect ke halaman data guru dengan pesan error
        if (isset($_SERVER['HTTP_HX_REQUEST'])) {
            echo "<script>window.location.href='data_guru.php?status=error&message=Gagal menghapus data guru';</script>";
            exit;
        }
        header("Location: data_guru.php?status=error&message=Gagal menghapus data guru");
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
                Konfirmasi Hapus Data Pengguna
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Apakah Anda yakin ingin menghapus data pengguna berikut?</h3>
                <div class="bg-gray-50 p-4 rounded-md">
                    <p class="mb-2"><span class="font-medium">Nama Lengkap:</span> <?php echo htmlspecialchars($guru['nama']); ?></p>
                    <p class="mb-2"><span class="font-medium">NIP/Pegawai:</span> <?php echo htmlspecialchars($guru['nip'] ?? '-'); ?></p>
                    <p class="mb-2"><span class="font-medium">Username:</span> <?php echo htmlspecialchars($guru['username']); ?></p>
                    <p class="mb-2"><span class="font-medium">Peran:</span> <?php echo htmlspecialchars($guru['peran']); ?></p>
                </div>
                <p class="text-sm text-red-600 mt-3 font-semibold">Tindakan ini akan membatasi akses login untuk pengguna bersangkutan.</p>
            </div>

            <div class="flex space-x-4">
                <form method="POST" action="hapus_guru.php" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $id_pengguna; ?>">
                    <input type="hidden" name="konfirmasi" value="ya">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white font-medium rounded hover:bg-red-700 transition-colors duration-200">
                        Ya, Hapus Data
                    </button>
                </form>
                <a href="data_guru.php" class="px-4 py-2 bg-gray-500 text-white font-medium rounded hover:bg-gray-600 transition-colors duration-200">
                    Batal
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'include/footer.php'; ?>
