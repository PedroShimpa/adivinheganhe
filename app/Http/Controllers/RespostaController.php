<?php

namespace App\Http\Controllers;

use App\Events\RespostaAprovada;
use App\Events\RespostaPrivada;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinhacoesRespostas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RespostaController extends Controller
{
    public function enviar(Request $request)
    {
        $limit = env('MAX_ADIVINHATIONS', 10);
        $limitExceded = AdivinhacoesRespostas::where('user_id', auth()->user()->id)->whereDate('created_at', today())->count() >= $limit;
        if ($limitExceded) {
            return response()->json(['error' => "Você atingiu seu limite de $limit respostas de hoje!"]);
        }
        $data = $request->validate([
            'resposta'       => 'required|string',
            'adivinhacao_id' => 'required|exists:adivinhacoes,id',
        ]);

        $respostaCliente = strtolower(trim($data['resposta']));
        $adivinhacao     = Adivinhacoes::findOrFail($data['adivinhacao_id']);

        $resposta = AdivinhacoesRespostas::create([
            'user_id'        => Auth::id(),
            'adivinhacao_id' => $adivinhacao->id,
            'resposta'       => $respostaCliente,
        ]);

        if (strtolower(trim($adivinhacao->resposta)) === $respostaCliente) {
            $username = Auth::user()->username;

            broadcast(new RespostaAprovada($username, $adivinhacao))
                ->toOthers();

            broadcast(new RespostaPrivada(
                "Você acertou, seu código de resposta: {$resposta->uuid}!!!\n Em breve será notificado do envio do prêmio.",
                $adivinhacao->id
            ));

            $adivinhacao->resolvida = 'S';
            $adivinhacao->save();

            AdivinhacoesPremiacoes::create([
                'user_id'        => Auth::id(),
                'adivinhacao_id' => $adivinhacao->id,
            ]);

            return response()->json(['status' => 'acertou']);
        }

        return response()->json(['status' => 'ok']);
    }
}
