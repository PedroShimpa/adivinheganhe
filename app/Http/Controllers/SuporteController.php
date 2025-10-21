<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSuporteRequest;
use App\Jobs\AdicionarSuporte;
use App\Mail\NotifyAdminsOfNewTicket;
use App\Mail\SupportResponseMail;
use App\Models\Suporte;
use App\Models\SuporteCategorias;
use App\Models\SuporteChatMessages;
use App\Models\User;
use App\Notifications\SupportResponseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SuporteController extends Controller
{
    public function new_help()
    {
        $categorias = SuporteCategorias::all();
        return view('suporte')->with(compact('categorias'));
    }

    public function store(CreateSuporteRequest $request)
    {

        $data = $request->validated();
        $data['created_at'] = now();
        dispatch(new AdicionarSuporte($data));

        $nome = auth()->check() ? auth()->user()->name : $request->input('nome');
        $email = auth()->check() ? null : $request->input('email');

        $categoria = SuporteCategorias::find($request->input('categoria_id'))->descricao ?? 'Desconhecida';

        $descricao = $request->input('descricao');

        $admins = User::where('is_admin', 'S')->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new NotifyAdminsOfNewTicket($nome, $email, $categoria, $descricao));
        }

        return redirect()->back()->with(['success' => 'Seu chamado foi aberto, em breve retornaremos com respostas']);
    }

    public function adminIndex()
    {
        $suportes = Suporte::with('categoria')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.suporte.index', compact('suportes'));
    }

    public function adminShow(Suporte $suporte)
    {
        $suporte->load('categoria', 'user');
        return view('admin.suporte.show', compact('suporte'));
    }

    public function adminUpdate(Request $request, Suporte $suporte)
    {
        $request->validate([
            'status' => 'required|in:A,EA,F',
            'admin_response' => 'nullable|string',
        ]);

        $oldStatus = $suporte->status;
        $oldResponse = $suporte->admin_response;

        $suporte->update($request->only(['status', 'admin_response']));

        // Send notification if status changed or response added
        if ($oldStatus !== $suporte->status || ($oldResponse !== $suporte->admin_response && !empty($suporte->admin_response))) {
            $this->notifyRequester($suporte);
        }

        return redirect()->route('suporte.admin.show', $suporte)->with('success', 'Chamado atualizado com sucesso.');
    }

    public function userIndex()
    {
        $suportes = Suporte::where('user_id', auth()->id())->with('categoria')->orderBy('created_at', 'desc')->paginate(20);
        return view('suporte.user_index', compact('suportes'));
    }

    public function userShow(Suporte $suporte)
    {
        // Ensure user owns the suporte
        if ($suporte->user_id !== auth()->id()) {
            abort(403);
        }
        $suporte->load('categoria', 'chatMessages');
        return view('suporte.user_show', compact('suporte'));
    }

    public function apiUserIndex()
    {
        $suportes = Suporte::where('user_id', auth()->id())->with('categoria')->orderBy('created_at', 'desc')->get();
        return response()->json($suportes);
    }

    public function apiGetChatMessages(Suporte $suporte)
    {
        if ($suporte->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $suporte->chatMessages()->with('user')->orderBy('created_at', 'asc')->get()->map(function ($msg) {
            return [
                'user_name' => $msg->user->name,
                'message' => $msg->message,
                'created_at' => $msg->created_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json($messages);
    }

    public function apiStoreChatMessage(Request $request, Suporte $suporte)
    {
        if ($suporte->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
            'send_push' => 'boolean',
            'send_email' => 'boolean',
        ]);

        $message = SuporteChatMessages::create([
            'suporte_id' => $suporte->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        // Send notifications if requested
        if ($request->send_push || $request->send_email) {
            $this->sendMessageNotification($suporte, $request->message, $request->send_push, $request->send_email);
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    private function notifyRequester(Suporte $suporte)
    {
        $email = $suporte->user ? $suporte->user->email : $suporte->email;

        if ($email) {
            Mail::to($email)->queue(new SupportResponseMail($suporte));
        }

        if ($suporte->user) {
            $suporte->user->notify(new SupportResponseNotification($suporte));
        }
    }

    private function sendMessageNotification(Suporte $suporte, string $message, bool $sendPush, bool $sendEmail)
    {
        if ($sendEmail) {
            $email = $suporte->user ? $suporte->user->email : $suporte->email;
            if ($email) {
                Mail::to($email)->queue(new \App\Mail\SupportMessageMail($suporte, $message));
            }
        }

        if ($sendPush && $suporte->user) {
            $suporte->user->notify(new \App\Notifications\SupportMessageNotification($suporte, $message));
        }
    }
}
