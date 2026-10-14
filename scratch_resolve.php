<?php
$files = glob('app/Models/*.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, '<<<<<<< HEAD') !== false) {
        preg_match('/<<<<<<< HEAD\r?\n(.*?)\r?\n=======\r?\n(.*?)\r?\n>>>>>>> dev-five/s', $content, $matches);
        if ($matches) {
            $head = $matches[1];
            $dev = $matches[2];
            
            // Extract functions
            preg_match_all('/function\s+([a-zA-Z0-9_]+)/', $dev, $devFuncs);
            preg_match_all('/function\s+([a-zA-Z0-9_]+)/', $head, $headFuncs);
            
            $diffFuncs = array_diff($devFuncs[1], $headFuncs[1]);
            
            echo basename($file) . ":\n";
            if (!empty($diffFuncs)) {
                echo "  New functions in dev: " . implode(', ', $diffFuncs) . "\n";
            } else {
                echo "  No new functions in dev.\n";
            }
            
            // Check fillable
            preg_match('/protected\s+\$fillable\s*=\s*\[(.*?)\];/s', $dev, $devFill);
            preg_match('/protected\s+\$fillable\s*=\s*\[(.*?)\];/s', $head, $headFill);
            if (!empty($devFill[1]) && !empty($headFill[1])) {
                // simple check for words
                preg_match_all('/\'([^\']+)\'/', $devFill[1], $devWords);
                preg_match_all('/\'([^\']+)\'/', $headFill[1], $headWords);
                $diffFill = array_diff($devWords[1], $headWords[1]);
                if (!empty($diffFill)) {
                    echo "  New fillable in dev: " . implode(', ', $diffFill) . "\n";
                }
            }
        } else {
            echo basename($file) . ": MATCH FAILED\n";
        }
    }
}
