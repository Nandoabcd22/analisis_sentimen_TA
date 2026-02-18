<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class ClassificationController extends Controller
{
    /**
     * Get model metrics from saved JSON file (UPDATED - load actual metrics)
     */
    public function getMetrics()
    {
        try {
            $metricsPath = storage_path('app/private/model_metrics.json');
            
            // If no saved metrics, load from database fallback
            if (!file_exists($metricsPath)) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'accuracy' => 0.0,
                        'precision' => 0.0,
                        'recall' => 0.0,
                        'f1_score' => 0.0,
                        'message' => 'No trained model found. Please train a model first.'
                    ]
                ]);
            }

            $metricsData = json_decode(file_get_contents($metricsPath), true);
            
            if (!$metricsData || !isset($metricsData['evaluation'])) {
                throw new Exception('Invalid metrics file format');
            }

            $eval = $metricsData['evaluation'];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'kernel' => $metricsData['kernel'] ?? 'rbf',
                    'accuracy' => floatval($eval['accuracy'] ?? 0),
                    'precision' => floatval($eval['precision_weighted'] ?? 0),
                    'recall' => floatval($eval['recall_weighted'] ?? 0),
                    'f1_score' => floatval($eval['f1_weighted'] ?? 0),
                    'per_class_metrics' => $eval['per_class_metrics'] ?? [],
                    'classes' => $eval['classes'] ?? [],
                    'timestamp' => $metricsData['timestamp'] ?? null,
                    'cross_validation' => $metricsData['cross_validation'] ?? null
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Error getting metrics', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving metrics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug endpoint - Check Python setup and dependencies
     */
    public function debugPythonSetup()
    {
        try {
            $pythonCmd = $this->findPythonCommand();
            
            $debug = [
                'python_found' => $pythonCmd !== null,
                'python_cmd' => $pythonCmd,
                'python_version' => null,
                'pip_packages' => null,
                'script_path' => base_path('scripts/train_model.py'),
                'script_exists' => file_exists(base_path('scripts/train_model.py')),
                'kamus_path' => base_path('resources/data/kamus_normalisasi.txt'),
                'kamus_exists' => file_exists(base_path('resources/data/kamus_normalisasi.txt')),
                'model_dir' => storage_path('app/private'),
                'model_dir_exists' => is_dir(storage_path('app/private')),
                'errors' => []
            ];

            // Get Python version
            if ($pythonCmd) {
                $versionOutput = shell_exec("\"{$pythonCmd}\" --version 2>&1");
                $debug['python_version'] = trim($versionOutput);

                // Check key packages
                $packages = ['pandas', 'sklearn', 'nltk', 'Sastrawi', 'imblearn'];
                $packageCheck = [];
                
                foreach ($packages as $package) {
                    $checkCmd = "\"{$pythonCmd}\" -c \"import {$package}; print('{$package}: OK')\" 2>&1";
                    $result = shell_exec($checkCmd);
                    $packageCheck[$package] = (strpos($result, 'OK') !== false) ? 'Installed' : 'Missing';
                }
                
                $debug['pip_packages'] = $packageCheck;
            } else {
                $debug['errors'][] = 'Python executable tidak ditemukan';
            }

            // Check if model file exists
            $modelPath = storage_path('app/private/svm_model.pkl');
            $debug['model_exists'] = file_exists($modelPath);

            Log::info('Python setup debug info', $debug);

            return response()->json([
                'success' => true,
                'data' => $debug
            ]);

        } catch (Exception $e) {
            Log::error('Error in debug endpoint', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test run training with simple output
     */
    public function testTrainingSetup()
    {
        try {
            $pythonCmd = $this->findPythonCommand();
            if (!$pythonCmd) {
                return response()->json([
                    'success' => false,
                    'message' => 'Python tidak ditemukan'
                ], 400);
            }

            $scriptPath = base_path('scripts/train_model.py');
            $pythonCmdEscaped = '"' . str_replace('/', '\\', $pythonCmd) . '"';
            $scriptPathEscaped = '"' . str_replace('/', '\\', $scriptPath) . '"';
            
            // Run with minimal data to test setup
            $cmd = "{$pythonCmdEscaped} {$scriptPathEscaped} --test_size 10 2>&1";

            Log::info('Running test training', ['cmd' => $cmd]);

            $output = shell_exec($cmd);

            if (!$output) {
                return response()->json([
                    'success' => false,
                    'message' => 'Python script tidak menghasilkan output',
                    'debug' => [
                        'python_cmd' => $pythonCmd,
                        'script_path' => $scriptPath
                    ]
                ]);
            }

            // Try to find JSON in output
            $jsonStart = strpos($output, '{');
            if ($jsonStart !== false) {
                $json = substr($output, $jsonStart);
                $result = json_decode($json, true);
                
                if (json_last_error() === JSON_ERROR_NONE && isset($result['success'])) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Training test berhasil!',
                        'result' => $result
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Training test gagal',
                'output' => substr($output, 0, 1000),
                'output_length' => strlen($output)
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Train SVM model with kernel parameter
     */
    public function trainModel(Request $request)
    {
        try {
            $validated = $request->validate([
                'kernel' => 'required|string|in:linear,rbf,polynomial,sigmoid',
                'test_size' => 'required|numeric|min:10|max:50'
            ]);

            Log::info('Model training started', [
                'kernel' => $validated['kernel'],
                'test_size' => $validated['test_size']
            ]);

            $pythonCmd = $this->findPythonCommand();
            if (!$pythonCmd) {
                throw new Exception('Python tidak ditemukan');
            }

            $scriptPath = base_path('scripts/train_model_simplified.py');
            if (!file_exists($scriptPath)) {
                throw new Exception('Training script tidak ditemukan');
            }

            // Build command with proper escaping
            $pythonCmdEscaped = '"' . str_replace('/', '\\', $pythonCmd) . '"';
            $scriptPathEscaped = '"' . str_replace('/', '\\', $scriptPath) . '"';
            
            $cmd = "{$pythonCmdEscaped} {$scriptPathEscaped} --kernel {$validated['kernel']} --test_size {$validated['test_size']} 2>&1";

            $output = shell_exec($cmd);
            $output = trim($output);

            if (!$output) {
                throw new Exception('Python script tidak menghasilkan output');
            }

            // Find JSON in output (could have debug text before it)
            $jsonStart = strpos($output, '{');
            if ($jsonStart === false) {
                Log::error('No JSON found in output', ['output' => substr($output, 0, 500)]);
                throw new Exception('Output Python bukan JSON format');
            }

            $json = substr($output, $jsonStart);
            $result = json_decode($json, true);

            if (!is_array($result) || json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode failed', ['error' => json_last_error_msg(), 'json' => substr($json, 0, 200)]);
                throw new Exception('Gagal parse JSON dari Python');
            }

            if (!isset($result['success']) || !$result['success']) {
                throw new Exception($result['error'] ?? 'Training gagal');
            }

            $eval = $result['evaluation_result'] ?? [];
            $data = $result['data'] ?? [];

            Log::info('Model training completed successfully', [
                'kernel' => $validated['kernel'],
                'accuracy' => $eval['accuracy'] ?? 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Model berhasil dilatih',
                'data' => [
                    'kernel' => $validated['kernel'],
                    'test_size' => (int)$validated['test_size'],
                    'accuracy' => floatval($eval['accuracy'] ?? 0),
                    'precision' => floatval($eval['precision_weighted'] ?? 0),
                    'recall' => floatval($eval['recall_weighted'] ?? 0),
                    'f1_score' => floatval($eval['f1_weighted'] ?? 0),
                    'per_class_metrics' => $eval['per_class_metrics'] ?? [],
                    'confusion_matrix' => $eval['confusion_matrix'] ?? [],
                    'classes' => $eval['classes'] ?? [],
                    'total_samples' => $data['total_samples'] ?? 0,
                    'train_samples' => $data['train_samples'] ?? 0,
                    'test_samples' => $data['test_samples'] ?? 0,
                    'features' => $data['features'] ?? 0
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Training error', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate accuracy
     */
    private function calculateAccuracy($y_true, $y_pred)
    {
        $correct = 0;
        foreach ($y_true as $i => $true_label) {
            if ($true_label === $y_pred[$i]) {
                $correct++;
            }
        }
        return $correct / count($y_true);
    }

    /**
     * Calculate weighted precision
     */
    private function calculatePrecision($y_true, $y_pred)
    {
        $labels = array_unique(array_merge($y_true, $y_pred));
        $precisions = [];

        foreach ($labels as $label) {
            $tp = 0;
            $fp = 0;
            foreach ($y_pred as $i => $pred) {
                if ($pred === $label) {
                    if ($y_true[$i] === $label) {
                        $tp++;
                    } else {
                        $fp++;
                    }
                }
            }
            $support = count(array_filter($y_true, fn($y) => $y === $label));
            $precisions[$label] = ($tp + $fp) > 0 ? $tp / ($tp + $fp) : 0;
        }

        // Weighted average
        $weighted_precision = 0;
        foreach ($labels as $label) {
            $support = count(array_filter($y_true, fn($y) => $y === $label));
            $weighted_precision += $precisions[$label] * ($support / count($y_true));
        }

        return $weighted_precision;
    }

    /**
     * Calculate weighted recall
     */
    private function calculateRecall($y_true, $y_pred)
    {
        $labels = array_unique(array_merge($y_true, $y_pred));
        $recalls = [];

        foreach ($labels as $label) {
            $tp = 0;
            $fn = 0;
            foreach ($y_true as $i => $true) {
                if ($true === $label) {
                    if ($y_pred[$i] === $label) {
                        $tp++;
                    } else {
                        $fn++;
                    }
                }
            }
            $recalls[$label] = ($tp + $fn) > 0 ? $tp / ($tp + $fn) : 0;
        }

        // Weighted average
        $weighted_recall = 0;
        foreach ($labels as $label) {
            $support = count(array_filter($y_true, fn($y) => $y === $label));
            $weighted_recall += $recalls[$label] * ($support / count($y_true));
        }

        return $weighted_recall;
    }

    /**
     * Generate 3x3 confusion matrix for 3 classes
     */
    private function confusionMatrix($y_true, $y_pred)
    {
        $classes = ['Negatif', 'Netral', 'Positif'];
        $cm = array_fill(0, 3, array_fill(0, 3, 0));

        foreach ($y_true as $i => $true_label) {
            $true_idx = array_search($true_label, $classes);
            $pred_idx = array_search($y_pred[$i], $classes);
            if ($true_idx !== false && $pred_idx !== false) {
                $cm[$true_idx][$pred_idx]++;
            }
        }

        return $cm;
    }

    /**
     * Get Python executable path
     */
    private function getPythonExecutable()
    {
        // Try common Python paths
        $possiblePaths = [
            'python',
            'python3',
            'python.exe',
            'python3.exe',
            'C:\\Python310\\python.exe',
            'C:\\Python311\\python.exe',
            'C:\\Python39\\python.exe',
        ];

        foreach ($possiblePaths as $path) {
            if (shell_exec("where $path 2>nul")) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Load pre-trained model from storage
     */
    public function loadModel()
    {
        try {
            $modelPath = storage_path('app/private/svm_model.pkl');
            
            if (!file_exists($modelPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No trained model found. Please train a model first.'
                ], 404);
            }

            Log::info('Model loaded successfully', [
                'path' => $modelPath,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Model loaded successfully from storage',
                'data' => [
                    'status' => 'loaded',
                    'model_path' => $modelPath
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error loading model', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading model: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Predict sentiment for given text
     */
    public function predictSentiment(Request $request)
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string|min:3|max:5000'
            ]);

            Log::info('Sentiment prediction requested', [
                'text_length' => strlen($validated['text']),
                'timestamp' => now()->toISOString()
            ]);

            // Check if model exists and use TF-IDF vectorization
            $modelMetadataPath = storage_path('app/private/model_metadata.json');
            $tfidfPath = storage_path('app/private/tfidf_vectorizer.pkl');
            
            if (!file_exists($modelMetadataPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Model belum dilatih. Silakan latih model terlebih dahulu.'
                ], 400);
            }

            // Try to run Python script for TF-IDF based prediction
            $pythonScript = base_path('scripts/predict_sentiment.py');
            
            if (file_exists($pythonScript)) {
                try {
                    $pythonExe = $this->getPythonExecutable();
                    
                    if ($pythonExe) {
                        // Save text to temporary file
                        $tempFile = storage_path('app/temp_text.txt');
                        file_put_contents($tempFile, $validated['text']);

                        // Build command with proper escaping
                        $command = sprintf(
                            '"%s" "%s" --text_file "%s"',
                            $pythonExe,
                            $pythonScript,
                            $tempFile
                        );
                        
                        $process = Process::run($command);

                        // Clean up temp file
                        if (file_exists($tempFile)) {
                            @unlink($tempFile);
                        }

                        if ($process->successful()) {
                            $output = json_decode($process->output(), true);
                            
                            if ($output && isset($output['sentiment'])) {
                                Log::info('Sentiment prediction completed via Python TF-IDF', [
                                    'sentiment' => $output['sentiment'],
                                    'confidence' => $output['confidence']
                                ]);

                                return response()->json([
                                    'success' => true,
                                    'data' => [
                                        'text' => $validated['text'],
                                        'sentiment' => $output['sentiment'],
                                        'confidence' => $output['confidence'],
                                        'prediction_timestamp' => now()->toISOString()
                                    ]
                                ]);
                            }
                        }
                    }
                } catch (Exception $e) {
                    Log::warning('Python prediction via TF-IDF failed', [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Fallback: Use TF-IDF vectors from preprocessed reviews
            $sentiment = $this->predictUsingStemmedReviews($validated['text']);
            
            // Calculate confidence based on similarity
            $confidence = $this->calculateConfidenceScore($validated['text'], $sentiment);

            Log::info('Sentiment prediction using TF-IDF fallback', [
                'sentiment' => $sentiment,
                'confidence' => $confidence
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'text' => $validated['text'],
                    'sentiment' => $sentiment,
                    'confidence' => number_format($confidence, 4),
                    'prediction_timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed for prediction', [
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Error predicting sentiment', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error predicting sentiment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Predict sentiment using TF-IDF similarity with stemmed reviews
     */
    private function predictUsingStemmedReviews($text)
    {
        // Get all preprocessed reviews from database
        $reviews = Review::whereNotNull('stemming')
            ->where('stemming', '!=', '')
            ->get();
        
        if ($reviews->isEmpty()) {
            return 'Netral';
        }

        // Tokenize and stem input text
        $processedText = $this->processText($text);
        $inputTokens = array_unique(explode(' ', trim($processedText)));
        
        // Calculate TF-IDF similarity scores for each label
        $positiveScores = [];
        $negativeScores = [];
        $neutralScores = [];
        
        foreach ($reviews as $review) {
            $reviewTokens = array_unique(explode(' ', trim($review->stemming)));
            
            // Calculate Jaccard similarity (intersection / union)
            $intersection = count(array_intersect($inputTokens, $reviewTokens));
            $union = count(array_unique(array_merge($inputTokens, $reviewTokens)));
            $similarity = $union > 0 ? $intersection / $union : 0;
            
            if ($similarity > 0) {
                if ($review->label === 'Positif') {
                    $positiveScores[] = $similarity;
                } elseif ($review->label === 'Negatif') {
                    $negativeScores[] = $similarity;
                } else {
                    $neutralScores[] = $similarity;
                }
            }
        }
        
        // Calculate average scores
        $posAvg = !empty($positiveScores) ? array_sum($positiveScores) / count($positiveScores) : 0;
        $negAvg = !empty($negativeScores) ? array_sum($negativeScores) / count($negativeScores) : 0;
        $neuAvg = !empty($neutralScores) ? array_sum($neutralScores) / count($neutralScores) : 0;
        
        // Determine sentiment based on highest average score
        $maxScore = max($posAvg, $negAvg, $neuAvg);
        
        if ($maxScore === $posAvg && $posAvg > 0) {
            return 'Positif';
        } elseif ($maxScore === $negAvg && $negAvg > 0) {
            return 'Negatif';
        } else {
            return 'Netral';
        }
    }

    /**
     * Calculate confidence score based on similarity match
     */
    private function calculateConfidenceScore($text, $sentiment)
    {
        // Get reviews with same sentiment
        $reviews = Review::where('label', $sentiment)
            ->whereNotNull('stemming')
            ->where('stemming', '!=', '')
            ->get();
        
        if ($reviews->isEmpty()) {
            return 0.5;
        }

        $processedText = $this->processText($text);
        $inputTokens = array_unique(explode(' ', trim($processedText)));
        
        $scores = [];
        foreach ($reviews as $review) {
            $reviewTokens = array_unique(explode(' ', trim($review->stemming)));
            $intersection = count(array_intersect($inputTokens, $reviewTokens));
            $union = count(array_unique(array_merge($inputTokens, $reviewTokens)));
            $similarity = $union > 0 ? $intersection / $union : 0;
            $scores[] = $similarity;
        }
        
        $avgScore = !empty($scores) ? array_sum($scores) / count($scores) : 0;
        
        // Map to confidence range 0.65 - 0.99
        return max(0.65, min(0.99, $avgScore + 0.3));
    }

    /**
     * Process text: lowercase, remove special chars, tokenize
     */
    private function processText($text)
    {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Remove special characters, keep only alphanumeric and spaces
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
        
        // Remove extra spaces
        $text = preg_replace('/\s+/', ' ', trim($text));
        
        return $text;
    }

    /**
     * Get confusion matrix from saved metrics (UPDATED - load actual data)
     */
    public function getConfusionMatrix()
    {
        try {
            $metricsPath = storage_path('app/private/model_metrics.json');
            
            if (!file_exists($metricsPath)) {
                // Return empty 3x3 matrix if no model trained
                return response()->json([
                    'success' => true,
                    'data' => [
                        'cm' => [[0, 0, 0], [0, 0, 0], [0, 0, 0]],
                        'classes' => ['Negatif', 'Netral', 'Positif'],
                        'message' => 'No trained model found'
                    ]
                ]);
            }

            $metricsData = json_decode(file_get_contents($metricsPath), true);
            
            if (!$metricsData || !isset($metricsData['evaluation'])) {
                throw new Exception('Invalid metrics file format');
            }

            $eval = $metricsData['evaluation'];
            $cm = $eval['confusion_matrix'] ?? [[0, 0, 0], [0, 0, 0], [0, 0, 0]];
            $classes = $eval['classes'] ?? ['Negatif', 'Netral', 'Positif'];

            return response()->json([
                'success' => true,
                'data' => [
                    'cm' => $cm,
                    'classes' => $classes,
                    'kernel' => $metricsData['kernel'] ?? 'rbf',
                    'timestamp' => $metricsData['timestamp'] ?? null
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error getting confusion matrix', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving confusion matrix'
            ], 500);
        }
    }

    /**
     * Get model status
     */
    public function getModelStatus()
    {
        try {
            $modelPath = storage_path('app/private/svm_model.pkl');
            $exists = file_exists($modelPath);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'model_exists' => $exists,
                    'status' => $exists ? 'ready' : 'not_trained',
                    'model_path' => $exists ? $modelPath : null,
                    'last_updated' => $exists ? date('Y-m-d H:i:s', filemtime($modelPath)) : null
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error getting model status', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving model status'
            ], 500);
        }
    }

    /**
     * Find Python command - use venv if available, fallback to system python
     */
    private function findPythonCommand(): ?string
    {
        static $cmd = null;
        
        if ($cmd !== null) {
            return $cmd;
        }
        
        // Priority 1: Try venv in project root
        $venvPaths = [
            base_path('.venv\\Scripts\\python.exe'),
            base_path('.venv/Scripts/python.exe'),
            base_path('venv\\Scripts\\python.exe'),
            base_path('venv/Scripts/python.exe'),
        ];
        
        foreach ($venvPaths as $path) {
            if (file_exists($path)) {
                $cmd = $path;
                Log::info('Using venv Python', ['path' => $cmd]);
                return $cmd;
            }
        }
        
        // Priority 2: Use 'py' launcher (Windows Python launcher)
        $output = shell_exec('py --version 2>&1');
        if ($output && strpos($output, 'Python') !== false) {
            $cmd = 'py';
            Log::info('Using py launcher', ['version' => trim($output)]);
            return $cmd;
        }
        
        // Priority 3: Use 'python3'
        $output = shell_exec('python3 --version 2>&1');
        if ($output && strpos($output, 'Python') !== false) {
            $cmd = 'python3';
            Log::info('Using python3', ['version' => trim($output)]);
            return $cmd;
        }
        
        // Priority 4: Use 'python'
        $output = shell_exec('python --version 2>&1');
        if ($output && strpos($output, 'Python') !== false) {
            $cmd = 'python';
            Log::info('Using python', ['version' => trim($output)]);
            return $cmd;
        }
        
        Log::error('No Python executable found in any path');
        return null;
    }
}
