<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
// use Anhskohbo\NoCaptcha\Rules\Captcha;

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
        $this->merge(['fingerprint' => session('fingerprint'), 'email' => strtolower($this->email), 'username' => strtolower($this->username)]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username', 'regex:/^[a-z0-9_]+$/i', 'lowercase'],
            'password' => ['required', Rules\Password::defaults()],
            'indicated_by' => ['nullable', 'string', 'exists:users,uuid'],
            'fingerprint' => ['nullable', 'string'],
            // 'g-recaptcha-response' => ['required', new Captcha()],
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
