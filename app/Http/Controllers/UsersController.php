<?php

namespace App\Http\Controllers;

use App\Events\NotificacaoEvent;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Friendship;
use App\Models\User;
use App\Notifications\FriendRequestAcceptedNotification;
use App\Notifications\FriendRequestNotification;
use App\Notifications\NewFollowerNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class UsersController extends Controller
{
    public function jogadores()
    {
        $players = User::select('username', 'image', 'bio')->where('perfil_privado', 'N')->paginate();

        return view('jogadores')->with('players', $players);
    }

    public function para_voce(Request $request)
    {
        $posts = auth()->user()->feedPosts();

        if ($request->ajax()) {
            $html = view('partials.posts', compact('posts'))->render();
            return response()->json([
                'html' => $html,
                'next_page_url' => $posts->nextPageUrl(),
            ]);
        }
        return view('para_voce')->with(compact('posts'));
    }

    public function view(User $user)
    {
        if ($user->perfil_privado == 'N') {
            return view('user.profile', compact('user'));
        } else {
            if ($user->id == auth()->user()->id) {
                return view('user.profile', compact('user'));
            } else {
                redirect()->route('home');
            }
        }
    }

    public function edit(Request $request): View
    {
        return view('user.edit_profile', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->file('image')) {
            $imagem = $request->file('image');
            $hash = Str::random(10);
            $fileName = $hash . '_' . time() . '.webp';

            $image = Image::read($imagem)->encodeByExtension('webp', 85);

            $filePath = 'usuarios/imagens/' . $fileName;

            Storage::disk('s3')->put($filePath, (string) $image);

            $urlImagem = Storage::disk('s3')->url($filePath);

            $request->user()->image = $urlImagem;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function follow(User $user)
    {
        $user->followers()->create(['user_id' => auth()->user()->id]);
        $user->notify(new NewFollowerNotification());
        broadcast(new NotificacaoEvent($user->id, auth()->user()->name . ' agora está te seguindo.'));
        return redirect()->back();
    }

    public function unfollow(User $user)
    {
        $user->followers()->where(['user_id' => auth()->user()->id])->delete();
        return redirect()->back();
    }

    public function sendFriendRequest(User $user)
    {
        $request = Friendship::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
        ]);

        $user->notify(new FriendRequestNotification());
        broadcast(new NotificacaoEvent($user->id, auth()->user()->name . ' enviou um pedido de amizade.'));
        return $request;
    }

    public function getUnreadNotifications()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $notifications =  auth()->user()->notifications;
        return $notifications;
    }

    public function acceptFriendRequest($userId)
    {
        $friendship = Friendship::where('sender_id', $userId)
            ->where('receiver_id', auth()->id())
            ->first();

        $accept = $friendship->update(['status' => 'accepted']);
        User::find($userId)->notify(new FriendRequestAcceptedNotification());
        broadcast(new NotificacaoEvent($userId, auth()->user()->name . ' aceitou seu pedido de amizade.'));

        return $accept;
    }

    public function recuseFriendRequest($userId)
    {
        return Friendship::where('sender_id', $userId)
            ->where('receiver_id', auth()->id())
            ->delete();
    }

    public function meusAmigos()
    {
        $friends = auth()->user()->friends();

        return view('amigos')->with(compact('friends'));
    }
}
