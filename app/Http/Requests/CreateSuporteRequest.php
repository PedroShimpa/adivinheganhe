<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateSuporteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation() {}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
            $rules['user_id'] = 'required|exists:users';
        }

        return $rules;
    }
}
