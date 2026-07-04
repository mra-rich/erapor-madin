<?php
// Script ringan untuk menjaga agar koneksi database Aiven tetap aktif (mencegah auto-pause)
// Panggil script ini melalui layanan cron-job eksternal (misal: cron-job.org) setiap 15 atau 30 menit.

require_once 'config/koneksi.php';

try {
    // Jalankan query paling ringan untuk memastikan ada aktivitas yang tercatat di server database
    $result = mysqli_query($koneksi, "SELECT 1");
    
    if ($result) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Database pinged successfully. Connection is active.",
            "timestamp" => date("Y-m-d H:i:s")
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to ping database."
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection error."
    ]);
}
?>
