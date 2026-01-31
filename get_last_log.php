<?php
$log = file_get_contents('storage/logs/laravel.log');
$lines = array_reverse(explode("\n", $log));

// Find first non-empty, non-Psy error line
foreach ($lines as $line) {
    $trimmed = trim($line);
    if (!empty($trimmed) && strpos($trimmed, 'Psy') === false && strpos($trimmed, 'ParseErrorException') === false) {
        echo $trimmed . "\n";
        break;
    }
}
