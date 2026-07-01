<?php
require 'koneksi.php';
require 'cek_sesi.php';
restrict_roles(RBAC_SUPER_ADMIN);

$tables = array();
$result = mysqli_query($koneksi, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

$sqlScript = "";
foreach ($tables as $table) {
    // Show create table
    $query = "SHOW CREATE TABLE $table";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_row($result);
    $sqlScript .= "\n\n" . $row[1] . ";\n\n";
    
    // Dump data
    $query = "SELECT * FROM $table";
    $result = mysqli_query($koneksi, $query);
    $columnCount = mysqli_num_fields($result);
    
    for ($i = 0; $i < $columnCount; $i ++) {
        while ($row = mysqli_fetch_row($result)) {
            $sqlScript .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $columnCount; $j ++) {
                $row[$j] = $row[$j];
                if (isset($row[$j])) {
                    $sqlScript .= '"' . mysqli_real_escape_string($koneksi, $row[$j]) . '"';
                } else {
                    $sqlScript .= '""';
                }
                if ($j < ($columnCount - 1)) {
                    $sqlScript .= ',';
                }
            }
            $sqlScript .= ");\n";
        }
    }
    $sqlScript .= "\n"; 
}

if (!empty($sqlScript)) {
    $backup_file_name = 'backup_erapor_' . date('Y-m-d_H-i-s') . '.sql';
    header('Content-Type: application/x-sql');
    header('Content-Transfer-Encoding: Binary');
    header('Content-Disposition: attachment; filename=' . $backup_file_name);
    echo $sqlScript;
    exit;
}
?>
