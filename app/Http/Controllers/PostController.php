<?php

namespace App\Http\Controllers;

use App\Events\NewCommentEvent;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\PostRequest;
use App\Http\Resources\GetCommentsResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class PostController extends Controller
{
    public function __construct(private Post $posts) {}

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
        $post->comments()->create(['user_id' => auth()->user()->id, 'body' => $request->input('body')]);
        broadcast(new NewCommentEvent(
            auth()->user()->image,
            auth()->user()->username,
            $post->id,
            $request->input('body'),
            true
        ));
    }
}
