<?php
// Ini adalah simulasi halaman yang TIDAK menggunakan perlindungan htmlspecialchars
$pesan = "";
if (isset($_POST['komentar'])) {
    // 🚨 KODE RENTAN (VULNERABLE): Teks dari pengguna langsung ditangkap tanpa disaring
    $pesan = $_POST['komentar']; 
}
?>
<!DOCTYPE html>
<html>
<head><title>Simulasi Rentan XSS</title></head>
<body style="font-family: Arial; padding: 50px; background: #e3f2fd;">
    <h1 style="color: #1565c0;">Simulasi 2: Formulir Tanpa Keamanan (XSS)</h1>
    <p>Ini adalah contoh website yang menelan mentah-mentah teks dari pengguna (tanpa <i>htmlspecialchars</i>).</p>
    <p>Hacker akan memasukkan teks berupa script: <br><b style="background: yellow; padding: 3px;">&lt;script&gt;alert('🔥 BOOM! Website Anda Terinfeksi Hacker! 🔥')&lt;/script&gt;</b></p>
    
    <form method="POST" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 500px;">
        <label>Tulis Komentar / Alamat:</label><br>
        <textarea name="komentar" rows="4" style="width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ccc;"></textarea><br>
        <button type="submit" style="width: 100%; padding: 12px; background: #1565c0; color: white; border: none; font-weight: bold; cursor: pointer; border-radius: 4px;">KIRIM KOMENTAR</button>
    </form>
    
    <div style="margin-top: 30px; padding: 20px; border: 2px dashed #1565c0; background: white; max-width: 500px;">
        <strong style="color: #666;">Tampilan di Layar Pengguna / Admin:</strong><br><br>
        
        <!-- Script dari pengguna akan dieksekusi di sini oleh browser! -->
        <div style="font-size: 18px;">
            <?php echo $pesan; ?> 
        </div>
    </div>
</body>
</html>
