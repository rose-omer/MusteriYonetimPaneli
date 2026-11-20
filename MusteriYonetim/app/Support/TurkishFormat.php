<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

/**
 * Excel ve formdan gelen Türkçe biçimli değerleri çözer.
 */
final class TurkishFormat
{
    /**
     * "1.250,50" → "1250.50", "1250.5" → "1250.50", "3.000 TL" → "3000.00".
     * Sayı olmayan değer için null döner; çağıran bunu satır hatası olarak raporlar.
     */
    public static function money(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return number_format((float) $value, 2, '.', '');
        }

        $text = preg_replace('/\s|\x{20BA}|TL|TRY/iu', '', (string) $value) ?? '';

        if ($text === '' || preg_match('/^-?[\d.,]+$/', $text) !== 1) {
            return null;
        }

        $lastComma = strrpos($text, ',');
        $lastDot = strrpos($text, '.');

        if ($lastComma !== false && ($lastDot === false || $lastComma > $lastDot)) {
            // Türkçe biçim: nokta binlik, virgül ondalık ayırıcı.
            $text = str_replace(['.', ','], ['', '.'], $text);
        } elseif ($lastDot !== false && (substr_count($text, '.') > 1 || preg_match('/^-?\d{1,3}\.\d{3}$/', $text) === 1)) {
            // "1.250.000" ya da "3.000": Türkçe yazımda binlik ayırıcı.
            $text = str_replace('.', '', $text);
        } else {
            $text = str_replace(',', '', $text);
        }

        if (! is_numeric($text)) {
            return null;
        }

        return number_format((float) $text, 2, '.', '');
    }

    /**
     * "15.03.2025", "15/03/2025", "2025-03-15" ya da Excel seri sayısı → "2025-03-15".
     */
    public static function date(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        try {
            if (is_int($value) || is_float($value) || (is_string($value) && preg_match('/^\d{4,5}(\.\d+)?$/', $value) === 1)) {
                return CarbonImmutable::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
            }

            $text = trim((string) $value);

            foreach (['d.m.Y', 'd/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
                // Yerel DateTime uyumsuz biçimde false döner (Carbon istisna atar).
                $date = \DateTimeImmutable::createFromFormat('!'.$format, $text);

                if ($date !== false && $date->format($format) === $text) {
                    return $date->format('Y-m-d');
                }
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    /** "evet", "1", "true", "onaylı" → true; "hayır", "0", "false" → false; tanınmayan → null. */
    public static function bool(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        $text = mb_strtolower(trim((string) $value), 'UTF-8');

        return match (true) {
            in_array($text, ['1', 'true', 'evet', 'e', 'var', 'onaylı', 'onayli', 'onaylandı', 'yes'], true) => true,
            in_array($text, ['0', 'false', 'hayır', 'hayir', 'h', 'yok', 'onaysız', 'onaysiz', 'no'], true) => false,
            default => null,
        };
    }

    /** Görüntülemek için: "05321234567" → "0532 123 45 67". */
    public static function phone(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        $digits = self::phoneDigits($phone);

        if (strlen($digits) !== 11) {
            return $phone;
        }

        return sprintf('%s %s %s %s', substr($digits, 0, 4), substr($digits, 4, 3), substr($digits, 7, 2), substr($digits, 9, 2));
    }

    /** KVKK: "05321234567" → "0532 *** ** 67". */
    public static function maskPhone(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        $digits = self::phoneDigits($phone);

        if (strlen($digits) < 7) {
            return str_repeat('*', strlen($digits));
        }

        return substr($digits, 0, 4).' *** ** '.substr($digits, -2);
    }

    private static function phoneDigits(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (strlen($digits) === 12 && str_starts_with($digits, '90')) {
            $digits = substr($digits, 2);
        }

        return strlen($digits) === 10 ? '0'.$digits : $digits;
    }
}
