<?php

use App\Http\Controllers\AdivinhacoesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PagamentosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RespostaController;
use App\Http\Controllers\SuporteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Pusher\Pusher;

Broadcast::routes(['middleware' => ['web', 'auth']]);

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/salvar_fingerprint', [HomeController::class, 'saveFingerprint'])->name('salvar_fingerprint');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/adivinhacoes/create', [AdivinhacoesController::class, 'create'])->name('adivinhacoes.create');
    Route::post('/adivinhacoes/create', [AdivinhacoesController::class, 'store'])->name('adivinhacoes.store');
    Route::get('/adivinhacoes/view/{adivinhacao}', [AdivinhacoesController::class, 'view'])->name('adivinhacoes.view');
    Route::put('/adivinhacoes/{adivinhacaoId}', [AdivinhacoesController::class, 'update'])->name('adivinhacoes.update')->whereNumber('adivinhacaoId');
    Route::post('/responder', [RespostaController::class, 'enviar'])->name('resposta.enviar');
    Route::get('/tentativas/comprar', [PagamentosController::class, 'index_buy_attempts'])->name('tentativas.shop');
    Route::post('/tentativas/comprar', [PagamentosController::class, 'buy_attempts'])->name('tentativas.comprar');
    Route::get('/dicas/{adivinhacao}/comprar', [PagamentosController::class, 'index_buy_dica'])->name('dicas.index_buy');
    Route::post('/dicas/{adivinhacao}/comprar', [PagamentosController::class, 'buy_dica'])->name('dicas.comprar');

    Route::get('/meus_premios', [HomeController::class, 'meusPremios'])->name('meus_premios');
});

Route::post('/webhook/mercadopago', [PagamentosController::class, 'webhook']);
Route::get('/hall-da-fama', [HomeController::class, 'hallOfFame'])->name('hall_da_fama');
Route::get('/sobre', [HomeController::class, 'sobre'])->name('sobre');


Route::get('/adivinhacoes/{adivinhacao}', [AdivinhacoesController::class, 'index'])->name('adivinhacoes.index');
Route::get('/adivinhacoes/{adivinhacao}/respostas-iframe', [AdivinhacoesController::class, 'respostas'])->name('adivinhacoes.respostas');

Route::get('/suporte', [SuporteController::class, 'new_help'])->name('suporte.index');
Route::post('/suporte', [SuporteController::class, 'store'])->name('suporte.store');


Route::post('/broadcasting/auth-mixed', function (Request $request) {
    $pusher = new Pusher(
        env('REVERB_APP_KEY'),
        env('REVERB_APP_SECRET'),
        env('REVERB_APP_ID'),
        ['cluster' => env('PUSHER_APP_CLUSTER')]
    );

    $user = auth()->user();
    if ($user) {
        $id   = 'user-' . $user->id;
        $info = ['name' => $user->name];
    } else {
        $id   = 'guest-' . substr(md5($request->ip() . microtime()), 0, 8);
        $info = ['name' => 'Visitante'];
    }

    return $pusher->presence_auth(
        $request->channel_name,
        $request->socket_id,
        $id,
        $info
    );
});

Route::get('/ping', function () {
    return 'pong';
});

require __DIR__ . '/auth.php';
