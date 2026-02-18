<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Review;

// Count processed vs unprocessed
$unprocessed = Review::whereNull('case_folding')->count();
$processed = Review::whereNotNull('case_folding')->count();
$total = Review::count();

echo "=== Database Summary ===\n";
echo "Total records: $total\n";
echo "Processed: $processed\n";
echo "Unprocessed: $unprocessed\n";
echo "Progress: " . ($total > 0 ? round(($processed / $total) * 100, 1) : 0) . "%\n\n";

// Sample a processed record
$sample = Review::whereNotNull('case_folding')->orderBy('id')->first();

if ($sample) {
    echo "Sample Processed Record:\n";
    echo "  ID: {$sample->id}\n";
    echo "  Original: " . substr($sample->review, 0, 80) . "...\n";
    echo "  Case Folding: " . $sample->case_folding . "\n";
    echo "  Cleansing: " . $sample->cleansing . "\n";
    echo "  Normalisasi: " . $sample->normalisasi . "\n";
    echo "  Tokenizing: " . $sample->tokenizing . "\n";
    echo "  Stopword: " . $sample->stopword . "\n";
    echo "  Stemming: " . $sample->stemming . "\n";
}
