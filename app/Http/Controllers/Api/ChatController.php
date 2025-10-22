<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessages;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function chats()
    {
        $authId = auth()->id();


        $chats = DB::table('chat_messages as cm')
            ->selectRaw('
            CASE 
                WHEN cm.user_id = ? THEN cm.receiver_id
                ELSE cm.user_id
            END as other_user_id,
            MAX(cm.id) as last_message_id,
            SUM(CASE WHEN cm.receiver_id = ? AND cm.read_at IS NULL THEN 1 ELSE 0 END) as unread_count
        ', [$authId, $authId])
            ->where(function ($q) use ($authId) {
                $q->where('cm.user_id', $authId)
                    ->orWhere('cm.receiver_id', $authId);
            })
            ->groupBy('other_user_id')
            ->get();

        $result = $chats->map(function ($chat) {
            $lastMessage = \App\Models\ChatMessages::find($chat->last_message_id);

            $user = \App\Models\User::find($chat->other_user_id);

            return [
                'user_id' => $chat->other_user_id,
                'username' => $user?->username,
                'avatar' => $user?->avatar_url ?? null,
                'ultima_mensagem' => $lastMessage?->message,
                'ultima_data' => optional($lastMessage?->created_at)->format('d/m/Y H:i'),
                'nao_lidas' => (int) $chat->unread_count,
            ];
        });

        return response()->json(['chats' => $result]);
    }


    public function get_messages(User $user)
    {
        ChatMessages::where('user_id', $user->id)->where('receiver_id', auth()->user()->id)->update(['read_at' => now()]);
        $messages = ChatMessages::select('chat_messages.user_id', 'chat_messages.receiver_id', 'chat_messages.message as mensagem', 'chat_messages.created_at', 'users.is_admin')
            ->join('users', 'users.id', '=', 'chat_messages.user_id')
            ->where(function ($q) use ($user) {
                $q->where('chat_messages.user_id', auth()->user()->id);
                $q->where('chat_messages.receiver_id', $user->id);
            })
            ->orWhere(function ($q) use ($user) {
                $q->where('chat_messages.user_id', $user->id);
                $q->where('chat_messages.receiver_id', auth()->user()->id);
            })
            ->orderBy('chat_messages.id', 'asc')
            ->get()
            ->toArray();
        return response()->json(['messages' => $messages, 'receiver_id' => $user->id]);
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

        event(new \App\Events\ChatMessageSent($message->message, auth()->user()->id,  auth()->user()->username, $message->receiver_id, $message->created_at, auth()->user()->is_admin));
        User::find($request->receiver_id)->notify(new NewMessageNotification($message->message));
        ChatMessages::where('user_id', $request->receiver_id)->where('receiver_id', auth()->user()->id)->update(['read_at' => now()]);
        return response()->json(['status' => 'success', 'message' => $message]);
    }
}
