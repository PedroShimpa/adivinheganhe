<?php

namespace App\Http\Requests;

use App\Models\User;
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

    public function prepareForValidation()
    {
        $this->merge(['fingerprint' => session('fingerprint')]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username', 'regex:/^[a-z0-9_]+$/i', 'lowercase'],
            'password' => ['required', Rules\Password::defaults()],
            'cpf' => ['required', 'string', 'max:20', 'unique:users,cpf', new Cpf()],
            'whatsapp' => ['nullable', 'string', 'max:15', 'unique:users,whatsapp', 'regex:/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/'],
            'indicated_by' => ['nullable', 'string', 'exists:users,uuid'],
            'fingerprint' => ['required', 'string']
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $fingerprint = $this->input('fingerprint');

            if ($fingerprint) {
                $quantidade = User::where('fingerprint', $fingerprint)
                    ->whereNotNull('fingerprint')
                    ->count();

                if ($quantidade >= env('MAX_REG_PER_FINGERPOINT', 3)) {
                    $validator->errors()->add('fingerprint', 'Você já tem muitos cadastros, entre em contato com nossa equipe caso precise fazer mais cadastros.');
                }
            }
        });
    }
}
