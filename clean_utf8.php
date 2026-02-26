<?php
// Quick script to clean UTF-8 in database
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    $request = \Illuminate\Http\Request::capture()
);

// Now use the app
$app->make(\App\Http\Controllers\DashboardController::class)->cleanDatabaseUtf8();
