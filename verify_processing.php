<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Review;

// Check that records have been processed
$processed = Review::whereNotNull('case_folding')
    ->whereNotNull('cleansing')
    ->whereNotNull('normalisasi')
    ->whereNotNull('tokenizing')
    ->count();

echo "=== Verification ===\n";
echo "Total processed records: $processed\n\n";

// Sample a processed record
$sample = Review::whereNotNull('case_folding')->first();

if ($sample) {
    echo "Sample Record ID: {$sample->id}\n";
    echo "Original: " . substr($sample->review, 0, 60) . "...\n";
    echo "Case folding: " . $sample->case_folding . "\n";
    echo "Cleansing: " . $sample->cleansing . "\n";
    echo "Normalisasi: " . $sample->normalisasi . "\n";
    echo "Tokenizing: " . $sample->tokenizing . "\n";
    echo "Stopword: " . $sample->stopword . "\n";
    echo "Stemming: " . $sample->stemming . "\n";
} else {
    echo "No processed records found!\n";
}
