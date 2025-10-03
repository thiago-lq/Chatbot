<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;


Route::middleware('web')->group(function () {
    Route::get('/', [ChatbotController::class, 'index']);
    Route::post('/chatbot/send', [ChatbotController::class, 'sendMessage']);
    Route::post('/chatbot/clear', [ChatbotController::class, 'cleanHistory']);
    Route::get('/chatbot/history', [ChatbotController::class, 'getHistory']);
});
