<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdivinhacoesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
            'expire_at' => ['nullable'],
            'regiao_id' => ['nullable', 'exists:regioes,id']
        ];
    }
}
