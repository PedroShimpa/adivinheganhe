<?php

namespace App\Http\Controllers;

use App\Events\AlertaGlobal;
use App\Events\NewCommentEvent;
use App\Models\Adivinhacoes;
use App\Http\Requests\StoreAdivinhacoesRequest;
use App\Models\AdivinhacoesRespostas;
use App\Http\Controllers\Traits\AdivinhacaoTrait;
use App\Http\Requests\UpdateAdivinhacoesRequest;
use App\Http\Resources\GetCommentsResource;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\Regioes;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class AdivinhacoesController extends Controller
{
    use AdivinhacaoTrait;

    public function index(Adivinhacoes $adivinhacao)
    {

        $adivinhacao->loadCount('likes');

        if (auth()->check()) {
            $adivinhacao->load(['likes' => function ($q) {
                $q->where('user_id', auth()->id());
            }]);
        }

        $this->customize($adivinhacao);

        $respostas = collect([]);
        if (($adivinhacao->resolvida == 'S' || (!empty($adivinhacao->expire_at) && $adivinhacao->expired) || (Auth::check() && auth()->user()->isAdmin()))) {
            $respostas = AdivinhacoesRespostas::select('adivinhacoes_respostas.uuid', 'users.username', 'adivinhacoes_respostas.created_at', 'resposta')
                ->join('users', 'users.id', '=', 'adivinhacoes_respostas.user_id')
                ->where('adivinhacao_id', $adivinhacao->id)
                ->orderBy('adivinhacoes_respostas.created_at', 'desc')
                ->paginate(10);

            $respostas->getCollection()->transform(function ($r) {
                $r->created_at_br = (new DateTime($r->created_at))->format('d/m/Y H:i:s');
                return $r;
            });
        }

        $title = $adivinhacao->titulo . ' - ' . env('APP_NAME', 'Adivinhe e Ganhe');
        return view('adivinhacoes.index', compact('adivinhacao', 'respostas', 'title'));
    }

    public function create()
    {
        $regioes = Regioes::all();
        return view('adivinhacoes.create')->with(compact('regioes'));
    }

    public function edit(Adivinhacoes $adivinhacao)
    {
        $regioes = Regioes::all();

        return view('adivinhacoes.view')->with(compact('adivinhacao', 'regioes'));
    }

    public function update(UpdateAdivinhacoesRequest $request, $adivinhacaoId)
    {
        $data = $request->validated();

        $imagem = $request->file('imagem');
        $hash = Str::random(10);
        $fileName = $hash . '_' . time() . '.webp';
        if (!empty($imagem) && !$imagem->getClientOriginalExtension() != 'webp') {

            $image = Image::read($imagem)->encodeByExtension('webp', 85);

            $filePath = 'imagens_adivinhacoes/' . $fileName;

            Storage::disk('s3')->put($filePath, (string) $image);

            $urlImagem = Storage::disk('s3')->url($filePath);

            $data['imagem'] = $urlImagem;

            $data['descricao'] = $request->input('descricao');
        }

        $data['descricao'] = $request->input('descricao');

        if (!empty($data['expire_at'])) {
            $data['expire_at'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $data['expire_at'])->format('Y-m-d H:i:s');
        }
        Adivinhacoes::where('id', $adivinhacaoId)->update($data);

        if ($request->input('enviar_alerta_global') == 'S') {
            broadcast(new AlertaGlobal('Adivinhação Editada', $data['titulo'] . ' foi atualizada, acesse a pagina inicial para ver'))->toOthers();
        }

        return redirect()->route('adivinhacoes.index', Adivinhacoes::find($adivinhacaoId)->uuid);
    }

    public function store(StoreAdivinhacoesRequest $request)
    {
        $data = $request->validated();

        $imagem = $request->file('imagem');

        $ext = strtolower($imagem->getClientOriginalExtension());
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {

            $hash = Str::random(10);
            $fileName = $hash . '_' . time() . '.webp';

            $image = Image::read($imagem)->encodeByExtension('webp', 85);
            $filePath = 'imagens_adivinhacoes/' . $fileName;
            Storage::disk('s3')->put($filePath, (string) $image);
            $urlImagem = Storage::disk('s3')->url($filePath);
        } else {
            $hash = Str::random(10);
            $fileName = $hash . '_' . time() . '.' . $ext;
            $filePath = 'imagens_adivinhacoes/' . $fileName;

            Storage::disk('s3')->putFileAs('imagens_adivinhacoes', $imagem, $fileName);
            $urlImagem = Storage::disk('s3')->url($filePath);
        }

        $data['imagem'] = $urlImagem;
        $data['descricao'] = $request->input('descricao');

        if (!empty($data['expire_at'])) {
            $data['expire_at'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $data['expire_at'])->format('Y-m-d H:i:s');
        }

        if (!empty($data['liberado_at'])) {
            $data['liberado_at'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $data['liberado_at'])->format('Y-m-d H:i:s');
        }

        $adivinhacao = Adivinhacoes::create($data);

        return redirect()->route('adivinhacoes.index', $adivinhacao->uuid);
    }

    public function findUserReply(Request $request)
    {
        $respostas = AdivinhacoesRespostas::select('resposta')->where('adivinhacao_id', $request->adivinhacao_id)->where('user_id', auth()->user()->id)->get();
        return response()->json($respostas);
    }

    public function comments(Adivinhacoes $adivinhacao)
    {
        $limit = request('limit', 5);
        $offset = request('offset', 0);

        $comments = $adivinhacao->comments()->orderBy('created_at', 'desc')->skip($offset)->take($limit)->get();

        $totalComments = $adivinhacao->comments()->count();

        return response()->json([
            'comments' => GetCommentsResource::collection($comments),
            'has_more' => $totalComments > ($offset + $limit),
            'total' => $totalComments
        ]);
    }

    public function comment(Request $request, Adivinhacoes $adivinhacao)
    {
        if (auth()->user()->banned) {
            return response()->json(['error' => 'Você foi banido e não pode realizar esta ação.'], 403);
        }
        $body = strip_tags($request->input('body'));
        $adivinhacao->comments()->create(['user_id' => auth()->user()->id, 'body' => $body]);
        broadcast(new NewCommentEvent(
            auth()->user()->image,
            auth()->user()->username,
            $adivinhacao->id,
            $body
        ));
    }

    public function toggleLike(Request $request, Adivinhacoes $adivinhacao)
    {
        if (auth()->user()->banned) {
            return response()->json(['error' => 'Você foi banido e não pode realizar esta ação.'], 403);
        }
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

    public function deletar(Adivinhacoes $adivinhacao)
    {
        $adivinhacao->delete();
        return redirect()->back();
    }

    public function deletarPremiacao(AdivinhacoesPremiacoes $premiacao)
    {
        $premiacao->delete();
        return redirect()->back();
    }

    public function marcarComoPago(Request $request, AdivinhacoesPremiacoes $premiacao)
    {
        $request->validate([
            'comprovante' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = ['premio_enviado' => 'S'];

        if ($request->hasFile('comprovante')) {
            $file = $request->file('comprovante');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('comprovantes_pagamento', $filename, 's3');
            $data['comprovante_pagamento'] = $path;
        }

        $premiacao->update($data);
        return redirect()->back();
    }
}
