<?php
require 'koneksi.php';

$sql = "CREATE TABLE IF NOT EXISTS `ekstrakurikuler` (
  `id_ekstrakurikuler` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` int(11) NOT NULL,
  `pramuka` varchar(20) DEFAULT NULL,
  `pmr` varchar(20) DEFAULT NULL,
  `paskibra` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_ekstrakurikuler`),
  KEY `id_transaksi` (`id_transaksi`),
  CONSTRAINT `ekstrakurikuler_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi_raport` (`id_transaksi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($koneksi, $sql)) {
    echo "Tabel ekstrakurikuler berhasil dibuat atau sudah ada.\n";
} else {
    echo "Error membuat tabel ekstrakurikuler: " . mysqli_error($koneksi) . "\n";
}
?>
