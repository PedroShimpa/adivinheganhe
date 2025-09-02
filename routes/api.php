<?php

use App\Http\Controllers\Api\AdivinhacoesController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompetitivoController;
use App\Http\Controllers\Api\GoogleController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\RespostaController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);


Route::middleware(['auth:sanctum', 'banned'])->group(function () {
    #adinhacoes
    Route::get('/adivinhacoes/index', [AdivinhacoesController::class, 'getAtivas']);
    Route::post('/adivinhacao/responder', [RespostaController::class, 'enviar']);
    Route::get('/adivinhacao/{adivinhacao}/minhas-respostas', [AdivinhacoesController::class, 'findUserReply']);
    Route::get('/adivinhacao/{adivinhacao}/comentarios', [AdivinhacoesController::class, 'comments']);
    Route::post('/adivinhacao/{adivinhacao}/comentar', [AdivinhacoesController::class, 'comment']);
    Route::post('/adivinhacao/{adivinhacao}/toogle-like', [AdivinhacoesController::class, 'toggleLike']);
    Route::get('/premiacoes', [AdivinhacoesController::class, 'premiacoes']);

    #modo competitivo

    #gestao de usuario / comunidade
    Route::get('/jogadores/index', [UsersController::class, 'jogadores']);
    Route::get('/meu-amigo/{userId}', [UsersController::class, 'verificarAmigo']);
    Route::get('/para_voce', [UsersController::class, 'para_voce']);
    Route::get('/user/{user}', [UsersController::class, 'view']);
    Route::get('/usuario/update', [UsersController::class, 'update']);
    Route::post('/meus-amigos', [UsersController::class, 'meusAmigos']);
    Route::post('/user/{user}/enviar-pedido-de-amizade', [UsersController::class, 'sendFriendRequest']);
    Route::get('/pedidos-de-amizade', [UsersController::class, 'friendRequests']);
    Route::post('/aceitar-pedido-de-amizade/{user}', [UsersController::class, 'acceptFriendRequest']);
    Route::post('/recusar-pedido-de-amizade/{user}', [UsersController::class, 'recuseFriendRequest']);
    Route::post('/notificacoes', [UsersController::class, 'getUnreadNotifications']);

    #posts
    Route::get('posts/by-user/{user}', [PostController::class, 'getPostsByUser']);
    Route::post('posts/store', [PostController::class, 'store']);
    Route::get('posts/individual{post}', [PostController::class, 'single_post']);
    Route::post('posts/comment/{post}', [PostController::class, 'comment']);
    Route::get('posts/comments/{post}', [PostController::class, 'comments']);
    Route::post('/posts/{post}/toggle-like', [PostController::class, 'toggleLike']);
    Route::delete('/posts/delete/{post}', [PostController::class, 'deletar']);

    #rotas faltantes api
    Route::get('/ranking-classico', [CompetitivoController::class, 'rankingClassico']);

    ###compras e suporte vai abrir o site
    ###sobre vai abrir o site

    Route::post('/competitivo/iniciar-busca', [CompetitivoController::class, 'iniciarBusca']);
    Route::post('/competitivo/cancelar-busca', [CompetitivoController::class, 'sairFila']);
    Route::post('/competitivo/partida/{partida}/{pergunta}/responder', [CompetitivoController::class, 'responder']);
    Route::get('/competitivo/partida/finalizada/{partida}', [CompetitivoController::class, 'partida']);
});
