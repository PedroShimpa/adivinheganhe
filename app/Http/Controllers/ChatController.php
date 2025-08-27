<?php

namespace App\Http\Controllers;

use App\Events\MensagemEnviada;
use App\Http\Controllers\Controller;
use App\Jobs\IncluirMensagemChat;
use App\Models\ChatMessages;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Exception;

class ChatController extends Controller
{

    public function get_messages()
    {
        if (!env('ENABLE_CHAT', true)) {
            return;
        }
        $messages = ChatMessages::select('users.username as usuario', 'message as mensagem')
            ->join('users', 'users.id', '=', 'chat_messages.user_id')
            ->orderBy('chat_messages.id', 'asc')
            ->limit(200)
            ->get()
            ->toArray();
        return response()->json($messages);
    }

    public function store(Request $request)
    {
        if (!env('ENABLE_CHAT', true)) {
            return;
        }

        try {
            $request->validate([
                'message' => 'required|string'
            ]);

            event(new MensagemEnviada(auth()->user()->username, $request->input('message')));

           ChatMessages::create([
                'user_id' => auth()->user()->id,
                'message' => $request->input('message'),
                'created_at' => now(),
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao adicionar mensagem no chat: ' . $e->getMessage());
        }
    }
}
