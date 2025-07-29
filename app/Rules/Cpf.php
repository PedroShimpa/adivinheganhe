<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Bissolli\ValidadorCpfCnpj\Cpf as CpfValidator;

class Cpf implements Rule
{
    public function passes($attribute, $value)
    {
        $cpfValidator = new CpfValidator($value);
        return $cpfValidator->isValid();
    }

    public function message()
    {
        return 'O :attribute informado não é um CPF válido.';
    }
}
