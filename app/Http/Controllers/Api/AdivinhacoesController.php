<?php

namespace App\Http\Controllers\Api;

use App\Events\NewCommentEvent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\AdivinhacaoTrait;
use App\Http\Resources\GetCommentsResource;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinhacoesRespostas;
use Illuminate\Http\Request;

class AdivinhacoesController extends Controller
{
    use AdivinhacaoTrait;

    public function __construct(protected Adivinhacoes $adivinhacoes, protected AdivinhacoesPremiacoes $adivinhacoesPremiacoes) {}

    public function premiacoes()
    {
        return response()->json(['premiacoes' => $this->adivinhacoesPremiacoes->getPremiosGanhos()]);
    }

    public function getAtivas()
    {
        $adivinhacoes = $this->adivinhacoes->getAtivas()->filter(function ($a) {
            return is_null($a->expire_at) || $a->expire_at > now();
        })->values();

        $adivinhacoes->each(function ($a) {
            $this->customize($a);
        });
        return response()->json($adivinhacoes);
    }

    public function findUserReply(Request $request)
    {
        $respostas = AdivinhacoesRespostas::select('resposta')->where('adivinhacao_id', $request->adivinhacao_id)->where('user_id', auth()->user()->id)->get();
        return response()->json($respostas);
    }

    public function comments(Adivinhacoes $adivinhacao)
    {
        return response()->json(GetCommentsResource::collection($adivinhacao->comments));
    }

    public function comment(Request $request, Adivinhacoes $adivinhacao)
    {
        $adivinhacao->comments()->create(['user_id' => auth()->user()->id, 'body' => $request->input('body')]);
        broadcast(new NewCommentEvent(
            auth()->user()->image,
            auth()->user()->username,
            $adivinhacao->id,
            $request->input('body')
        ));
    }

    public function toggleLike(Adivinhacoes $adivinhacao)
    {
        $user = auth()->user();

        $like = $adivinhacao->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            $adivinhacao->likes()->create([
                'user_id' => $user->id
            ]);
            $liked = true;
        }
        return response()->json([
            'liked' => $liked,
            'likes_count' => $adivinhacao->likes()->count()
        ]);
    }

    public function rankingClassico(Request $request)
    {
        return response()->json(['ranking' => $this->adivinhacoesPremiacoes->getUsuariosMaisPremiados()]);
    }
}
