<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/upload-file', [DashboardController::class, 'uploadFile'])->name('upload.file');
Route::get('/get-reviews', [DashboardController::class, 'getReviews'])->name('get.reviews');
Route::post('/preprocess-data', [DashboardController::class, 'preprocessData'])->name('preprocess.data');
Route::get('/get-preprocessed-reviews', [DashboardController::class, 'getPreprocessedReviews'])->name('get.preprocessed.reviews');
Route::get('/get-statistics', [DashboardController::class, 'getStatistics'])->name('get.statistics');
Route::get('/api/statistics', [DashboardController::class, 'getStatistics'])->name('api.statistics');

Route::get('/preprocessing', function () {
    return view('preprocessing');
})->name('preprocessing');


Route::get('/klasifikasi', function () {
    return view('klasifikasi');
})->name('klasifikasi');

Route::get('/hasil-laporan', function () {
    return view('hasil-laporan');
})->name('hasil-laporan');

Route::get('/tfidf', function () {
    return view('tfidf');
})->name('tfidf');

// TF-IDF and Data Processing Routes
Route::post('/process-tfidf', [DashboardController::class, 'processTfidf'])->name('process.tfidf');
Route::post('/apply-smote', [DashboardController::class, 'applySmote'])->name('apply.smote');
Route::get('/api/tfidf-results', [DashboardController::class, 'getTfidfResults'])->name('api.tfidf.results');
