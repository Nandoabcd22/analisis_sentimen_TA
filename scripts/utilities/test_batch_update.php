<?php
require __DIR__ . '/vendor/autoload.php';

// Setup Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Review;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// Get 2 unprocessed reviews
$reviews = Review::whereNull('case_folding')->take(2)->get();

echo "=== Testing Database Update ===\n";
echo "Found " . count($reviews) . " unprocessed reviews\n\n";

foreach ($reviews as $review) {
    echo "Review ID: " . $review->id . " (type: " . gettype($review->id) . ")\n";
    echo "Review text: " . substr($review->review, 0, 50) . "...\n";
}

// Simulate what happens with Python results
$pythonResults = [];
foreach ($reviews as $review) {
    $pythonResults[] = [
        'id' => (string)$review->id,  // Python returns string IDs in JSON
        'case_folding' => 'test case folding for id ' . $review->id,
        'cleansing' => 'test cleansing',
        'normalisasi' => 'test normalisasi',
        'tokenizing' => ['test', 'tokens'],
        'stopword' => ['test'],
        'stemming' => ['test']
    ];
}

echo "\n=== Simulated Python Results ===\n";
foreach ($pythonResults as $result) {
    echo "Result ID: " . $result['id'] . " (type: " . gettype($result['id']) . ")\n";
}

// Now try the update
echo "\n=== Attempting Update ===\n";

$processedCount = 0;
foreach ($pythonResults as $result) {
    echo "Processing result ID: " . $result['id'] . "\n";
    
    try {
        // This is what the controller does
        $query = Review::where('id', $result['id']);
        echo "  Query: " . $query->toSql() . "\n";
        echo "  Bindings: " . json_encode($query->getBindings()) . "\n";
        
        $updated = $query->update([
            'case_folding' => $result['case_folding'],
            'cleansing' => $result['cleansing'],
            'normalisasi' => $result['normalisasi'],
            'tokenizing' => json_encode($result['tokenizing']),
            'stopword' => json_encode($result['stopword']),
            'stemming' => json_encode($result['stemming']),
        ]);
        
        echo "  Update result: " . ($updated ? "SUCCESS ($updated rows)" : "FAILED (0 rows)") . "\n";
        
        if ($updated > 0) {
            $processedCount++;
        }
        
        // Verify the update
        $check = Review::where('id', $result['id'])->first();
        echo "  Verification - case_folding set to: " . $check->case_folding . "\n";
        
    } catch (\Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Summary ===\n";
echo "Processed count: " . $processedCount . "\n";

// Final verification
$stillUnprocessed = Review::whereNull('case_folding')->count();
echo "Unprocessed remaining: " . $stillUnprocessed . "\n";
