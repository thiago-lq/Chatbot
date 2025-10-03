<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chatbot', [ChatbotController::class, 'index']);
Route::post('/chatbot/send', [ChatbotController::class, 'sendMessage']);
Route::post('/chatbot/clear', [ChatbotController::class, 'clearHistory']);
Route::get('/chatbot/history', [ChatbotController::class, 'getHistory']);

