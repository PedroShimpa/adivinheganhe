<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterExtraUserRequest extends FormRequest
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
            'username' => ['required', 'string', 'max:255', 'unique:users,username', 'regex:/^[a-z0-9_]+$/i', 'lowercase'],
            'cpf' => ['required', 'string', 'max:20', 'unique:users,cpf', new Cpf()],
            'whatsapp' => ['nullable', 'string', 'max:15', 'unique:users,whatsapp', 'regex:/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/'],
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
