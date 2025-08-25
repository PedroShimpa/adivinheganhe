<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
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
        if (empty($this->perfil_privado))
            $this->merge(['perfil_privado' => 'N']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
                'regex:/^[a-z0-9_]+$/i', // apenas letras, números e _
                'lowercase',
            ],

            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
                'lowercase',
            ],

            'whatsapp' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique(User::class)->ignore($this->user()->id, 'id'),
                'regex:/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/',
            ],

            'perfil_privado' => ['required', 'in:S,N'], 

            'bio' => ['nullable', 'string', 'max:500'],

            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
        ];
    }
}
