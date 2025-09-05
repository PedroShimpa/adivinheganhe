<?php

use App\Http\Controllers\Api\AdivinhacoesController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CompetitivoController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\RespostaController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::middleware(['auth:sanctum', 'banned'])->group(function () {
    #auth
    Route::get('/me', [UsersController::class, 'me']);

    #adinhacoes
    Route::get('/adivinhacoes/index', [AdivinhacoesController::class, 'getAtivas']);
    Route::post('/adivinhacao/responder', [RespostaController::class, 'enviar']);
    Route::get('/adivinhacao/{adivinhacao}/minhas-respostas', [AdivinhacoesController::class, 'findUserReply']);
    Route::get('/adivinhacao/{adivinhacao}/comentarios', [AdivinhacoesController::class, 'comments']);
    Route::post('/adivinhacao/{adivinhacao}/comentar', [AdivinhacoesController::class, 'comment']);
    Route::post('/adivinhacao/{adivinhacao}/toogle-like', [AdivinhacoesController::class, 'toggleLike']);
    Route::get('/premiacoes', [AdivinhacoesController::class, 'premiacoes']);
    Route::get('/ranking-classico', [AdivinhacoesController::class, 'rankingClassico']);

    #gestao de usuario / comunidade
    Route::get('/jogadores/index', [UsersController::class, 'jogadores']);
    Route::get('/para_voce', [UsersController::class, 'para_voce']);
    Route::get('/user/{user}', [UsersController::class, 'getProfile']);
    Route::post('/usuario/update', [UsersController::class, 'update']);
    Route::get('/meus-amigos', [UsersController::class, 'meusAmigos']);
    Route::post('/user/{user}/enviar-pedido-de-amizade', [UsersController::class, 'sendFriendRequest']);
    Route::get('/pedidos-de-amizade', [UsersController::class, 'friendRequests']);
    Route::post('/aceitar-pedido-de-amizade/{user}', [UsersController::class, 'acceptFriendRequest']);
    Route::post('/recusar-pedido-de-amizade/{user}', [UsersController::class, 'recuseFriendRequest']);
    Route::get('/notificacoes', [UsersController::class, 'getUnreadNotifications']);

    #chat
    Route::get('chat/messages/{userId}', [ChatController::class, 'get_messages']);
    Route::post('chat/new-message', [ChatController::class, 'store']);

    #posts
    Route::get('posts/by-user/{user}', [PostController::class, 'getPostsByUser']);
    Route::post('posts/store', [PostController::class, 'store']);
    Route::get('posts/individual{post}', [PostController::class, 'single_post']);
    Route::post('posts/comment/{post}', [PostController::class, 'comment']);
    Route::get('posts/comments/{post}', [PostController::class, 'comments']);
    Route::post('/posts/{post}/toggle-like', [PostController::class, 'toggleLike']);
    Route::delete('/posts/delete/{post}', [PostController::class, 'deletar']);

    #rotas faltantes api

    Route::post('/competitivo/iniciar-busca', [CompetitivoController::class, 'iniciarBusca']);
    Route::post('/competitivo/cancelar-busca', [CompetitivoController::class, 'sairFila']);
    Route::post('/competitivo/partida/{partida}/{pergunta}/responder', [CompetitivoController::class, 'responder']);
    Route::get('/competitivo/partida/finalizada/{partida}', [CompetitivoController::class, 'partida']);
});
