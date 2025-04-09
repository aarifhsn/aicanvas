<?php

use App\Http\Controllers\AIController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AIController::class, 'index'])->name('ai.index');
Route::middleware(['throttle:5,1'])
    ->post('/ai/generate', [AIController::class, 'generateText'])
    ->name('ai.generate');