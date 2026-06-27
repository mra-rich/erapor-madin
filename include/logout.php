<?php
session_start();
session_unset(); // Hapus semua variabel sesi
session_destroy(); // Hancurkan sesi

// Redirect ke halaman login
header("Location: ../index.php");
exit();
?>
