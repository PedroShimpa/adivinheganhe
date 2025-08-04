<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateSuporteRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        $data = [];
        if (Auth::check()) {

            $data['user_id'] =  auth()->user()->id;
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        $rules = [
            'categoria_id' => 'required|exists:suporte_categorias,id',
            'descricao' => 'required|string',
        ];

        if (!Auth::check()) {
            $rules['nome'] = 'required|max:255';
            $rules['email'] = 'required|email:rfc,dns|max:255';
        } else {
            $rules['user_id'] = 'required|exists:users,id';
        }

        return $rules;
    }
}
