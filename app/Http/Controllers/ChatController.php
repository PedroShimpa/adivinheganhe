<?php

namespace App\Http\Controllers;

use App\Events\MensagemEnviada;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        return response()->json(Cache::get('chat_messages', []));
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

            $messageData = [
                'user' => auth()->user()->username,
                'message' => $request->input('message'),
                'created_at' => now(),
            ];

            event(new MensagemEnviada($messageData['user'], $messageData['message']));

            $cachedMessages = Cache::get('chat_messages', []);
            $cachedMessages = array_slice($cachedMessages, -199);
            $cachedMessages[] = $messageData;

            Cache::put('chat_messages', $cachedMessages, now()->addHour());

            DB::table('chat_messages')->insert([
                'user_id' => auth()->user()->id,
                'message' => $messageData['message'],
                'created_at' => now(),
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao adicionar mensagem no chat: ' . $e->getMessage());
        }
    }
}
