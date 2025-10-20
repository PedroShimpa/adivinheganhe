<?php

use App\Http\Controllers\Api\AdivinhacoesController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CompetitivoController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\RespostaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pusher\Pusher;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::middleware(['auth:sanctum', 'banned', 'trackOnline'])->group(function () {
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
    Route::get('/meus-premios', [UsersController::class, 'meusPremios']);
    Route::post('/user/{user}/enviar-pedido-de-amizade', [UsersController::class, 'sendFriendRequest']);
    Route::get('/users/friend-requests', [UsersController::class, 'friendRequests']);
    Route::post('/users/friend-request/accept/{user}', [UsersController::class, 'acceptFriendRequest']);
    Route::post('/users/friend-request/recuse/{user}', [UsersController::class, 'recuseFriendRequest']);
    Route::post('/user/save-token', [UsersController::class, 'savePushNotificationToken']);
    Route::get('/notificacoes', [UsersController::class, 'getUnreadNotifications']);

    #chat
    Route::get('chats', [ChatController::class, 'chats']);
    Route::get('chat/messages/{user}', [ChatController::class, 'get_messages']);
    Route::post('chat/new-message', [ChatController::class, 'store']);

    #posts
    Route::get('posts/by-user/{user}', [PostController::class, 'getPostsByUser']);
    Route::post('posts/store', [PostController::class, 'store']);
    Route::get('posts/individual{post}', [PostController::class, 'single_post']);
    Route::post('posts/comment/{post}', [PostController::class, 'comment']);
    Route::get('posts/comments/{post}', [PostController::class, 'comments']);
    Route::post('/posts/{post}/toggle-like', [PostController::class, 'toggleLike']);
    Route::delete('/posts/delete/{post}', [PostController::class, 'deletar']);

    #suporte
    Route::get('/suporte/meus-chamados', [SuporteController::class, 'apiUserIndex']);
    Route::get('/suporte/{suporte}/chat/messages', [SuporteController::class, 'apiGetChatMessages']);
    Route::post('/suporte/{suporte}/chat/store', [SuporteController::class, 'apiStoreChatMessage']);

    #rotas faltantes api

    Route::post('/competitivo/iniciar-busca', [CompetitivoController::class, 'iniciarBusca']);
    Route::post('/competitivo/cancelar-busca', [CompetitivoController::class, 'sairFila']);
    Route::post('/competitivo/partida/{partida}/{pergunta}/responder', [CompetitivoController::class, 'responder']);
    Route::get('/competitivo/partida/finalizada/{partida}', [CompetitivoController::class, 'partida']);
});
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
