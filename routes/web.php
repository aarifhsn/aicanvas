<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\GenerationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AIController::class, 'index'])->name('ai.index');
Route::get('/ai/templates', [AIController::class, 'templates'])->name('ai.templates');
Route::post('/ai/build-prompt', [AIController::class, 'buildPrompt'])->name('ai.buildPrompt');

// Rate limited: 20 requests/min per IP — protects your API budget on a public demo
Route::middleware('throttle:20,1')->group(function () {
    Route::post('/ai/generate', [AIController::class, 'generateText'])->name('ai.generate');
    Route::post('/ai/generate-stream', [AIController::class, 'generateStream'])->name('ai.generateStream');
    Route::post('/ai/compare', [AIController::class, 'compare'])->name('ai.compare');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/history', [GenerationController::class, 'index'])->name('history.index');
    Route::delete('/history/{generation}', [GenerationController::class, 'destroy'])->name('history.destroy');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::post('/documents/{document}/ask', [DocumentController::class, 'ask'])->name('documents.ask');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
});