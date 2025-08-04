<?php

namespace App\Http\Controllers;

use App\Events\MensagemEnviada;
use App\Models\ChatMessages;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function store(Request $request)
    {
        try {

            $request->validate([
                'message' => 'required|string'
            ]);

            $data = [
                'user_id' => auth()->user()->id,
                'message' => $request->input('message')
            ];

            event(new MensagemEnviada(auth()->user()->name, $request->input('message')));
            ChatMessages::create($data);
        } catch (Exception $e) {

            Log::error('Erro ao adicionar mensagem no chat:' . $e->getMessage());
        }
    }
}
