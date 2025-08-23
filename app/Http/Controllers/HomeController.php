<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CountTrys;
use App\Http\Controllers\Traits\AdivinhacaoTrait;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\Regioes;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use AdivinhacaoTrait;
    use CountTrys;

    public function __construct(private AdivinhacoesPremiacoes $adivinhacoesPremiacoes, private Adivinhacoes $adivinhacoes, private Regioes $regioes) {}

    public function index(Request $request)
    {
        $trys = 0;
        $limitExceded = true;

        $this->count($trys, $limitExceded);

        $adivinhacoes =  $this->adivinhacoes->getAtivas();

        $adivinhacoes = $adivinhacoes->filter(function ($a) {
            return is_null($a->expire_at) || $a->expire_at > now();
        })->values();


        $adivinhacoes->each(function ($a) {
            $this->customize($a);
        });

        return view('home')->with(compact('adivinhacoes', 'limitExceded', 'trys'));
    }

    public function expiradas()
    {
        $trys = 0;
        $limitExceded = true;

        $this->count($trys, $limitExceded);

        $adivinhacoes =  $this->adivinhacoes->getExpiradas();

        $adivinhacoes = $adivinhacoes->filter(function ($a) {
            return is_null($a->expire_at) || $a->expire_at > now();
        })->values();


        $adivinhacoes->each(function ($a) {
            $this->customize($a);
        });

        return view('home')->with(compact('adivinhacoes', 'limitExceded', 'trys'));
    }

    public function premiacoes()
    {
        $premios = $this->adivinhacoesPremiacoes->getPremiosGanhos();
        return view('premiacoes')->with('premios', $premios);
    }

    public function meusPremios(Request $request)
    {
        $meusPremios = $this->adivinhacoesPremiacoes->getMeusPremios();
        return view('meus_premios')->with(compact('meusPremios'));
    }

    public function get_by_region(Regioes $regiao)
    {
        $trys = 0;
        $limitExceded = true;

        $this->count($trys, $limitExceded);

        $adivinhacoes = $this->adivinhacoes->getByRegion($regiao->id);

        $adivinhacoes = $adivinhacoes->filter(function ($a) {
            return is_null($a->expire_at) || $a->expire_at > now();
        })->values();

        $adivinhacoes->each(function ($a) {
            $this->customize($a);
        });


        $adivinhacoesExpiradas = $this->adivinhacoes->getExpiradasByRegion($regiao->id);

        $premios = $this->adivinhacoesPremiacoes->getPremiosByRegiao($regiao->id);

        return view('home')->with(compact('adivinhacoes', 'limitExceded', 'premios', 'trys', 'adivinhacoesExpiradas', 'regiao'));
    }

    public function hallOfFame(Request $request)
    {
        $usuariosComMaisPremios = $this->adivinhacoesPremiacoes->getUsuariosMaisPremiados();
        return view('hall_da_fama')->with(compact('usuariosComMaisPremios'));
    }

    public function saveFingerprint(Request $request)
    {
        $fingerprint = $request->input('fingerprint');

        session(['fingerprint' => $fingerprint]);
    }

    public function getRegioes()
    {
        $regioes = $this->regioes->getAll();
        return view('regioes')->with(compact('regioes'));
    }

    public function sobre()
    {
        return view('sobre');
    }
}
