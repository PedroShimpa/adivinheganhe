<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSuporteRequest;
use App\Jobs\AdicionarSuporte;
use App\Mail\NotifyAdminsOfNewTicket;
use App\Mail\SupportResponseMail;
use App\Models\Suporte;
use App\Models\SuporteCategorias;
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
}
