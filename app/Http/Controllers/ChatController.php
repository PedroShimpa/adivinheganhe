<?php

namespace App\Http\Controllers;

use App\Events\NotificacaoEvent;
use App\Http\Controllers\Controller;
use App\Models\ChatMessages;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{

    public function private_chat(User $user)
    {
        return view('user.chat_privado')->with('user', $user);
    }

    public function get_messages($userId)
    {
        $messages = ChatMessages::select('user_id', 'receiver_id', 'message as mensagem', 'created_at')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', auth()->user()->id);
                $q->where('receiver_id', $userId);
            })
            ->orWhere(function ($q) use ($userId) {
                $q->where('user_id', $userId);
                $q->where('receiver_id', auth()->user()->id);
            })
            ->orderBy('chat_messages.id', 'asc')
            ->get()
            ->toArray();
        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'receiver_id' => 'required|integer|exists:users,id'
        ]);

        $message = ChatMessages::create([
            'user_id' => auth()->user()->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'created_at' => now(),
        ]);

        event(new \App\Events\ChatMessageSent($message->message, auth()->user()->id, $message->receiver_id));
        broadcast(new NotificacaoEvent($request->receiver_id, auth()->user()->username . ' te enviou uma mensagem.'));
        return response()->json(['status' => 'success', 'message' => $message]);
    }
}
