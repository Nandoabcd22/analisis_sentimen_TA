<?php
/**
 * Test prediction functionality
 * Run: php test_prediction.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Setup Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Test cases
$testCases = [
    "pantai nya kotor" => "Negatif",
    "pantai ini sangat indah" => "Positif",
    "pantai ini dekat dari kota" => "Netral",
    "pelayanan sangat buruk" => "Negatif",
    "makanan enak dan segar" => "Positif",
];

echo "\n========== PREDICTION TEST ==========\n\n";

foreach ($testCases as $text => $expectedSentiment) {
    // Use the .venv python directly
    $pythonCmd = __DIR__ . '/.venv/Scripts/python.exe';
    if (!file_exists($pythonCmd)) {
        echo "❌ Python not found at $pythonCmd\n";
        exit(1);
    }
    
    $scriptPath = __DIR__ . '/scripts/predict_sentiment.py';
    $tempFile = __DIR__ . '/storage/app/private/test_' . uniqid() . '.txt';
    
    file_put_contents($tempFile, $text);
    
    $command = "\"{$pythonCmd}\" \"{$scriptPath}\" --file \"{$tempFile}\" 2>&1";
    $output = shell_exec($command);
    
    @unlink($tempFile);
    
    if (empty($output)) {
        echo "❌ Empty output for: $text\n";
        continue;
    }
    
    $result = json_decode(trim($output), true);
    
    if (!$result) {
        echo "❌ Invalid JSON for: $text\n";
        echo "   Output: " . substr($output, 0, 100) . "\n";
        continue;
    }
    
    $sentiment = $result['sentiment'] ?? 'Unknown';
    $confidence = $result['confidence'] ?? 0;
    $success = ($sentiment === $expectedSentiment) ? '✅' : '❌';
    
    echo "$success Text: \"$text\"\n";
    echo "   Expected: $expectedSentiment, Got: $sentiment (" . round($confidence*100, 2) . "%)\n";
    
    if (isset($result['debug_info']['stemmed'])) {
        echo "   Stemmed: " . $result['debug_info']['stemmed'] . "\n";
    }
    echo "\n";
}

echo "\n========== TEST COMPLETE ==========\n";
?>
