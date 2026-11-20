<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * İçe aktarımda atlanan satırların listesi: satır numarası ve hata nedeni.
 */
class ImportFailuresExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    /** @param  list<array{line: int|string, messages: string}>  $rows */
    public function __construct(private readonly array $rows) {}

    /** @return list<list<mixed>> */
    public function array(): array
    {
        return array_map(fn (array $row) => [(int) $row['line'], $row['messages']], $this->rows);
    }

    /** @return list<string> */
    public function headings(): array
    {
        return ['Excel satırı', 'Hata'];
    }

    /** @return array<int, array<string, mixed>> */
    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
