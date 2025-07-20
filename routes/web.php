<?php

use App\Http\Controllers\AdivinhacoesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RespostaController;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinhacoesRespostas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Broadcast::routes(['middleware' => ['web', 'auth']]);

Route::get('/', function () {
    $limitExceded = true;
    if(Auth::check()) {
        $limitExceded = AdivinhacoesRespostas::where('user_id', auth()->user()->id)->whereDate('created_at', today())->count() >= env('MAX_ADIVINHATIONS', 10);
    }
    $adivinhacoes = Adivinhacoes::where('resolvida', 'N')->get([
        'id',
        'titulo',
        'imagem',
        'descricao',
        'premio'
    ]);

    $adivinhacoes->filter(function($a) {
            $a->count_respostas = AdivinhacoesRespostas::where('adivinhacao_id', $a->id)->count();
    });

    $premios = AdivinhacoesPremiacoes::select('adivinhacao_id', 'adivinhacoes.titulo', 'adivinhacoes.premio', 'users.username', 'premio_enviado')
    ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_premiacoes.adivinhacao_id')
    ->join('users', 'users.id', '=', 'adivinhacoes_premiacoes.user_id')
    ->orderby('adivinhacoes_premiacoes.id', 'desc')
    ->get();

    return view('home')->with(compact('adivinhacoes', 'limitExceded', 'premios'));
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware('auth')->group(function () {
Route::get('/adivinhacoes/create', [AdivinhacoesController::class, 'create'])->name('adivinhacoes.create');
Route::post('/adivinhacoes/create', [AdivinhacoesController::class, 'store'])->name('adivinhacoes.store');
Route::post('/responder', [RespostaController::class, 'enviar'])->name('resposta.enviar');
});

Route::get('/adivinhacoes/respostas/{adivinhacao_id}', [AdivinhacoesController::class, 'respostas'])->name('adivinhacoes.respostas')->whereNumber('adivinhacao_id');


require __DIR__ . '/auth.php';
