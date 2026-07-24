<?php

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'e_raport';
$port = getenv('DB_PORT') ?: 3306;
$ssl = getenv('DB_SSL') === 'true' ? MYSQLI_CLIENT_SSL : 0;

// Matikan error reporting bawaan agar error database tidak bocor ke layar pengguna
mysqli_report(MYSQLI_REPORT_OFF);

$koneksi = mysqli_init();
if ($ssl) {
    // Agar bisa koneksi ke Aiven (membutuhkan SSL)
    $koneksi->ssl_set(NULL, NULL, NULL, NULL, NULL);
}
$koneksi->real_connect($host, $user, $password, $database, $port, NULL, $ssl);

if ($koneksi->connect_error) {
    error_log("DB connect failed: " . $koneksi->connect_error);
    http_response_code(503);
    die("Layanan tidak tersedia. Silakan coba lagi nanti.");
}

if (!class_exists('SysSession')) {
    class SysSession implements SessionHandlerInterface {
        private $link;
        public function __construct($link) { $this->link = $link; }
        #[\ReturnTypeWillChange]
        public function open($path, $name) { return true; }
        #[\ReturnTypeWillChange]
        public function close() { return true; }
        #[\ReturnTypeWillChange]
        public function read($id) {
            $stmt = $this->link->prepare("SELECT data FROM sessions WHERE id = ? AND expires > ?");
            if (!$stmt) return "";
            $now = time();
            $stmt->bind_param("si", $id, $now);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                return $row['data'];
            }
            return "";
        }
        #[\ReturnTypeWillChange]
        public function write($id, $data) {
            $expires = time() + (int)ini_get('session.gc_maxlifetime');
            $stmt = $this->link->prepare("REPLACE INTO sessions (id, data, expires) VALUES (?, ?, ?)");
            if (!$stmt) return false;
            $stmt->bind_param("ssi", $id, $data, $expires);
            return $stmt->execute();
        }
        #[\ReturnTypeWillChange]
        public function destroy($id) {
            $stmt = $this->link->prepare("DELETE FROM sessions WHERE id = ?");
            if (!$stmt) return false;
            $stmt->bind_param("s", $id);
            return $stmt->execute();
        }
        #[\ReturnTypeWillChange]
        public function gc($max_lifetime) {
            $stmt = $this->link->prepare("DELETE FROM sessions WHERE expires < ?");
            if (!$stmt) return false;
            $now = time();
            $stmt->bind_param("i", $now);
            $stmt->execute();
            return true;
        }
    }
}
if (session_status() === PHP_SESSION_NONE) {
    $handler = new SysSession($koneksi);
    session_set_save_handler($handler, true);
    session_start();
}

// Inisialisasi Lightweight ORM / Query Builder (legacy)
require_once __DIR__ . '/../app/Models/QueryBuilder.php';
$db = new QueryBuilder($koneksi);
?>
