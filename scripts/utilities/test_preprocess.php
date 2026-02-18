<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    $controller = $app->make(\App\Http\Controllers\DashboardController::class);
    
    $unprocessed = \App\Models\Review::whereNull('case_folding')->count();
    echo "Records to process: {$unprocessed}\n";
    
    if ($unprocessed > 0) {
        echo "Starting batch preprocessing...\n";
        $result = $controller->preprocessData();
        echo "Preprocessing completed!\n";
        echo "Result type: " . gettype($result) . "\n";
        
        $completed = \App\Models\Review::whereNotNull('case_folding')->count();
        echo "Records now processed: {$completed}\n";
    } else {
        echo "No records to process.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>
