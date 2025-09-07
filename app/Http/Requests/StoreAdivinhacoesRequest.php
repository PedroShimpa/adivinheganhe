<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdivinhacoesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['string'],
            'imagem' => ['file'],
            'descricao'  => ['string',  'nullable'],
            'premio'  => ['string'],
            'resposta' => ['string'],
            'dica' => ['string', 'nullable'],
            'dica_paga' => ['string', 'size:1'],
            'dica_valor' => ['nullable'],
            'liberado_at' => ['nullable'],
            'expire_at' => ['nullable'],
            'regiao_id' => ['nullable', 'exists:regioes,id'],
            'formato_resposta' => ['nullable'],
            'notificar_whatsapp' => ['nullable'],
            'notificar_email' => ['nullable'],
        ];
    }
}
