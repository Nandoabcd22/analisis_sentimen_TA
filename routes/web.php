<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\AuthController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes - Require Authentication
Route::middleware('auth')->group(function () {
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
    Route::post('/api/clean-database-utf8', [DashboardController::class, 'cleanDatabaseUtf8'])->name('clean.database.utf8');

    Route::get('/preprocessing', function () {
        return view('preprocessing');
    })->name('preprocessing');

    // Classification Routes
    Route::get('/klasifikasi', function () {
        return view('klasifikasi');
    })->name('klasifikasi');

    Route::post('/api/train-model', [ClassificationController::class, 'trainModel'])->name('train.model');
    Route::post('/api/load-model', [ClassificationController::class, 'loadModel'])->name('load.model');
    Route::post('/api/predict-sentiment', [ClassificationController::class, 'predictSentiment'])->name('predict.sentiment');
    Route::get('/api/model-metrics', [ClassificationController::class, 'getMetrics'])->name('api.model.metrics');
    Route::get('/api/confusion-matrix', [ClassificationController::class, 'getConfusionMatrix'])->name('api.confusion.matrix');
    Route::get('/api/word-cloud', [ClassificationController::class, 'getWordCloud'])->name('api.word.cloud');
    Route::get('/api/wordcloud-image', [ClassificationController::class, 'getWordCloudImage'])->name('api.wordcloud.image');
    Route::get('/api/model-status', [ClassificationController::class, 'getModelStatus'])->name('api.model.status');
    Route::get('/api/debug/python-setup', [ClassificationController::class, 'debugPythonSetup'])->name('debug.python.setup');
    Route::post('/api/debug/test-training', [ClassificationController::class, 'testTrainingSetup'])->name('debug.test.training');

    Route::get('/tfidf', function () {
        return view('tfidf');
    })->name('tfidf');

    // TF-IDF and Data Processing Routes
    Route::post('/process-tfidf', [DashboardController::class, 'processTfidf'])->name('process.tfidf');
    Route::post('/apply-smote', [DashboardController::class, 'applySmote'])->name('apply.smote');
    Route::get('/api/tfidf-results', [DashboardController::class, 'getTfidfResults'])->name('api.tfidf.results');
});
