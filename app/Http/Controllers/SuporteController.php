<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSuporteRequest;
use App\Jobs\AdicionarSuporte;
use App\Mail\NotifyAdminsOfNewTicket;
use App\Models\SuporteCategorias;
use App\Models\User;
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
}
