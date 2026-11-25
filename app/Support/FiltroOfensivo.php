<?php

namespace App\Support;

class FiltroOfensivo
{
    // Ampliá la lista a gusto. Se compara sin tildes, sin mayúsculas y con variantes tipo "1d10ta".
    private const PALABRAS = [
        'idiota', 'estupido', 'imbecil', 'boludo', 'pelotudo', 'forro', 'inutil',
        'basura', 'mierda', 'puto', 'puta', 'hijo de puta', 'hdp', 'cagon',
        'tarado', 'mongolico', 'sorete',
    ];

    public static function contiene(string $texto): bool
    {
        $limpio = self::normalizar($texto);

        foreach (self::PALABRAS as $palabra) {
            // \b = palabra completa: "computadora" no dispara "puta"
            if (preg_match('/\b' . preg_quote(self::normalizar($palabra), '/') . '\b/u', $limpio)) {
                return true;
            }
        }

        return false;
    }

    private static function normalizar(string $t): string
    {
        $t = mb_strtolower($t);
        $t = strtr($t, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            '0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's', '@' => 'a', '$' => 's',
        ]);
        $t = preg_replace('/(.)\1+/u', '$1', $t);   // "idiotaaa" -> "idiota"

        return preg_replace('/\s+/', ' ', $t);
    }
}