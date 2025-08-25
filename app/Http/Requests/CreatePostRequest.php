<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:1000'],
            'file'    => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.max' => 'O conteúdo não pode ter mais que 1000 caracteres.',
            'file.image'  => 'O arquivo precisa ser uma imagem.',
            'file.mimes'  => 'Formatos aceitos: JPG, JPEG, PNG ou WEBP.',
            'file.max'    => 'A imagem não pode ultrapassar 2MB.',
        ];
    }
}
