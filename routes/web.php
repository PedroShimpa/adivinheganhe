<?php

use App\Http\Controllers\AdivinhacoesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PagamentosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RespostaController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Broadcast::routes(['middleware' => ['web', 'auth']]);

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/adivinhacoes/create', [AdivinhacoesController::class, 'create'])->name('adivinhacoes.create');
    Route::post('/adivinhacoes/create', [AdivinhacoesController::class, 'store'])->name('adivinhacoes.store');
    Route::post('/responder', [RespostaController::class, 'enviar'])->name('resposta.enviar');
    Route::get('/tentativas/comprar', [PagamentosController::class, 'index'])->name('tentativas.shop');
    Route::post('/tentativas/comprar', [PagamentosController::class, 'buy'])->name('tentativas.comprar');
    Route::get('/dicas/{adivinhacao}/comprar', [PagamentosController::class, 'index_buy_dica'])->name('dicas.index_buy');
    Route::post('/dicas/{adivinhacao}/comprar', [PagamentosController::class, 'buy_dica'])->name('dicas.comprar');
});
Route::post('/webhook/mercadopago', [PagamentosController::class, 'webhook']);


Route::get('/adivinhacoes/{adivinhacao}', [AdivinhacoesController::class, 'index'])->name('adivinhacoes.index');
Route::get('/adivinhacoes/{adivinhacao}/respostas-iframe', [AdivinhacoesController::class, 'respostas'])->name('adivinhacoes.respostas');


require __DIR__ . '/auth.php';
