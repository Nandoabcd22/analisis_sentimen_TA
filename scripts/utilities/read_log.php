<?php
$logFile = 'storage/logs/laravel.log';
$content = file_get_contents($logFile);
$lines = explode("\n", $content);

// Get last 50 lines
$lastLines = array_slice($lines, -50);
foreach ($lastLines as $line) {
    if (!empty(trim($line))) {
        echo $line . "\n";
    }
}
