<?php

namespace App\Enums\Concerns;

use Illuminate\Support\Str;

/**
 * Excel'den ya da eski kayıtlardan gelen serbest metni enum değerine çevirir.
 * "ÖDENMEMİŞ", "Ödenmemiş" ve "odenmemis" aynı sonucu verir.
 */
trait LabelLookup
{
    public static function fromLabel(mixed $text): ?self
    {
        if ($text instanceof self) {
            return $text;
        }

        if (! is_string($text) || trim($text) === '') {
            return null;
        }

        $key = self::normalizeKey($text);

        foreach (self::cases() as $case) {
            if ($key === self::normalizeKey($case->value) || $key === self::normalizeKey($case->getLabel())) {
                return $case;
            }

            foreach ($case->aliases() as $alias) {
                if ($key === self::normalizeKey($alias)) {
                    return $case;
                }
            }
        }

        return null;
    }

    /** @return list<string> */
    public function aliases(): array
    {
        return [];
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }

    private static function normalizeKey(string $text): string
    {
        return Str::slug(Str::ascii(mb_strtolower(str_replace(['İ', 'I'], ['i', 'ı'], trim($text)), 'UTF-8'), 'tr'), '_');
    }
}
