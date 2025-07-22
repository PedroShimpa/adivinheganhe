<?php

use App\Http\Controllers\AdivinhacoesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RespostaController;
use App\Http\Controllers\TentativasController;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinhacoesRespostas;
use Illuminate\Support\Facades\Auth;
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
Route::get('/tentativas/comprar', [TentativasController::class, 'index'])->name('tentativas.shop');
Route::post('/tentativas/comprar', [TentativasController::class, 'buy'])->name('tentativas.comprar');
Route::get('/tentativas/success', [TentativasController::class, 'success'])->name('tentativas.success');
Route::get('/tentativas/fail', [TentativasController::class, 'fail'])->name('tentativas.fail');
Route::get('/tentativas/pending', [TentativasController::class, 'pending'])->name('tentativas.pending');

});

Route::post('/webhook/mercadopago', [TentativasController::class, 'webhook']);


Route::get('/adivinhacoes/{adivinhacao}/respostas-iframe', [AdivinhacoesController::class, 'respostas'])->name('adivinhacoes.respostas');


require __DIR__ . '/auth.php';
