<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\PenggunaModel;
use App\Models\GuruModel;
use App\Services\CsrfService;

/**
 * GuruController
 * Menangani CRUD data guru/pengguna.
 * Menggantikan: data_guru.php (logika), proses_input_guru.php, proses_edit_guru.php, hapus_guru.php, export_guru.php
 */
class GuruController extends BaseController
{
    private PenggunaModel $penggunaModel;
    private GuruModel $guruModel;

    // RBAC constants (mirror dari cek_sesi.php legacy)
    private const RBAC_MANAGE = ['Admin', 'Kepala Madrasah'];

    public function __construct()
    {
        parent::__construct();
        $this->penggunaModel = new PenggunaModel();
        $this->guruModel = new GuruModel();
    }

    /**
     * GET: Halaman list data guru
     * Saat ini men-delegate ke view legacy yang self-contained.
     * View ini akan di-refaktor bertahap di fase berikutnya.
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole(self::RBAC_MANAGE);

        // Sediakan variabel global untuk backward-compat view legacy
        $koneksi = $this->db;
        $db = new \QueryBuilder($koneksi);

        // Delegate ke view legacy (sudah include header/navbar/sidebar sendiri)
        require dirname(__DIR__) . '/Views/data_guru.php';
    }

    /**
     * POST: Tambah atau Edit guru
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->requireRole(self::RBAC_MANAGE);

        if (!$this->isPost()) {
            $this->redirect('data_guru.php');
        }

        $this->verifyCsrf();

        $nip           = $this->input('nip');
        $namaLengkap   = $this->input('nama_lengkap');
        $jenisKelamin  = $this->input('jenis_kelamin');
        $tempatLahir   = $this->input('tempat_lahir');
        $tanggalLahir  = $this->input('tanggal_lahir') ?: null;
        $noHp          = $this->input('no_hp');
        $alamat        = $this->input('alamat');
        $peran         = $this->input('peran', 'Guru');
        $username      = $this->input('username');
        $password       = $this->input('password');
        $idPengguna    = (int) $this->input('id_pengguna', 0);
        $idGuru        = (int) $this->input('id_guru', 0);

        // Validasi
        if (empty($namaLengkap) || empty($username)) {
            $this->redirect('data_guru.php', 'error', 'Nama dan Username wajib diisi!');
        }

        if ($idPengguna === 0 && empty($password)) {
            $this->redirect('data_guru.php', 'error', 'Password wajib diisi untuk pengguna baru!');
        }

        // Cek duplikat username
        if ($this->penggunaModel->isUsernameExists($username, $idPengguna ?: null)) {
            $this->redirect('data_guru.php', 'error', 'Username sudah digunakan. Silakan pilih username lain.');
        }

        $this->guruModel->beginTransaction();

        try {
            if ($idPengguna === 0) {
                // INSERT BARU
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $newIdPengguna = $this->penggunaModel->insert([
                    'nama'     => $namaLengkap,
                    'username' => $username,
                    'password' => $hashedPassword,
                    'peran'    => $peran,
                    'status'   => 'Aktif',
                ]);

                if (!$newIdPengguna) {
                    throw new \Exception('Gagal menyimpan data login.');
                }

                $insertGuru = $this->guruModel->insert([
                    'id_pengguna'   => $newIdPengguna,
                    'nip'           => $nip,
                    'nama_lengkap'  => $namaLengkap,
                    'jenis_kelamin' => $jenisKelamin,
                    'tempat_lahir'  => $tempatLahir,
                    'tanggal_lahir' => $tanggalLahir,
                    'no_hp'         => $noHp,
                    'alamat'        => $alamat,
                ]);

                if (!$insertGuru) {
                    throw new \Exception('Gagal menyimpan biodata guru.');
                }

                $logAction = 'Tambah Guru';
                $logMsg = "Menambahkan guru baru: $namaLengkap (Username: $username)";
            } else {
                // UPDATE
                $dataUpdate = [
                    'nama'     => $namaLengkap,
                    'username' => $username,
                    'peran'    => $peran,
                ];

                if (!empty($password)) {
                    $dataUpdate['password'] = password_hash($password, PASSWORD_DEFAULT);
                }

                $this->penggunaModel->update($dataUpdate, ['id_pengguna' => $idPengguna]);

                $guruData = [
                    'nip'           => $nip,
                    'nama_lengkap'  => $namaLengkap,
                    'jenis_kelamin' => $jenisKelamin,
                    'tempat_lahir'  => $tempatLahir,
                    'tanggal_lahir' => $tanggalLahir,
                    'no_hp'         => $noHp,
                    'alamat'        => $alamat,
                ];

                if ($idGuru > 0) {
                    $this->guruModel->update($guruData, ['id_guru' => $idGuru]);
                } else {
                    $guruData['id_pengguna'] = $idPengguna;
                    $this->guruModel->insert($guruData);
                }

                $logAction = 'Edit Guru';
                $logMsg = "Mengedit data guru: $namaLengkap (Username: $username)";
            }

            $this->guruModel->commit();
            $this->log($logAction, $logMsg);

            $successMsg = $idPengguna === 0 ? 'Data guru berhasil ditambahkan!' : 'Data guru berhasil diperbarui!';
            $this->redirect('data_guru.php', 'success', $successMsg);

        } catch (\Exception $e) {
            $this->guruModel->rollback();
            $this->redirect('data_guru.php', 'error', $e->getMessage());
        }
    }

    /**
     * GET: Hapus guru (soft delete)
     */
    public function delete(): void
    {
        $this->requireAuth();
        $this->requireRole(self::RBAC_MANAGE);

        $id = (int) ($this->input('id') ?: 0);
        if ($id === 0) {
            $this->redirect('data_guru.php', 'error', 'ID tidak valid.');
        }

        // Cek guru ada
        $guru = $this->penggunaModel->findById($id);
        if (!$guru) {
            $this->redirect('data_guru.php', 'error', 'Data guru tidak ditemukan.');
        }

        // Jangan hapus diri sendiri
        if ($id === (int) $_SESSION['id_pengguna']) {
            $this->redirect('data_guru.php', 'error', 'Anda tidak bisa menghapus akun Anda sendiri!');
        }

        // Jika sudah konfirmasi
        $konfirmasi = $this->input('konfirmasi');
        if ($konfirmasi === 'ya') {
            $this->verifyCsrf();

            if ($this->penggunaModel->softDelete($id)) {
                $this->log('Hapus Pengguna', "Menghapus (soft delete) pengguna: {$guru['nama']} (ID: {$id})");

                // HTMX support
                if (isset($_SERVER['HTTP_HX_REQUEST'])) {
                    echo '';
                    exit;
                }

                $this->redirect('data_guru.php', 'success', 'Data guru berhasil dihapus.');
            }

            $this->redirect('data_guru.php', 'error', 'Gagal menghapus data guru.');
        }

        // Belum konfirmasi — render halaman konfirmasi (legacy view)
        $koneksi = $this->db;
        $id_pengguna = $id;
        require_once 'csrf.php';
        include 'include/header.php';
        include 'include/navbar.php';
        include 'include/sidebar.php';

        // Render inline confirmation (dari hapus_guru.php legacy)
        echo '<div class="p-4 sm:ml-64">';
        echo '  <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg mt-14">';
        echo '    <div class="text-xl font-bold text-gray-800 mb-6">Konfirmasi Hapus Data Pengguna</div>';
        echo '    <div class="bg-white p-6 rounded-lg shadow-md">';
        echo '      <h3 class="text-lg font-medium text-gray-900 mb-2">Apakah Anda yakin ingin menghapus data pengguna berikut?</h3>';
        echo '      <div class="bg-gray-50 p-4 rounded-md mb-4">';
        echo '        <p class="mb-2"><span class="font-medium">Nama:</span> ' . htmlspecialchars($guru['nama']) . '</p>';
        echo '        <p class="mb-2"><span class="font-medium">Username:</span> ' . htmlspecialchars($guru['username']) . '</p>';
        echo '        <p class="mb-2"><span class="font-medium">Peran:</span> ' . htmlspecialchars($guru['peran']) . '</p>';
        echo '      </div>';
        echo '      <p class="text-sm text-red-600 font-semibold mb-4">Tindakan ini akan membatasi akses login untuk pengguna bersangkutan.</p>';
        echo '      <div class="flex space-x-4">';
        echo '        <a href="hapus_guru.php?id=' . $id . '&konfirmasi=ya&csrf_token=' . CsrfService::generate() . '" class="px-4 py-2 bg-red-600 text-white font-medium rounded hover:bg-red-700">Ya, Hapus Data</a>';
        echo '        <a href="data_guru.php" class="px-4 py-2 bg-gray-500 text-white font-medium rounded hover:bg-gray-600">Batal</a>';
        echo '      </div>';
        echo '    </div>';
        echo '  </div>';
        echo '</div>';

        include 'include/footer.php';
    }
}
