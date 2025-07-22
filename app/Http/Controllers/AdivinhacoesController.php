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
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Show the form for creating a new resource.
     */
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

            // Obtém o arquivo da imagem
            $imagem = $request->file('imagem');

            // Gera um nome único para a imagem com hash (usando uniqid, Str::random, ou qualquer outro método)
            $hash = Str::random(10); // Gera uma string aleatória de 10 caracteres
            $fileName = $hash . '_' . time() . '.' . $imagem->getClientOriginalExtension(); // Combina o hash com o timestamp para garantir unicidade

            // Salva a imagem no diretório desejado, com o novo nome
            $path = $imagem->storeAs('imagens_adivinhacoes', $fileName, 'public');

            // Cria a entrada no banco de dados
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
                ->orderBy('adivinhacoes_respostas.created_at', 'desc') // Ordenar mais recentes primeiro
                ->paginate(10);

            // Formatar as datas para exibir no Blade
            $respostas->getCollection()->transform(function ($r) {
                $r->created_at_br = (new DateTime($r->created_at))->format('d/m/Y H:i:s');
                return $r;
            });

            if ($request->ajax()) {
                return view('partials.respostas_table_rows', compact('respostas'))->render();
            }

            return view('respostas', compact('respostas', 'adivinhacao'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Adivinhacoes $adivinhacoes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Adivinhacoes $adivinhacoes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdivinhacoesRequest $request, Adivinhacoes $adivinhacoes)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Adivinhacoes $adivinhacoes)
    {
        //
    }
}
