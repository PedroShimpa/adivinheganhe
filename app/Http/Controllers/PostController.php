<?php

namespace App\Http\Controllers;

use App\Events\NewCommentEvent;
use App\Events\NotificacaoEvent;
use App\Http\Requests\CreatePostRequest;
use App\Http\Resources\GetCommentsResource;
use App\Models\Post;
use App\Notifications\NewCommnetNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class PostController extends Controller
{
    public function __construct(private Post $posts) {}

    public function single_post(Post $post)
    {
        return view('post.single')->with('post', $post);
    }

    public function store(CreatePostRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        try {
            if ($request->hasFile('file')) {
                $imagem = $request->file('file');
                $hash = Str::random(10);
                $fileName = $hash . '_' . time() . '.webp';

                $image = Image::read($imagem)->encodeByExtension('webp', 85);

                $filePath = "usuarios/posts/{$fileName}";
                Storage::disk('s3')->put($filePath, (string) $image);

                $data['file'] = Storage::disk('s3')->url($filePath);
            }

            $this->posts->create($data);

            return redirect()->back()->with('success', 'Publicação criada com sucesso!');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->withErrors('Erro ao criar a publicação. Tente novamente.');
        }
    }

    public function comments(Post $post)
    {
        return response()->json(GetCommentsResource::collection($post->comments));
    }

    public function comment(Request $request, Post $post)
    {
        if (auth()->user()->banned) {
            return response()->json(['error' => 'Você foi banido e não pode realizar esta ação.'], 403);
        }
        $body = strip_tags($request->input('body'));
        $post->comments()->create(['user_id' => auth()->user()->id, 'body' => $body]);
        broadcast(new NewCommentEvent(
            auth()->user()->image,
            auth()->user()->username,
            $post->id,
            $body,
            true
        ));
        if (auth()->user()->id !=  $post->user_id) {
            $post->user->notify(new NewCommnetNotification($body, $post->id));
            broadcast(new NotificacaoEvent($post->user->id, auth()->user()->name . ' comentou: ' . $body));
        }
    }


    public function toggleLike(Request $request, Post $post)
    {
        if (auth()->user()->banned) {
            return response()->json(['error' => 'Você foi banido e não pode realizar esta ação.'], 403);
        }
        $user = auth()->user();

        $like = $post->likes()->where('user_id', $user->id)->first();

        if ($like) {
            // Se já tiver like, remove
            $like->delete();
            $liked = false;
        } else {
            // Senão, adiciona like
            $post->likes()->create([
                'user_id' => $user->id
            ]);
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => $post->likes()->count()
        ]);
    }

    public function deletar(Post $post)
    {
        if (auth()->user()->isAdmin() || auth()->user()->id == $post->user_id) {
            return response()->json($post->delete());
        }
    }
}
