<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\CsrfService;

/**
 * CRUD master tingkat kelas.
 * Menggantikan query mutasi yang sebelumnya berada di view.
 */
class TingkatController extends BaseController
{
    private const ROLES = ['Admin', 'Kepala Madrasah'];

    public function index(): void
    {
        $this->requireRole(self::ROLES);

        if ($this->isPost() && $this->input('tambah_tingkat') !== '') {
            $this->verifyCsrf();
            $nama = $this->input('nama_tingkat');

            if ($nama === '') {
                $this->redirect('data_tingkat', 'error', 'Nama tingkat wajib diisi.');
            }

            $stmt = $this->db->prepare('INSERT INTO tingkat_kelas (nama_tingkat) VALUES (?)');
            $stmt->bind_param('s', $nama);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                $this->log('Tambah Tingkat', "Menambahkan tingkat baru: {$nama}");
                $this->redirect('data_tingkat', 'success', 'Data berhasil ditambahkan.');
            }

            $this->redirect('data_tingkat', 'error', 'Data gagal ditambahkan.');
        }

        if ($this->input('hapus') !== '') {
            $this->verifyCsrf();
            $id = (int) $this->input('hapus', 0);
            if ($id <= 0) {
                $this->redirect('data_tingkat', 'error', 'ID tingkat tidak valid.');
            }

            $stmt = $this->db->prepare('DELETE FROM tingkat_kelas WHERE id_tingkat = ?');
            $stmt->bind_param('i', $id);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                $this->log('Hapus Tingkat', "Menghapus tingkat ID: {$id}");
                $this->redirect('data_tingkat', 'success', 'Data berhasil dihapus.');
            }

            $this->redirect('data_tingkat', 'error', 'Data gagal dihapus.');
        }

        $result = $this->db->query('SELECT id_tingkat, nama_tingkat FROM tingkat_kelas ORDER BY id_tingkat ASC');
        $tingkatList = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

        // Bridge sementara untuk layout legacy.
        $koneksi = $this->db;
        require_once 'csrf.php';
        include 'include/header.php';
        include 'include/navbar.php';
        include 'include/sidebar.php';
        require dirname(__DIR__) . '/Views/data_tingkat.php';
    }
}
