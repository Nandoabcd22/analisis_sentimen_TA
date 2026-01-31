<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Review;

$labels = Review::distinct('label')->pluck('label')->toArray();

echo "=== Unique Labels in Database ===\n";
foreach ($labels as $label) {
    $count = Review::where('label', $label)->count();
    echo "- $label: $count records\n";
}
