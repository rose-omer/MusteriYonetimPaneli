<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Müşteri listesini içe aktarır. Başlıklar 2. satırda, veri 3. satırdan başlar.
 * Aynı firma_no ile gelen satır mevcut kaydı günceller (upsert); hatalı satır atlanır.
 */
class CustomersImport implements SkipsEmptyRows, SkipsOnFailure, ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow, WithUpsertColumns, WithUpserts, WithValidation
{
    use Importable;
    use SkipsFailures;

    public const BATCH_SIZE = 500;

    public int $inserted = 0;

    public int $updated = 0;

    /** @var array<string, true> Bu dosyada eklenen ya da güncellenen firma numaraları (denetim özeti). */
    private array $touchedFirmaNo = [];

    /** @var array<string, true> İçe aktarımdan önce var olan ve bu dosyada görülen firma numaraları. */
    private array $knownFirmaNo;

    public function __construct()
    {
        $this->knownFirmaNo = array_fill_keys(
            Customer::withTrashed()->whereNotNull('firma_no')->pluck('firma_no')->all(),
            true,
        );
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function prepareForValidation(array $row, int $index): array
    {
        return CustomerRowMapper::map($row);
    }

    /** @param  array<string, mixed>  $row */
    public function model(array $row): Customer
    {
        $firmaNo = $row['firma_no'];

        if ($firmaNo !== null) {
            $this->touchedFirmaNo[$firmaNo] = true;
        }

        if ($firmaNo !== null && isset($this->knownFirmaNo[$firmaNo])) {
            $this->updated++;
        } else {
            $this->inserted++;
            if ($firmaNo !== null) {
                $this->knownFirmaNo[$firmaNo] = true;
            }
        }

        $row['faaliyet_id'] = CustomerRowMapper::faaliyetId($row['faaliyet']);
        unset($row['faaliyet']);

        $customer = new Customer($row);
        // Silinmiş bir firma yeniden içe aktarılırsa kayıt geri gelir.
        $customer->setAttribute('deleted_at', null);

        return $customer;
    }

    public function headingRow(): int
    {
        return 2;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return CustomerRowMapper::rules();
    }

    /** @return array<string, string> */
    public function customValidationMessages(): array
    {
        return CustomerRowMapper::messages();
    }

    /** @return array<string, string> */
    public function customValidationAttributes(): array
    {
        return CustomerRowMapper::attributeLabels();
    }

    public function uniqueBy(): string
    {
        return 'firma_no';
    }

    /** @return list<string> created_at korunur; diğer bütün alanlar güncellenir. */
    public function upsertColumns(): array
    {
        return [...(new Customer)->getFillable(), 'deleted_at', 'updated_at'];
    }

    public function batchSize(): int
    {
        return self::BATCH_SIZE;
    }

    public function chunkSize(): int
    {
        return self::BATCH_SIZE;
    }

    /** @return list<string> */
    public function firmaNumbers(): array
    {
        return array_map('strval', array_keys($this->touchedFirmaNo));
    }

    public function skipped(): int
    {
        return $this->failures()->map->row()->unique()->count();
    }
}
