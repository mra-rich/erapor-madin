<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Tambahan untuk brute force protection
function check_brute_force($username, $koneksi) {
    // Clean old logs (older than 15 mins)
    mysqli_query($koneksi, "DELETE FROM login_attempts WHERE timestamp < (NOW() - INTERVAL 15 MINUTE)");
    
    $stmt = $koneksi->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // If more than 5 attempts, block
    if ($row['attempts'] >= 5) {
        return true; // Blocked
    }
    return false; // Safe
}

function record_failed_login($username, $ip, $koneksi) {
    $stmt = $koneksi->prepare("INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $ip);
    $stmt->execute();
}

function clear_login_attempts($username, $koneksi) {
    $stmt = $koneksi->prepare("DELETE FROM login_attempts WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
}
?>
