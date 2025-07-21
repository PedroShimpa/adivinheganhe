<?php

namespace App\Http\Controllers;

use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinhacoesRespostas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $limitExceded = true;
        if (Auth::check()) {
            $limitExceded = AdivinhacoesRespostas::where('user_id', auth()->user()->id)->whereDate('created_at', today())->count() >= env('MAX_ADIVINHATIONS', 10);
        }
        $adivinhacoes = Adivinhacoes::where('resolvida', 'N')
        ->orderBy('id', 'desc')
        ->get([
            'id',
            'titulo',
            'imagem',
            'descricao',
            'premio'
        ]);

        $adivinhacoes->filter(function ($a) {
            $a->count_respostas = AdivinhacoesRespostas::where('adivinhacao_id', $a->id)->count();
        });

        $premios = AdivinhacoesPremiacoes::select('adivinhacoes.uuid', 'adivinhacoes.titulo', 'adivinhacoes.premio', 'users.username', 'premio_enviado')
            ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_premiacoes.adivinhacao_id')
            ->join('users', 'users.id', '=', 'adivinhacoes_premiacoes.user_id')
            ->orderby('adivinhacoes_premiacoes.id', 'desc')
            ->get();

        return view('home')->with(compact('adivinhacoes', 'limitExceded', 'premios'));
    }
}
