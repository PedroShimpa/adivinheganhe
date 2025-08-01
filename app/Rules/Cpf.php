<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cpf implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->isCpfValid(preg_replace('/[^0-9]/', '', $value))) {
            return;
        }

        $fail('O cpf informado não é valido')->translate();
    }


    public static function isCpfValid(?string $cpf): bool
    {
        if ($cpf === null) {
            return false;
        }

        $digits = substr($cpf, 0, 9);
        $newCpf = self::calculateDigitsPositions($digits);
        $newCpf = self::calculateDigitsPositions($newCpf, 11);

        return $newCpf === $cpf && self::validateSequency($cpf, 11);
    }

    private static function calculateDigitsPositions(string $digits, int $positions = 10, int $digitsSum = 0): string
    {
        for ($i = 0; $i < strlen($digits); $i++) {
            $digitsSum += intval($digits[$i]) * $positions;
            $positions--;

            if ($positions < 2) {
                $positions = 9;
            }
        }
        $digitsSum = $digitsSum % 11;

        if ($digitsSum < 2) {
            $digitsSum = 0;
        } else {
            $digitsSum = 11 - $digitsSum;
        }

        return $digits . $digitsSum;
    }

    private static function validateSequency(string $doc, int $multiples): bool
    {
        for ($i = 0; $i < 10; $i++) {
            if (str_repeat(strval($i), $multiples) == $doc) {
                return false;
            }
        }

        return true;
    }
}
