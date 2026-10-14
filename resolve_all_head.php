<?php
$files = glob('app/Models/*.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, '<<<<<<< HEAD') !== false) {
        $newContent = preg_replace('/<<<<<<< HEAD\r?\n(.*?)\r?\n=======\r?\n(.*?)\r?\n>>>>>>> dev-five/s', '$1', $content);
        file_put_contents($file, $newContent);
        echo "Resolved " . basename($file) . " using HEAD.\n";
    }
}
