<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Adivinhacoes extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'titulo',
        'imagem',
        'descricao',
        'premio',
        'resposta',
        'resolvida',
        'liberado_at',
        'vip_release_at',
        'only_members',
        'expire_at',
        'exibir_home',
        'dica',
        'dica_paga',
        'dica_valor',
        'regiao_id',
        'visualizacoes',
        'formato_resposta',
        'enviar_emails',
        'notificar_whatsapp',
        'notificar_email',
        'notificado_email_em',
        'notificado_whatsapp_em',
        'dificuldade',
    ];

    protected $dates = [
        'expire_at',
        'vip_release_at'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }



    public function respostas()
    {
        return $this->hasMany(AdivinhacoesRespostas::class, 'adivinhacao_id');
    }

    public function getAtivas($withRespostas = false)
    {
        $query = $this->select(
            'id',
            'uuid',
            'titulo',
            'imagem',
            'descricao',
            'premio',
            'expire_at',
            'dica',
            'dica_paga',
            'dica_valor',
            'created_at',
            'formato_resposta',
            'dificuldade',
            'vip_release_at',
            'only_members',
        )
            ->whereNull('regiao_id')
            ->where('resolvida', 'N')
            ->where('exibir_home', 'S')
            ->where(function ($q) {
                $q->where('expire_at', '>', now());
                $q->orWhereNull('expire_at');
            })
            ->where(function ($q) {
                $q->where('liberado_at', '<=', now());
                $q->orWhereNull('liberado_at');
            })
            ->withCount('likes');

        if (auth()->check()) {
            $query->with(['likes' => function ($q) {
                $q->where('user_id', auth()->id());
            }]);
        }

        if ($withRespostas) {
            $query->with('respostas');
        }

        return $query->orderBy('liberado_at', 'desc')->get();
    }

    public function getByRegion(int $regiaoId)
    {
        return $this->select(
            'id',
            'uuid',
            'titulo',
            'imagem',
            'descricao',
            'premio',
            'expire_at',
            'dica',
            'dica_paga',
            'dica_valor',
            'created_at',
            'formato_resposta',
            'dificuldade',
            'vip_release_at',
            'only_members'
        )
            ->where('regiao_id', $regiaoId)
            ->where('resolvida', 'N')
            ->where('exibir_home', 'S')
            ->where(function ($q) {
                $q->where('expire_at', '>', now());
                $q->orWhereNull('expire_at');
            })
            ->where(function ($q) {
                $q->where('liberado_at', '<=', now());
                $q->orWhereNull('liberado_at');
            })
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getExpiradasByRegion(int $regiaoId)
    {
        return $this->select('uuid', 'titulo')
            ->where('resolvida', 'N')
            ->where('regiao_id', $regiaoId)
            ->whereNotNull('expire_at')
            ->where('expire_at', '<', now())
            ->orderBy('expire_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function getExpiradas()
    {
        return $this->select(
            'id',
            'uuid',
            'titulo',
            'imagem',
            'descricao',
            'premio',
            'dica',
            'dica_paga',
            'dica_valor',
            'created_at',
            'formato_resposta',
            'dificuldade',
            'vip_release_at',
            'only_members',
        )
            ->where('resolvida', 'N')
            ->whereNotNull('expire_at')
            ->where('expire_at', '<', now())
            ->orderBy('expire_at', 'desc')
            ->get();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    protected static function booted()
    {
        static::creating(function ($model) {

            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
