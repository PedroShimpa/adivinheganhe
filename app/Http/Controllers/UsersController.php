<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
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
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('user.edit_profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
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

    /**
     * Delete the user's account.
     */
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
        return redirect()->back();
    }

    public function unfollow(User $user)
    {
        $user->followers()->where(['user_id' => auth()->user()->id])->delete();
        return redirect()->back();
    }
}
