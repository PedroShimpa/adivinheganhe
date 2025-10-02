<?php

namespace App\Http\Controllers\Api;

use App\Events\NotificacaoEvent;
use App\Http\Requests\ProfileUpdateRequest;
use App\Jobs\AddProfileVisitJob;
use App\Mail\FriendrequestMail;
use App\Models\Friendship;
use App\Models\User;
use App\Notifications\FriendRequestAcceptedNotification;
use App\Notifications\FriendRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use App\Http\Controllers\Controller;
use App\Models\AdivinhacoesPremiacoes;

class UsersController extends Controller
{
    public function me()
    {
        $me = auth()->user();
        $me->unread_notifications = $me->unreadNotificationsCount();
        return response()->json(['user' => $me]);
    }

    public function meusPremios()
    {
        $meusPremios = (new AdivinhacoesPremiacoes())->getMeusPremios();
        return response()->json(['meus_premios' => $meusPremios]);
    }

    public function jogadores(Request $request)
    {
        if ($request->input('search')) {
            $players = User::search($request->input('search'))->where('perfil_privado', 'N')->get();
        } else {
            $players = User::select('username', 'image as user_photo', 'bio')->where('perfil_privado', 'N')->where('banned', false)->inRandomOrder()->limit(9)->get();
        }

        return response()->json(['players' => $players]);
    }

    public function para_voce(Request $request)
    {
        $posts = auth()->user()->feedPosts();
        return view('para_voce')->with(['posts' => $posts]);
    }

    public function getProfile(User $user)
    {
        if (auth()->check() && auth()->user()->id != $user->id) {
            dispatch(new AddProfileVisitJob(auth()->user()->id, auth()->user()->username, $user->id, $user->email));
        }

        if ($user->id != auth()->user()->id) {

            $user->isFriend = auth()->user()->friends()->contains(fn($f) => $f->id === $user->id);
            if (!$user->isFriend) {

                $user->friendRequested = auth()->user()->sentFriendships()
                    ->where('receiver_id', $user->id)
                    ->where('status', 'pending')
                    ->exists();
            }
            if ($user->perfil_privado == 'S') {
                if ($user->isFriend) {
                    $user->load('posts');
                }
            } else {
                $user->load('posts');
            }
        } else {
            $user->load('posts');
        }

        unset($user->name, $user->password, $user->email, $user->cpf, $user->whatsapp, $user->indicated_by, $user->fingerprint, $user->token_push_notification);


        return response()->json(['user' => $user]);
    }

    public function update(ProfileUpdateRequest $request)
    {
        $request->user()->fill($request->validated());

        if ($request->file('image')) {
            $imagem = $request->file('image');
            $hash = Str::random(10);
            $fileName = $hash . '_' . time() . '.webp';
            $image = Image::read($imagem)->encodeByExtension('webp', 55);
            $filePath = 'usuarios/imagens/' . $fileName;
            Storage::disk('s3')->put($filePath, (string) $image);
            $urlImagem = Storage::disk('s3')->url($filePath);
            $request->user()->image = $urlImagem;
        }

        $request->user()->save();

        return response()->json(['status' => 'Perfil atualizado com sucesso!', 'user' => $request->user()]);
    }

    public function getUnreadNotifications()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $notifications =  auth()->user()->notifications;
        $notifications->filter(function ($q) {
            $q->created_at_br = $q->created_at->format('d/m/Y H:i');
        });
        return response()->json(['notifications' => $notifications]);
    }

    public function friendRequests()
    {
        $user = auth()->user();

        $pendingRequests = $user->receivedFriendships()
            ->where('status', 'pending')
            ->with('sender')
            ->get();

        $pendingRequests->filter(function ($q) {
            $sender = $q->sender;
            $q->username =  '@' . $sender->username;
            $q->user_photo =  $sender->image;
        });

        return response()->json(['peding_requests' => $pendingRequests]);
    }

    public function sendFriendRequest(User $user)
    {
        $friendRequest = Friendship::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
        ]);

        $user->notify(new FriendRequestNotification());
        broadcast(new NotificacaoEvent($user->id, auth()->user()->username . ' enviou um pedido de amizade.'));
        Mail::to($user->email)->queue(new FriendrequestMail(auth()->user()->username, $user->name));
        return response()->json(['friend_request' => $friendRequest]);
    }


    public function acceptFriendRequest($userId)
    {
        $friendship = Friendship::where('sender_id', $userId)
            ->where('receiver_id', auth()->id())
            ->first();

        $accept = $friendship->update(['status' => 'accepted']);
        User::find($userId)->notify(new FriendRequestAcceptedNotification());
        broadcast(new NotificacaoEvent($userId, auth()->user()->username . ' aceitou seu pedido de amizade.'));

        return response()->json(['accept' => $accept]);
    }

    public function recuseFriendRequest($userId)
    {
        return response()->json(['recuse' => Friendship::where('sender_id', $userId)
            ->where('receiver_id', auth()->id())
            ->delete()]);
    }

    public function meusAmigos()
    {
        $friends = auth()->user()->friendsWithUsername();

        return response()->json(['friends' => $friends]);
    }

    public function savePushNotificationToken(Request $request) {
        
        $user = auth()->user();
        $user->token_push_notification = $request->input('token_push_notification');
        $user->save();
    }
}
