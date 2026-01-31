<?php
$log = file_get_contents('storage/logs/laravel.log');
$lines = explode("\n", $log);

// Get last 30 lines and reverse to see from latest back
$lastLines = array_slice($lines, -30);

echo "=== Last 30 Log Entries ===\n";
foreach ($lastLines as $line) {
    if (!empty(trim($line))) {
        // Remove the long exception stack traces for readability
        if (strpos($line, '{"exception"') === false && 
            strpos($line, 'Stack trace') === false &&
            strpos($line, '#') === false) {
            echo $line . "\n";
        }
    }
}
