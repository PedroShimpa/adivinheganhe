<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class Regioes extends Model
{
    use Cachable;

    const UPDATED_AT = null;

    protected $table = 'regioes';

    protected $fillbale = [
        'nome',
        'slug_url',
        'descricao'
    ];

    public function getRouteKeyName()
    {
        return 'slug_url';
    }

    public function adivinhacoes()
    {
        return $this->hasMany(\App\Models\Adivinhacoes::class, 'regiao_id');
    }

    public function getAll()
    {
        return $this->withCount([
            'adivinhacoes as count_adivinhacoes' => function ($query) {
                $query->where('resolvida', 'N');
            }
        ])
            ->orderBy('nome', 'asc')
            ->get();
    }
}
