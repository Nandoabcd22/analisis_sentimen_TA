<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Review;

// Get 3 samples
$reviews = Review::take(3)->get();
$batchData = [];

foreach ($reviews as $review) {
    $batchData[] = [
        'id' => $review->id,
        'text' => $review->review
    ];
}

// Write to temp file
$tempFile = tempnam(sys_get_temp_dir(), 'test_');
file_put_contents($tempFile, json_encode($batchData));

echo "Test batch file created: $tempFile\n";
echo "Content:\n";
echo json_encode($batchData, JSON_PRETTY_PRINT) . "\n\n";

// Run Python
$cmd = "py \"scripts/preprocessing.py\" --batch \"$tempFile\" 2>&1";
echo "Command: $cmd\n\n";
echo "Output:\n";
$output = shell_exec($cmd);
echo $output;

// Check output
echo "\n\nOutput length: " . strlen($output);
$jsonStart = strpos($output, '[');
echo "\nJSON starts at position: " . ($jsonStart !== false ? $jsonStart : "NOT FOUND");

if ($jsonStart !== false) {
    $json = substr($output, $jsonStart);
    $result = json_decode($json, true);
    if ($result) {
        echo "\n✓ JSON decoded successfully!\n";
        echo "Records in result: " . count($result) . "\n";
        echo json_encode($result[0], JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "\n✗ Failed to decode JSON\n";
        echo "First 500 chars: " . substr($json, 0, 500) . "\n";
    }
}

@unlink($tempFile);
