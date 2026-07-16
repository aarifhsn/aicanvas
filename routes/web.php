<?php

use App\Http\Controllers\AIController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/', [AIController::class, 'index'])->name('ai.index');
Route::middleware(['throttle:5,1'])
    ->post('/ai/generate', [AIController::class, 'generateText'])
    ->name('ai.generate');

// In routes/web.php
// In routes/web.php
Route::get('/test-log', function () {
    Log::info('TEST LOG - This should appear in laravel.log');
    return 'Check storage/logs/laravel.log now';
});

Route::post('/ai/generate-stream', [AIController::class, 'generateStream'])->name('ai.generateStream');
Route::post('/ai/compare', [AIController::class, 'compare'])->name('ai.compare');

Route::get('/ai/templates', [AIController::class, 'templates'])->name('ai.templates');
Route::post('/ai/build-prompt', [AIController::class, 'buildPrompt'])->name('ai.buildPrompt');