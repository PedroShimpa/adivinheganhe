<?php

namespace App\Http\Controllers;

use App\Models\AdicionaisIndicacao;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinhacoesRespostas;
use App\Models\DicasCompras;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $trys = 0;
        $limitExceded = true;
        if (Auth::check()) {
            $countTrysToday = AdivinhacoesRespostas::where('user_id', auth()->user()->id)->whereDate('created_at', today())->count();
            $countFromIndications = AdicionaisIndicacao::where('user_uuid', auth()->user()->uuid)->value('value') ?? 0;
            $limitExceded = $countTrysToday >= (env('MAX_ADIVINHATIONS', 10) + $countFromIndications);
            $trys = (env('MAX_ADIVINHATIONS', 10) + $countFromIndications) - $countTrysToday;
        }
        $adivinhacoes = Adivinhacoes::select(
            'id',
            'uuid',
            'titulo',
            'imagem',
            'descricao',
            'premio',
            'dica',
            'dica_paga',
            'dica_valor',

        )
            ->where('resolvida', 'N')
            ->orderBy('id', 'desc')
            ->get();

        $adivinhacoes->filter(function ($a) {
            $a->count_respostas = AdivinhacoesRespostas::where('adivinhacao_id', $a->id)->count();
            if (!empty($a->dica) && $a->dica_paga == 'S') {
                $a->buyed = DicasCompras::where('user_id', auth()->id)->where('adivinhacao_id', $a->id)->exists();
               
            }
        });

        $premios = AdivinhacoesPremiacoes::select('adivinhacoes.uuid', 'adivinhacoes.titulo', 'adivinhacoes.resposta', 'adivinhacoes.premio', 'users.username', 'premio_enviado')
            ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_premiacoes.adivinhacao_id')
            ->join('users', 'users.id', '=', 'adivinhacoes_premiacoes.user_id')
            ->orderby('adivinhacoes_premiacoes.id', 'desc')
            ->get();

        return view('home')->with(compact('adivinhacoes', 'limitExceded', 'premios', 'trys'));
    }
}
