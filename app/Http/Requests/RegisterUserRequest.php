<?php

namespace App\Http\Requests;

use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username', 'regex:/\w*$/', 'lowercase'],
            'password' => ['required', Rules\Password::defaults()],
            'cpf' => ['required', 'string', 'max:20', 'unique:users,cpf', new Cpf()],
            'whatsapp' => ['nullable', 'string', 'max:15', 'unique:users,whatsapp', 'regex:/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/'],
            'indicated_by' => ['nullable', 'string']
        ];
    }
}
