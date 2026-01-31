<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DashboardController;

class TestPreprocess extends Command
{
    protected $signature = 'test:preprocess';
    protected $description = 'Test batch preprocessing';

    public function handle()
    {
        try {
            $unprocessed = \App\Models\Review::whereNull('case_folding')->count();
            $this->info("Records to process: {$unprocessed}");
            
            if ($unprocessed > 0) {
                $this->info("Starting batch preprocessing...");
                
                $controller = app(DashboardController::class);
                $result = $controller->preprocessData();
                
                $this->info("Preprocessing completed!");
                
                $completed = \App\Models\Review::whereNotNull('case_folding')->count();
                $this->info("Records now processed: {$completed}");
            } else {
                $this->info("No records to process.");
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . " Line: " . $e->getLine());
        }
    }
}
