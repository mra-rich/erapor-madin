<?php
// Ini adalah simulasi halaman login yang TIDAK menggunakan pertahanan Prepared Statements
require 'koneksi.php';

$message = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 🚨 KODE RENTAN (VULNERABLE): Variabel langsung dimasukkan ke dalam teks Query
    // Sengaja saya biarkan rentan untuk demonstrasi.
    $query = "SELECT * FROM pengguna WHERE username = '$username' AND password = '$password'";
    
    // Hacker akan memasukkan username: ' OR '1'='1' -- 
    // Sehingga query berubah menjadi:
    // SELECT * FROM pengguna WHERE username = '' OR '1'='1' -- ' AND password = '...'
    
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $message = "<div style='color: white; background: red; padding: 15px; border-radius: 5px; font-size: 20px; font-weight: bold;'>🚨 HACKER BERHASIL MASUK! 🚨<br>Sistem mengira Anda adalah: " . $user['nama_lengkap'] . " (" . $user['peran'] . ")</div>";
    } else {
        $message = "<div style='color: red;'>Gagal login. Username atau password salah.</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Simulasi Rentan SQL Injection</title></head>
<body style="font-family: Arial; padding: 50px; background: #ffebee;">
    <h1 style="color: #c62828;">Simulasi 1: Login Tanpa Keamanan (SQLi)</h1>
    <p>Ini adalah contoh halaman login web jadul atau yang tidak diproteksi dengan <i>Prepared Statements</i>.</p>
    <p>Hacker hanya perlu mengetik sembarang di Username, lalu memasukkan mantra sakti: <b style="background: yellow; padding: 3px;">' OR '1'='1</b> pada kolom <b>Password</b>.</p>
    
    <div style="margin-bottom: 20px;">
        <?php echo $message; ?>
    </div>

    <form method="POST" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 400px;">
        <label>Username:</label><br>
        <input type="text" name="username" style="width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ccc;"><br>
        <label>Password:</label><br>
        <input type="password" name="password" style="width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ccc;"><br>
        <button type="submit" name="login" style="width: 100%; padding: 12px; background: #c62828; color: white; border: none; font-weight: bold; cursor: pointer; border-radius: 4px;">COBA HACK SEKARANG</button>
    </form>
</body>
</html>
