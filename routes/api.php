<?php

use App\Http\Controllers\Api\AdivinhacoesController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoogleController;
use App\Http\Controllers\RespostaController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);


Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);


Route::middleware(['auth:sanctum', 'banned'])->group(function () {
    Route::get('/adivinhacoes', [AdivinhacoesController::class, 'getAtivas']);

    Route::post('/adivinhacoes/responder', [RespostaController::class, 'enviar']);
});
