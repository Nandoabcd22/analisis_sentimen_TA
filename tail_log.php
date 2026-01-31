<?php
$log = file_get_contents('storage/logs/laravel.log');
$lines = explode("\n", $log);
$recent = array_slice($lines, -50);

echo "=== Recent Log Entries (Last 50 lines) ===\n";
foreach ($recent as $line) {
    if (!empty(trim($line))) {
        echo $line . "\n";
    }
}
