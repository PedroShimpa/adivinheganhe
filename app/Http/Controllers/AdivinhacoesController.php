<?php

namespace App\Http\Controllers;

use App\Models\Adivinhacoes;
use App\Http\Requests\StoreAdivinhacoesRequest;
use App\Http\Requests\UpdateAdivinhacoesRequest;
use App\Models\AdivinhacoesRespostas;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdivinhacoesController extends Controller
{/
    public function create()
    {
        if (auth()->user()->id == 1) {
            return view('adivinhacoes.create');
        }
        return redirect()->route('home');
    }

    public function store(StoreAdivinhacoesRequest $request)
    {
        if (auth()->user()->id == 1) {
            $imagem = $request->file('imagem');
            $hash = Str::random(10); 
            $fileName = $hash . '_' . time() . '.' . $imagem->getClientOriginalExtension(); 
            $path = $imagem->storeAs('imagens_adivinhacoes', $fileName, 'public');
            $data = $request->validated();
            $data['imagem'] = $path;
            $data['descricao'] = $request->input('descricao');
            Adivinhacoes::create($data);
            return redirect()->route('home');
        }
        return redirect()->route('home');
    }

    public function respostas(Request $request, Adivinhacoes $adivinhacao)
    {
        if ($adivinhacao->resolvida == 'S') {
            $respostas = AdivinhacoesRespostas::select('adivinhacoes_respostas.uuid', 'users.username', 'adivinhacoes_respostas.created_at', 'resposta')
                ->join('users', 'users.id', '=', 'adivinhacoes_respostas.user_id')
                ->where('adivinhacao_id', $adivinhacao->id)
                ->orderBy('adivinhacoes_respostas.created_at', 'desc') 
                ->paginate(10);

            $respostas->getCollection()->transform(function ($r) {
                $r->created_at_br = (new DateTime($r->created_at))->format('d/m/Y H:i:s');
                return $r;
            });
            return view('respostas', compact('respostas', 'adivinhacao'));
        }
    }
}
