<?php

use App\Http\Controllers\AdivinhacoesController;
use App\Http\Controllers\AdivinheOMilhaoController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CompetitivoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PagamentosController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RespostaController;
use App\Http\Controllers\SuporteController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Broadcast::routes(['middleware' => ['web', 'auth']]);

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/salvar_fingerprint', [HomeController::class, 'saveFingerprint'])->name('salvar_fingerprint');

Route::get('/jogadores', [UsersController::class, 'jogadores'])->name('jogadores');
Route::get('/jogadores/{user}', [UsersController::class, 'view'])->name('profile.view');

Route::get('/competitivo', [CompetitivoController::class, 'index'])->name('competitivo.index');


Route::get('/ranking-classico', [HomeController::class, 'rankingClassico'])->name('ranking_classico');
Route::get('/sobre', [HomeController::class, 'sobre'])->name('sobre');

Route::get('/adivinhacoes/{adivinhacao}/comments', [AdivinhacoesController::class, 'comments'])->name('adivinhacoes.comments');
Route::get('/adivinhacoes/{adivinhacao}', [AdivinhacoesController::class, 'index'])->name('adivinhacoes.index');

Route::get('/premiacoes', [HomeController::class, 'premiacoes'])->name('premiacoes');

Route::get('/suporte', [SuporteController::class, 'new_help'])->name('suporte.index');
Route::post('/suporte', [SuporteController::class, 'store'])->name('suporte.store');

Route::get('/r', [HomeController::class, 'getRegioes'])->name('regioes.index');
Route::get('/r/{regiao}', [HomeController::class, 'get_by_region'])->name('adivinhacoes.buscar_por_regiao');

Route::get('login/google', [GoogleController::class, 'redirectToGoogle'])->name('login.google');
Route::get('login/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('/adivinhe-o-milhao', [AdivinheOMilhaoController::class, 'index'])->name('adivinhe_o_milhao.index');

Route::post('/webhook/mercadopago', [PagamentosController::class, 'webhook']);
Route::get('/banned', function () {
    return view('auth.banned');
})->name('banned.view');

#rotas autenticadas

Route::middleware(['auth', 'banned'])->group(function () {

    Route::get('/profile', [UsersController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [UsersController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [UsersController::class, 'destroy'])->name('profile.destroy');

    Route::get('/para-voce', [UsersController::class, 'para_voce'])->name('para_voce');

    Route::post('/adivinhacoes/respostas-do-usuario', [AdivinhacoesController::class, 'findUserReply'])->name('adivinhacoes.respostas_do_usuario');
    Route::post('/adivinhacoes/{adivinhacao}/comment', [AdivinhacoesController::class, 'comment'])->name('adivinhacoes.comment');
    Route::post('/adivinhacoes/{adivinhacao}/toggle-like', [AdivinhacoesController::class, 'toggleLike'])->name('adivinhacoes.toggle-like');
    Route::post('/responder', [RespostaController::class, 'enviar'])->name('resposta.enviar');

    Route::get('/palpites/comprar', [PagamentosController::class, 'index_buy_attempts'])->name('tentativas.shop');
    Route::post('/palpites/comprar', [PagamentosController::class, 'buy_attempts'])->name('tentativas.comprar');
    Route::get('/dicas/{adivinhacao}/comprar', [PagamentosController::class, 'index_buy_dica'])->name('dicas.index_buy');
    Route::post('/dicas/{adivinhacao}/comprar', [PagamentosController::class, 'buy_dica'])->name('dicas.comprar');

    Route::get('/meus_premios', [HomeController::class, 'meusPremios'])->name('meus_premios');

    Route::get('/chat/{user}', [ChatController::class, 'private_chat'])->name('chat.chat_privado');
    Route::get('/chat/buscar/{userId}', [ChatController::class, 'get_messages'])->name('chat.buscar');
    Route::post('/chat', [ChatController::class, 'store'])->name('chat.enviar');

    Route::get('/adivinhe-o-milhao/iniciar', [AdivinheOMilhaoController::class, 'iniciar'])->name('adivinhe_o_milhao.iniciar');
    Route::get('/adivinhe-o-milhao/pergunta', [AdivinheOMilhaoController::class, 'pergunta'])->name('adivinhe_o_milhao.pergunta');
    Route::post('/adivinhe-o-milhao/responder', [AdivinheOMilhaoController::class, 'responder'])->name('adivinhe_o_milhao.responder');
    Route::get('/adivinhe-o-milhao/voce-ganhou', [AdivinheOMilhaoController::class, 'voce_ganhou'])->name('adivinhe_o_milhao.voce_ganhou');

    Route::get('/adivinhe-o-milhao/errou', [AdivinheOMilhaoController::class, 'errou'])->name('adivinhe_o_milhao.errou');

    Route::get('/users/follow/{user}', [UsersController::class, 'follow'])->name('users.follow');
    Route::get('/users/unfollow/{user}', [UsersController::class, 'unfollow'])->name('users.unfollow');

    Route::get('/users/friend-requests', [UsersController::class, 'friendRequests'])->name('users.friend_requests');
    Route::post('/users/friend-request/{user}', [UsersController::class, 'sendFriendRequest'])->name('users.friend_request');
    Route::post('/users/friend-request/accept/{userId}', [UsersController::class, 'acceptFriendRequest'])->name('users.friend_request.accept')->whereNumber('userId');
    Route::post('/users/friend-request/recuse/{userId}', [UsersController::class, 'recuseFriendRequest'])->name('users.friend_request.recuse')->whereNumber('userId');

    Route::post('posts/', [PostController::class, 'store'])->name('posts.store');
    Route::get('posts/individual{post}', [PostController::class, 'single_post'])->name('posts.single');
    Route::post('posts/comment/{post}', [PostController::class, 'comment'])->name('posts.comment');
    Route::get('posts/comments/{post}', [PostController::class, 'comments'])->name('posts.comments');
    Route::post('/posts/{post}/toggle-like', [PostController::class, 'toggleLike'])->name('posts.toggle-like');

    Route::get('/notificacoes', [UsersController::class, 'getUnreadNotifications'])->name('user.notificacoes');

    Route::get('/competitivo/partida/{partida}', [CompetitivoController::class, 'partida'])->name('competitivo.partida');
    Route::get('/competitivo/partida/{partida}/pergunta', [CompetitivoController::class, 'buscar_pergunta'])->name('competitivo.pergunta');

    Route::post('/competitivo/partida/{partida}/{pergunta}/responder', [CompetitivoController::class, 'responder'])->name('competitivo.responder');
    Route::post('/competitivo/iniciar-busca', [CompetitivoController::class, 'iniciarBusca'])->name('competitivo.iniciar_busca');
    Route::post('/competitivo/cancelar-busca', [CompetitivoController::class, 'sairFila'])->name('competitivo.cancelar_busca');
    Route::get('/competitivo/partida/finalizada/{partida}', [CompetitivoController::class, 'partida'])->name('competitivo.partida.finalizada');


    #rotas apenas para administreadores
    Route::middleware(['isAdmin'])->group(function () {
        Route::post('users/ban-player/{user}', [UsersController::class, 'banUser'])->name('user.ban');
        Route::get('/adivinhacoes/create', [AdivinhacoesController::class, 'create'])->name('adivinhacoes.create');
        Route::post('/adivinhacoes/create', [AdivinhacoesController::class, 'store'])->name('adivinhacoes.store');
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::put('/adivinhacoes/{adivinhacaoId}', [AdivinhacoesController::class, 'update'])->name('adivinhacoes.update')->whereNumber('adivinhacaoId');
        Route::get('/adivinhacoes/view/{adivinhacao}', [AdivinhacoesController::class, 'edit'])->name('adivinhacoes.view');
        Route::get('/adivinhe-o-milhao/create-pergunta', [AdivinheOMilhaoController::class, 'create'])->name('adivinhe_o_milhao.create_pergunta');
        Route::post('/adivinhe-o-milhao/create-pergunta', [AdivinheOMilhaoController::class, 'store'])->name('adivinhe_o_milhao.store_pergunta');
        Route::get('/competitivo/nova-pergunta', [CompetitivoController::class, 'create_pergunta'])->name('competitivo.nova_pergunta');
        Route::post('/competitivo/nova-pergunta', [CompetitivoController::class, 'store_pergunta'])->name('competitivo.store_pergunta');
        Route::get('/adivinhacoes-expiradas', [HomeController::class, 'expiradas'])->name('adivinhacoes.expiradas');

        #deleções
        Route::delete('/adivinhacoes/deletar/{adivinhacao}', [AdivinhacoesController::class, 'deletar'])->name('adivinhacoes.delete');
        Route::delete('/premiacoes/deletar/{premiacao}', [AdivinhacoesController::class, 'deletarPremiacao'])->name('premiacoes.delete');
    });
});

require __DIR__ . '/socket.php';
require __DIR__ . '/auth.php';
