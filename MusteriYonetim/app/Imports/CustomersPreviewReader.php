<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Önizleme için satırları yalnız okur; veritabanına yazmaz.
 */
class CustomersPreviewReader implements Import, WithHeadingRow
{
    public function headingRow(): int
    {
        return 2;
    }
}
