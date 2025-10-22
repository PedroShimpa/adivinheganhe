<?php

namespace App\Http\Controllers\Api;

use App\Events\NewCommentEvent;
use App\Events\NotificacaoEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePostRequest;
use App\Http\Resources\GetCommentsResource;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewCommnetNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class PostController extends Controller
{
    public function __construct(private Post $posts) {}

    public function getPostsByUser(User $user)
    {
        if ($user->perfil_privado == 'S' && auth()->id() != $user->id && !auth()->user()->friends()->contains(fn($f) => $f->id === $user->id)) {
            return response()->json(['error' => 'Este perfil é privado'], 403);
        }

        $posts = $user->posts()->withCount('likes')->get();

        return response()->json(['posts' => $posts]);
    }

    public function single_post(Post $post)
    {
        $post = $post->withCount('likes');
        return response()->json(['post' => $post]);
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

            $post = $this->posts->create($data);

            return response()->json(['success' => true, 'post' => $post]);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['error' => 'Erro ao criar a publicação. Tente novamente.']);
        }
    }

    public function comments(Post $post)
    {
        return response()->json(GetCommentsResource::collection($post->comments));
    }

    public function comment(Request $request, Post $post)
    {
        $body = strip_tags($request->input('body'));
        $comment = $post->comments()->create(['user_id' => auth()->user()->id, 'body' => $body]);
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
        return response()->json(['comment' => $comment]);
    }

    public function toggleLike(Request $request, Post $post)
    {
        $user = auth()->user();

        $like = $post->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
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
