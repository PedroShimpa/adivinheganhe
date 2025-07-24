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
            'descricao'  => ['string'],
            'premio'  => ['string'],
            'resposta'=> ['string'],
            'dica' => ['string', 'nullable'],
            'dica_paga' => ['string', 'size:1'],
            'dica_valor' => [ 'nullable'],
        ];
    }
}
