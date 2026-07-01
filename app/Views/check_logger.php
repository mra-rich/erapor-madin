<?php
foreach(glob('*.php') as $file) {
    $content = file_get_contents($file);
    if(strpos($content, 'catat_log') !== false && strpos($content, 'logger.php') === false) {
        if ($file !== 'logger.php') echo $file . "\n";
    }
}
