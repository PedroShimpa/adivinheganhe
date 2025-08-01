<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSuporteRequest;
use App\Models\Suporte;
use App\Models\SuporteCategorias;

class SuporteController extends Controller
{
    public function new_help()
    {
        $categorias = SuporteCategorias::all();
        return view('suporte')->with(compact('categorias'));
    }

    public function store(CreateSuporteRequest $request)
    {
        Suporte::create($request->validated());

        return redirect()->back()->with(['success' => 'Seu chamado foi aberto, em breve retornaremos com respostas']);
    }
}
