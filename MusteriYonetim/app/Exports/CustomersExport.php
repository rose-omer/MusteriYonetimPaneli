<?php

namespace App\Exports;

use App\Imports\CustomerRowMapper;
use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * İçe aktarım şablonuyla aynı düzende dışa aktarır: 1. satır başlık notu,
 * 2. satır sütun başlıkları. Çıktı dosyası değiştirilmeden yeniden içe aktarılabilir.
 */
class CustomersExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    /** @param  Collection<int, Customer>  $customers */
    public function __construct(private readonly Collection $customers) {}

    /** @return list<list<mixed>> */
    public function array(): array
    {
        $rows = [
            ['Müşteri listesi ('.now()->format('d.m.Y H:i').')'],
            array_column(CustomerRowMapper::COLUMNS, 1),
        ];

        foreach ($this->customers as $customer) {
            $rows[] = self::row($customer);
        }

        return $rows;
    }

    /** @return list<mixed> */
    public static function row(Customer $customer): array
    {
        $values = [];

        foreach (CustomerRowMapper::COLUMNS as [$attribute]) {
            $values[] = match ($attribute) {
                'faaliyet' => $customer->faaliyet?->isim,
                'tehlike_sinifi', 'fatura_tipi', 'fatura_durumu', 'odeme_durumu' => $customer->{$attribute}?->getLabel(),
                'sozlesme_onay_durumu' => $customer->sozlesme_onay_durumu ? 'Evet' : 'Hayır',
                'firma_ziyareti' => $customer->firma_ziyareti?->format('d.m.Y'),
                'tutar' => $customer->tutar === null ? null : (float) $customer->tutar,
                default => $customer->{$attribute},
            };
        }

        return $values;
    }

    /** @return array<int, array<string, mixed>> */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['italic' => true, 'color' => ['rgb' => '555555']]],
            2 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Müşteriler';
    }
}
