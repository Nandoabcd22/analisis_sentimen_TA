<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Review;

$processed = Review::whereNotNull('case_folding')->count();
$unprocessed = Review::whereNull('case_folding')->count();
$total = Review::count();

echo "=== Database Status ===\n";
echo "Total records: $total\n";
echo "Processed: $processed\n";
echo "Unprocessed: $unprocessed\n";

// Sample a record to verify data structure
$sample = Review::whereNotNull('case_folding')->first();
if ($sample) {
    echo "\n=== Sample Record (ID: " . $sample->id . ") ===\n";
    echo "Original text: " . substr($sample->review, 0, 80) . "...\n";
    echo "Case folding: " . substr($sample->case_folding, 0, 80) . "...\n";
    echo "Cleansing: " . substr($sample->cleansing, 0, 80) . "...\n";
    echo "Normalisasi: " . substr($sample->normalisasi, 0, 80) . "...\n";
    echo "Tokenizing: " . substr($sample->tokenizing, 0, 80) . "...\n";
    echo "Stopword: " . substr($sample->stopword, 0, 80) . "...\n";
    echo "Stemming: " . substr($sample->stemming, 0, 80) . "...\n";
}
