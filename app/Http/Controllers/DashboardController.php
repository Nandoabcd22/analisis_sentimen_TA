<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics and reviews.
     */
    public function index()
    {
        // Get statistics
        $total = Review::count();
        $positif = Review::where('label', 'Positif')->count();
        $negatif = Review::where('label', 'Negatif')->count();
        $netral = Review::where('label', 'Netral')->count();

        // Get recent reviews for the table (empty collection if no data)
        $reviews = $total > 0 ? Review::latest()->paginate(10) : collect();

        return view('dashboard', compact('total', 'positif', 'negatif', 'netral', 'reviews'));
    }

    /**
     * Handle file upload and data processing.
     */
    public function uploadFile(Request $request)
    {
        // Enhanced validation
        $validator = Validator::make($request->all(), [
            'datafile' => [
                'required',
                'file',
                'mimes:csv,txt,xlsx,xls',
                'max:10240', // Max 10MB
                function ($attribute, $value, $fail) {
                    if ($value && $value->getSize() === 0) {
                        $fail('The file cannot be empty.');
                    }
                },
            ],
        ], [
            'datafile.required' => 'Please select a file to upload.',
            'datafile.mimes' => 'Invalid file format. Please upload a CSV, TXT, or Excel file.',
            'datafile.max' => 'File size cannot exceed 10MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->all()
            ], 422);
        }

        try {
            $file = $request->file('datafile');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $fileExtension = $file->getClientOriginalExtension();
            
            // Enhanced logging
            \Log::info('File upload started', [
                'filename' => $fileName,
                'size' => $fileSize,
                'extension' => $fileExtension,
                'mime_type' => $file->getMimeType(),
                'timestamp' => now()->toISOString()
            ]);
            
            // Validate file content
            if ($fileSize === 0) {
                throw new \Exception('File is empty');
            }
            
            // Clear existing data
            Review::truncate();
            
            \Log::info('Existing data cleared successfully');
            
            // Process the uploaded file
            $processedRows = $this->processUploadedFile($file);
            
            if ($processedRows === 0) {
                throw new \Exception('No valid data found in the uploaded file');
            }
            
            \Log::info('File processing completed', [
                'processed_rows' => $processedRows,
                'timestamp' => now()->toISOString()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "File uploaded and processed successfully! {$processedRows} records imported.",
                'data' => [
                    'filename' => $fileName,
                    'size' => $fileSize,
                    'processed_rows' => $processedRows
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Upload validation failed', [
                'errors' => $e->errors(),
                'file' => $request->file('datafile')?->getClientOriginalName()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $validator->errors()->all()),
                'errors' => $validator->errors()->toArray()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Upload error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'filename' => $request->file('datafile')?->getClientOriginalName()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error uploading file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Normalize various label formats from CSV into standard labels
     */
    private function normalizeLabel($value)
    {
        $v = strtolower(trim((string)$value));
        // Map common variants
        $positives = ['positif','positive','pos','1','p','good','bagus'];
        $negatives = ['negatif','negative','neg','-1','n','bad','buruk'];
        $neutrals  = ['netral','neutral','neu','0','neut'];

        if (in_array($v, $positives, true)) return 'Positif';
        if (in_array($v, $negatives, true)) return 'Negatif';
        if (in_array($v, $neutrals, true)) return 'Netral';

        // Fallback: capitalize first letter if already one of our labels
        if ($v === 'positif' || $v === 'negatif' || $v === 'netral') {
            return ucfirst($v);
        }
        // Unknown -> Netral to be safe (or keep original?)
        return ucfirst($v) ?: 'Netral';
    }

    /**
     * Get statistics data for AJAX requests.
     */
    public function getStatistics()
    {
        $total = Review::count();
        $positif = Review::where('label', 'Positif')->count();
        $negatif = Review::where('label', 'Negatif')->count();
        $netral = Review::where('label', 'Netral')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'positif' => $positif,
                'negatif' => $negatif,
                'netral' => $netral
            ]
        ]);
    }

    /**
     * Get reviews data for AJAX requests (pagination, search).
     */
    public function getReviews(Request $request)
    {
        $query = Review::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('review', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('label', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('username', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $reviews = $query->latest()->paginate($perPage);

        // Convert to array with proper structure
        $reviewsData = [];
        foreach ($reviews->items() as $review) {
            $reviewsData[] = [
                'id' => (int)$review->id,
                'username' => (string)$review->username,
                'review' => (string)$review->review,
                'label' => (string)$review->label,
                'created_at' => $review->created_at ? $review->created_at->format('Y-m-d H:i:s') : null,
            ];
        }

        $payload = [
            'success' => true,
            'data' => $reviewsData,
            'pagination' => [
                'current_page' => (int)$reviews->currentPage(),
                'last_page' => (int)$reviews->lastPage(),
                'per_page' => (int)$reviews->perPage(),
                'total' => (int)$reviews->total(),
                'from' => $reviews->firstItem() ? (int)$reviews->firstItem() : 0,
                'to' => $reviews->lastItem() ? (int)$reviews->lastItem() : 0
            ]
        ];

        return response()->json(
            $payload,
            200,
            [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );
    }

    /**
     * Process uploaded file (CSV/Excel parsing).
     */
    private function processUploadedFile($file)
    {
        $path = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());
        $processedCount = 0;
        $skippedCount = 0;
        
        try {
            if ($extension === 'csv' || $extension === 'txt') {
                $processedCount = $this->processCsvFile($path);
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                $processedCount = $this->processExcelFile($path);
            } else {
                throw new \Exception('Unsupported file format: ' . $extension);
            }
            
            \Log::info('File processing completed', [
                'processed_count' => $processedCount,
                'skipped_count' => $skippedCount,
                'extension' => $extension
            ]);
            
            return $processedCount;
            
        } catch (\Exception $e) {
            \Log::error('File processing error', [
                'message' => $e->getMessage(),
                'file_path' => $path,
                'extension' => $extension
            ]);
            throw $e;
        }
    }
    
    /**
     * Process CSV file
     */
    private function processCsvFile($path)
    {
        $processedCount = 0;
        $handle = fopen($path, 'r');
        
        if (!$handle) {
            throw new \Exception('Cannot open file for reading');
        }
        
        try {
            // Detect delimiter
            $delimiter = $this->detectCsvDelimiter($path);
            
            // Read header
            $header = fgetcsv($handle, 0, $delimiter);
            if ($header === false) {
                throw new \Exception('Cannot read CSV header');
            }
            
            // Validate header structure - more flexible column names
            $header = array_map('strtolower', array_map('trim', $header));
            
            // Define possible column names for each required field
            $columnPatterns = [
                'username' => ['username', 'user', 'name', 'pengguna', 'nama'],
                'review' => ['review', 'text', 'comment', 'ulasan', 'komentar', 'isi'],
                'label' => ['label', 'sentiment', 'class', 'kategori', 'sentimen']
            ];
            
            $columnMapping = [];
            foreach ($columnPatterns as $requiredField => $possibleNames) {
                $found = false;
                foreach ($possibleNames as $name) {
                    if (in_array($name, $header)) {
                        $columnMapping[$requiredField] = array_search($name, $header);
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    \Log::warning("Column '{$requiredField}' not found. Available columns: " . implode(', ', $header));
                }
            }
            
            // At minimum, we need review column
            if (!isset($columnMapping['review'])) {
                throw new \Exception('CSV must contain a review/text column. Available columns: ' . implode(', ', $header) . '. Expected: review, text, comment, ulasan, or komentar');
            }
            
            // Process data rows
            $batchSize = 100;
            $batch = [];
            $rowNumber = 2; // Start from 2 (after header)
            
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                try {
                    $rowData = $this->extractRowData($row, $columnMapping, $rowNumber);
                    
                    if ($rowData) {
                        $batch[] = $rowData;
                        $processedCount++;
                        
                        // Insert in batches for better performance
                        if (count($batch) >= $batchSize) {
                            Review::insert($batch);
                            $batch = [];
                        }
                    }
                    
                } catch (\Exception $e) {
                    \Log::warning('Skipped row ' . $rowNumber . ': ' . $e->getMessage());
                }
                
                $rowNumber++;
            }
            
            // Insert remaining batch
            if (!empty($batch)) {
                Review::insert($batch);
            }
            
            return $processedCount;
            
        } finally {
            fclose($handle);
        }
    }
    
    /**
     * Process Excel file
     */
    private function processExcelFile($path)
    {
        // For now, delegate to CSV processing (you can implement proper Excel parsing later)
        return $this->processCsvFile($path);
    }
    
    /**
     * Detect CSV delimiter
     */
    private function detectCsvDelimiter($path)
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return ','; // Default to comma
        }
        
        $firstLine = fgets($handle);
        fclose($handle);
        
        $delimiters = [',', ';', '\t', '|'];
        $counts = [];
        
        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($firstLine, $delimiter);
        }
        
        return array_keys($counts, max($counts))[0];
    }
    
    /**
     * Map CSV columns to required fields
     */
    private function mapColumns($header, $requiredColumns)
    {
        $mapping = [];
        
        foreach ($requiredColumns as $required) {
            $found = false;
            
            // Try exact match first
            $index = array_search($required, $header);
            if ($index !== false) {
                $mapping[$required] = $index;
                $found = true;
                continue;
            }
            
            // Try partial match
            foreach ($header as $index => $column) {
                if (stripos($column, $required) !== false) {
                    $mapping[$required] = $index;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                return false;
            }
        }
        
        return $mapping;
    }
    
    /**
     * Extract data from row
     */
    private function extractRowData($row, $columnMapping, $rowNumber)
    {
        // Extract data with fallbacks for missing columns
        $username = isset($columnMapping['username']) ? trim($row[$columnMapping['username']] ?? '') : 'Anonymous';
        $review = isset($columnMapping['review']) ? trim($row[$columnMapping['review']] ?? '') : '';
        $label = isset($columnMapping['label']) ? trim($row[$columnMapping['label']] ?? '') : '';
        
        // Validate required fields
        if (empty($review)) {
            \Log::warning("Empty review in row {$rowNumber}");
            return null; // Skip this row instead of throwing exception
        }
        
        return [
            'username' => $username ?: 'Anonymous',
            'review' => $review,
            'label' => $this->normalizeLabel($label),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Process data through preprocessing pipeline
     */
    public function preprocessData()
    {
        try {
            set_time_limit(900);  // 15 minutes
            \Log::info('=== BATCH PREPROCESS START ===');
            
            $startTime = microtime(true);
            
            // Get ALL unprocessed reviews at once
            $reviews = Review::where(function($q) {
                $q->whereNull('case_folding')->orWhere('case_folding', '');
            })->get();
            
            $totalReviews = Review::count();
            $unprocessedCount = $reviews->count();
            
            \Log::info("Total: {$totalReviews}, Unprocessed: {$unprocessedCount}");
            
            if ($totalReviews === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data found. Please upload a file first.'
                ], 400);
            }
            
            if ($unprocessedCount === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'All data has already been preprocessed.',
                    'data' => [
                        'processed_count' => 0,
                        'processing_time' => 0,
                        'total_reviews' => $totalReviews,
                        'remaining_unprocessed' => 0,
                    ]
                ]);
            }

            // Prepare batch data for Python
            $batchData = [];
            foreach ($reviews as $review) {
                $batchData[] = [
                    'id' => $review->id,
                    'text' => $review->review
                ];
            }
            
            \Log::info("Processing {$unprocessedCount} reviews in batch mode");

            // Process entire batch via Python
            $results = $this->runPythonBatchPreprocess($batchData);
            
            \Log::info("Batch preprocessing returned. Is array: " . (is_array($results) ? 'YES' : 'NO'));
            \Log::info("Results count: " . (is_array($results) ? count($results) : 'N/A'));
            
            if (!is_array($results) || empty($results)) {
                \Log::error("Invalid results from Python. Results type: " . gettype($results));
                if (is_array($results)) {
                    \Log::error("Results array is empty!");
                }
                throw new \Exception('No results from Python batch processing');
            }

            \Log::info("First result sample: " . json_encode($results[0]));

            // Update database with results
            $processedCount = 0;
            foreach ($results as $idx => $result) {
                try {
                    \Log::info("Processing result $idx: ID=" . $result['id']);
                    
                    $updated = Review::where('id', $result['id'])->update([
                        'case_folding' => $result['case_folding'] ?? '',
                        'cleansing'    => $result['cleansing'] ?? '',
                        'normalisasi'  => $result['normalisasi'] ?? '',
                        'tokenizing'   => is_array($result['tokenizing'] ?? null) ? json_encode($result['tokenizing']) : '[]',
                        'stopword'     => is_array($result['stopword'] ?? null) ? json_encode($result['stopword']) : '[]',
                        'stemming'     => is_array($result['stemming'] ?? null) ? json_encode($result['stemming']) : '[]',
                    ]);
                    
                    \Log::info("Update for ID {$result['id']}: " . ($updated > 0 ? "SUCCESS ($updated rows)" : "FAILED (0 rows)"));
                    
                    if ($updated > 0) {
                        $processedCount++;
                    }
                } catch (\Exception $e) {
                    \Log::error("Failed to update review {$result['id']}: " . $e->getMessage());
                }
            }
            
            \Log::info("Total processed: $processedCount out of " . count($results));

            $processingTime = round(microtime(true) - $startTime, 2);
            
            return response()->json([
                'success' => true,
                'message' => "Batch processed {$processedCount} reviews in {$processingTime}s",
                'data' => [
                    'processed_count' => $processedCount,
                    'processing_time' => $processingTime,
                    'total_reviews' => $totalReviews,
                    'remaining_unprocessed' => 0,
                ]
            ]);
            
        } catch (\Throwable $e) {
            \Log::error('Preprocessing error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Preprocessing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function forceUtf8($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            // Convert to valid UTF-8 and strip invalid sequences
            $converted = @mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            if ($converted === false) {
                // Last resort: drop invalid bytes
                $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
            }

            return $converted === false ? '' : $converted;
        }

        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                // Also sanitize keys just in case
                $safeKey = is_string($k) ? $this->forceUtf8($k) : $k;
                $out[$safeKey] = $this->forceUtf8($v);
            }
            return $out;
        }

        if (is_object($value)) {
            // Convert objects to arrays and sanitize
            return $this->forceUtf8((array) $value);
        }

        // Resources/unknown types
        return '';
    }

    /**
     * Simple text cleaning to avoid encoding issues
     */
    private function runPythonBatchPreprocess($batchData): array
    {
        // Find Python executable
        $pythonCmd = $this->findPythonCommand();
        if (!$pythonCmd) {
            throw new \RuntimeException('Python not found in PATH');
        }
        
        $scriptPath = base_path('scripts/preprocessing.py');
        if (!file_exists($scriptPath)) {
            throw new \RuntimeException('Preprocessing script not found');
        }
        
        // Create temporary JSON file for batch data
        $tempFile = tempnam(sys_get_temp_dir(), 'preprocess_');
        $jsonData = json_encode($batchData);
        
        \Log::info("Batch file: {$tempFile}, Records: " . count($batchData));
        
        if (file_put_contents($tempFile, $jsonData) === false) {
            throw new \RuntimeException('Failed to write temporary data file');
        }
        
        try {
            // Prepare command to process batch via Python
            $escapedScript = escapeshellarg($scriptPath);
            $escapedFile = escapeshellarg($tempFile);
            $cmd = "{$pythonCmd} {$escapedScript} --batch {$escapedFile} 2>&1";
            
            \Log::info("Executing: {$cmd}");
            
            // Execute
            $output = shell_exec($cmd);
            
            if (!$output) {
                throw new \RuntimeException('Python batch execution returned no output');
            }
            
            \Log::info("Python output length: " . strlen($output));
            \Log::info("Python output (first 500 chars): " . substr($output, 0, 500));
            
            // Extract JSON from output - look for [ character
            $output = trim($output);
            $jsonStart = strpos($output, '[');
            
            if ($jsonStart === false) {
                \Log::error("No JSON array found in output: " . substr($output, 0, 1000));
                throw new \RuntimeException('Invalid output from Python batch script');
            }
            
            // Extract complete JSON
            $json = substr($output, $jsonStart);
            
            \Log::info("Extracted JSON length: " . strlen($json));
            
            $result = json_decode($json, true);
            
            if (!is_array($result)) {
                throw new \RuntimeException('Failed to parse Python batch output as JSON');
            }
            
            \Log::info("Batch processing completed. Results: " . count($result));
            
            return $result;
            
        } finally {
            @unlink($tempFile);
        }
    }
    
    private function runPythonPreprocess(string $text): array
    {
        // Find Python executable
        $pythonCmd = $this->findPythonCommand();
        if (!$pythonCmd) {
            throw new \RuntimeException('Python not found in PATH');
        }
        
        $scriptPath = base_path('scripts/preprocessing.py');
        if (!file_exists($scriptPath)) {
            throw new \RuntimeException('Preprocessing script not found');
        }
        
        // Prepare command
        $escapedScript = escapeshellarg($scriptPath);
        $escapedText = escapeshellarg($text);
        $cmd = "{$pythonCmd} {$escapedScript} --text {$escapedText} 2>&1";
        
        // Execute
        $output = shell_exec($cmd);
        if (!$output) {
            throw new \RuntimeException('Python execution failed');
        }
        
        // Extract JSON from output
        $output = trim($output);
        $jsonStart = strpos($output, '{');
        
        if ($jsonStart === false) {
            throw new \RuntimeException('Invalid output from Python script');
        }
        
        // Extract complete JSON
        $json = substr($output, $jsonStart);
        $result = json_decode($json, true);
        
        if (!is_array($result)) {
            throw new \RuntimeException('Failed to parse Python output as JSON');
        }
        
        return $result;
    }
    
    private function findPythonCommand(): ?string
    {
        static $cmd = null;
        
        if ($cmd !== null) {
            return $cmd;
        }
        
        foreach (['py', 'python', 'python3'] as $python) {
            $check = "where {$python} 2>nul";
            if (shell_exec($check)) {
                $cmd = $python;
                return $cmd;
            }
        }
        
        return null;
    }

    /**
     * Get preprocessed reviews data for AJAX requests.
     */
    public function getPreprocessedReviews(Request $request)
    {
        $query = Review::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('review', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('label', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('case_folding', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $reviews = $query->latest()->paginate($perPage);

        // Convert to array with proper structure
        $reviewsData = [];
        foreach ($reviews->items() as $review) {
            $tokenizing = json_decode($review->tokenizing, true) ?: [];
            $stopword = json_decode($review->stopword, true) ?: [];
            $stemming = json_decode($review->stemming, true) ?: [];
            
            // Check if preprocessed
            $isPreprocessed = !empty($review->case_folding);
            
            $reviewsData[] = [
                'id' => (int)$review->id,
                'username' => (string)$review->username,
                'review' => (string)$review->review,
                'label' => (string)$review->label,
                'case_folding' => (string)$review->case_folding,
                'cleansing' => (string)$review->cleansing,
                'normalisasi' => (string)$review->normalisasi,
                'tokenizing' => implode(' ', $tokenizing),
                'stopword' => implode(' ', $stopword),
                'stemming' => implode(' ', $stemming),
                'is_preprocessed' => $isPreprocessed,
                'status' => $isPreprocessed ? 'Selesai' : 'Belum Diproses',
                'created_at' => $review->created_at ? $review->created_at->format('Y-m-d H:i:s') : null,
            ];
        }

        $payload = [
            'success' => true,
            'data' => $reviewsData,
            'pagination' => [
                'current_page' => (int)$reviews->currentPage(),
                'last_page' => (int)$reviews->lastPage(),
                'per_page' => (int)$reviews->perPage(),
                'total' => (int)$reviews->total(),
                'from' => $reviews->firstItem() ? (int)$reviews->firstItem() : 0,
                'to' => $reviews->lastItem() ? (int)$reviews->lastItem() : 0
            ]
        ];

        return response()->json(
            $payload,
            200,
            [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );
    }
}
