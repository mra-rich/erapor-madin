<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\PenggunaModel;
use App\Services\AuthService;
use App\Services\CsrfService;
use App\Services\LoggerService;

/**
 * AuthController
 * Menangani login, logout, dan ganti password.
 * Menggantikan: proses_login.php, include/logout.php, proses_ganti_password.php
 */
class AuthController extends BaseController
{
    /**
     * POST: Proses login
     */
    public function login(): void
    {
        if (!$this->isPost()) {
            $this->redirect('index.php');
        }

        $this->verifyCsrf();

        $username = $this->input('username');
        $password = $this->input('password');

        // Brute force check
        if (AuthService::checkBruteForce($username, $this->db)) {
            $_SESSION['error'] = 'Akun terkunci sementara karena terlalu banyak percobaan gagal. Coba lagi dalam 15 menit.';
            $this->redirect('index.php');
        }

        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Username dan password wajib diisi!';
            $this->redirect('index.php');
        }

        $model = new PenggunaModel();
        $user = $model->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil
            AuthService::login([
                'id_pengguna' => $user['id_pengguna'],
                'nama'        => $user['nama'],
                'username'    => $user['username'],
                'peran'       => $user['peran'],
            ]);

            AuthService::clearLoginAttempts($username, $this->db);

            // Regenerate CSRF token setelah login (security best practice)
            CsrfService::regenerate();

            $this->log('Login Berhasil', "Pengguna {$user['username']} login ke sistem.");
            $this->redirect('dashboard.php');
        }

        // Login gagal
        AuthService::recordFailedLogin($username, $_SERVER['REMOTE_ADDR'], $this->db);
        $_SESSION['error'] = 'Username atau password salah!';
        $this->redirect('index.php');
    }

    /**
     * GET: Logout
     */
    public function logout(): void
    {
        if (AuthService::check()) {
            $this->log('Logout', "Pengguna {$_SESSION['username']} logout dari sistem.");
        }
        AuthService::logout();
        $this->redirect('index.php');
    }

    /**
     * POST: Ganti password
     */
    public function changePassword(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('profil.php');
        }

        $this->verifyCsrf();

        $passwordLama   = $this->input('password_lama');
        $passwordBaru   = $this->input('password_baru');
        $konfirmasi     = $this->input('konfirmasi_password');

        if (empty($passwordLama) || empty($passwordBaru) || empty($konfirmasi)) {
            $this->redirect('profil.php', 'error', 'Semua field wajib diisi!');
        }

        if ($passwordBaru !== $konfirmasi) {
            $this->redirect('profil.php', 'error', 'Password baru dan konfirmasi tidak cocok!');
        }

        $model = new PenggunaModel();
        $user = $model->findById($_SESSION['id_pengguna']);

        if (!$user) {
            $this->redirect('profil.php', 'error', 'Data pengguna tidak ditemukan!');
        }

        // Verifikasi password lama
        if (!password_verify($passwordLama, $user['password'])) {
            $this->redirect('profil.php', 'error', 'Password lama salah!');
        }

        // Update password
        $hashed = password_hash($passwordBaru, PASSWORD_DEFAULT);
        if ($model->changePassword($_SESSION['id_pengguna'], $hashed)) {
            $this->log('Ganti Password', "Pengguna {$_SESSION['username']} mengubah password.");
            $this->redirect('profil.php', 'success', 'Password berhasil diubah!');
        }

        $this->redirect('profil.php', 'error', 'Gagal mengubah password.');
    }
}
