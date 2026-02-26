<?php
// Quick export for comparison
require_once __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use App\Models\Review;

$reviews = Review::where('label', '!=', null)->get(['id', 'text', 'label'])->toArray();

echo "Total reviews: " . count($reviews) . PHP_EOL;
echo "First 3 samples:" . PHP_EOL;
for ($i = 0; $i < min(3, count($reviews)); $i++) {
    echo "ID: " . $reviews[$i]['id'] . PHP_EOL;
    echo "Text: " . substr($reviews[$i]['text'], 0, 50) . "..." . PHP_EOL;
    echo "Label: " . $reviews[$i]['label'] . PHP_EOL;
    echo "---" . PHP_EOL;
}

// Count distribution
$labels = array_count_values(array_column($reviews, 'label'));
echo "Label distribution: " . json_encode($labels) . PHP_EOL;

// Export as JSON for Python to read
file_put_contents(__DIR__ . '/reviews_export.json', json_encode(['reviews' => $reviews], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "✓ Exported to reviews_export.json" . PHP_EOL;
