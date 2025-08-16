<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdivinhacoesPremiacoes extends Model
{
    use Cachable;

    protected $table = 'adivinhacoes_premiacoes';

    protected $fillable = [
        'user_id',
        'adivinhacao_id',
        'premio_enviado',
        'previsao_envio_premio',
        'vencedor_notificado'
    ];

    public function getMeusPremios()
    {
        return $this->select(
            'adivinhacoes_premiacoes.id',
            'adivinhacoes.uuid',
            'adivinhacoes.titulo',
            'adivinhacoes.resposta',
            'adivinhacoes.premio',
            'users.username',
            'premio_enviado',
            'previsao_envio_premio',
            'vencedor_notificado'
        )
            ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_premiacoes.adivinhacao_id')
            ->join('users', 'users.id', '=', 'adivinhacoes_premiacoes.user_id')
            ->where('adivinhacoes_premiacoes.user_id', auth()->user()->id)
            ->orderBy('adivinhacoes_premiacoes.id', 'desc')
            ->get();
    }

    public function getUsuariosMaisPremiados()
    {
        return $this->select(
            'users.username',
            DB::raw('COUNT(adivinhacoes_premiacoes.id) as count_premiacoes')
        )
            ->join('users', 'users.id', '=', 'adivinhacoes_premiacoes.user_id')
            ->groupBy('adivinhacoes_premiacoes.user_id', 'users.username')
            ->orderByDesc('count_premiacoes')
            ->limit(10)
            ->get();
    }

    public function getPremiosGanhos()
    {

        return $this->select(
            'adivinhacoes_premiacoes.id',
            'adivinhacoes.uuid',
            'adivinhacoes.titulo',
            'adivinhacoes.resposta',
            'adivinhacoes.premio',
            'users.username',
            'premio_enviado',
            'previsao_envio_premio',
            'vencedor_notificado'
        )
            ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_premiacoes.adivinhacao_id')
            ->join('users', 'users.id', '=', 'adivinhacoes_premiacoes.user_id')
            ->orderBy('adivinhacoes_premiacoes.id', 'desc')
            ->limit(10)
            ->get();
    }

    public function getPremiosByRegiao(int $regiaoId)
    {
        return $this->select(
            'adivinhacoes_premiacoes.id',
            'adivinhacoes.uuid',
            'adivinhacoes.titulo',
            'adivinhacoes.resposta',
            'adivinhacoes.premio',
            'users.username',
            'premio_enviado',
            'previsao_envio_premio',
            'vencedor_notificado'
        )
            ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_premiacoes.adivinhacao_id')
            ->join('users', 'users.id', '=', 'adivinhacoes_premiacoes.user_id')
            ->where('regiao_id', $regiaoId)
            ->orderBy('adivinhacoes_premiacoes.id', 'desc')
            ->limit(10)
            ->get();
    }
}
