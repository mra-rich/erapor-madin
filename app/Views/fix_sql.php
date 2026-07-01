<?php
function fix_sql($filename) {
    if (!file_exists($filename)) return;
    $content = file_get_contents($filename);
    
    // Fix the squashed lines
    $replacements = [
        "-- " => "\n-- ",
        "CREATE TABLE " => "\nCREATE TABLE ",
        "INSERT INTO " => "\nINSERT INTO ",
        "ALTER TABLE " => "\nALTER TABLE ",
        "SET " => "\nSET ",
        "START TRANSACTION;" => "\nSTART TRANSACTION;",
        "COMMIT;" => "\nCOMMIT;",
        "/*!4" => "\n/*!4",
        " ) ENGINE=" => "\n) ENGINE=",
        "  ADD " => "\n  ADD ",
        "  MODIFY " => "\n  MODIFY "
    ];
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    file_put_contents($filename, trim($content));
    echo "Fixed $filename\n";
}

fix_sql('e_raport.sql');
fix_sql('mata_pelajaran.sql');
?>
