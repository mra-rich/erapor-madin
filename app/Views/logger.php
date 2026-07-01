<?php
/**
 * Fungsi Pembantu untuk mencatat log aktivitas pengguna
 */
function catat_log($koneksi, $id_pengguna, $aksi, $detail = '') {
    // Pastikan id_pengguna ada nilainya (jika null, biarkan null untuk sistem)
    $id_pengguna_val = empty($id_pengguna) ? "NULL" : intval($id_pengguna);
    
    // Ambil IP Address (Opsional, tapi jika nanti ditambahkan kolom ip_address di DB)
    // $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // Siapkan statement untuk mencegah SQL Injection
    $stmt = $koneksi->prepare("INSERT INTO log_aktivitas (id_pengguna, aksi, detail) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iss", $id_pengguna_val, $aksi, $detail);
        $stmt->execute();
        $stmt->close();
    }
}
?>
