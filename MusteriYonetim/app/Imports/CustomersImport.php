<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;

class CustomersImport implements ToModel, WithHeadingRow, WithValidation, WithStartRow
{
    public function model(array $row)
    {
        

        return new Customer([
            'hizmet_alan_isyeri_unvani' => $row['hizmet_alan_isyeri_unvani'] ?? null,
            'firma_no'                  => $row['firma_no'] ?? null,
            'gorunur_unvani'            => $row['gorunur_unvani'] ?? null,
            'faaliyet'                  => $row['faaliyet'] ?? null,
            'calisan_sayisi'            => isset($row['hizmet_alan_isyeri_calisan_sayisi']) ? (int)$row['hizmet_alan_isyeri_calisan_sayisi'] : null,
            'tehlike_sinifi'            => $row['hizmet_alan_isyeri_tehlike_sinifi'] ?? null,
            'firma_sorumlusu'           => $row['firma_sorumlusu'] ?? null,
            'telefon_numarasi'          => $row['yetkili_kisi_telefon_numarasi'] ?? null,
            'il'                        => $row['hizmet_alan_isyeri_ili'] ?? null,
            'mahalle'                   => $row['mahalle'] ?? null,
            'konum_url'                 => $row['konum'] ?? null,
            'fatura_tipi'               => $row['fatura_tipi'] ?? null,
            'fatura_durumu'             => $row['fatura_durumu'] ?? null,
            'tutar'                     => isset($row['tutar']) ? (float)$row['tutar'] : null,
            'odeme_durumu'              => $row['odeme'] ?? null,
            'sozlesme_onay_durumu'      => filter_var($row['sozlesme_onay_durumu'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'igu'                       => $row['igu'] ?? null,
            'ih'                        => $row['ih'] ?? null,
            'firma_ziyareti'            => null,
            'defter'                    => $row['defter'] ?? null,
            'ra'                        => $row['ra'] ?? null,
        ]);
    }

    public function startRow(): int
    {
        return 3;
    }

    public function headingRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'hizmet_alan_isyeri_unvani' => 'required|string|max:255',
            'firma_no'                  => 'nullable|string|max:255',
            // Diğer validasyonlar buraya eklenebilir
        ];
    }

    public function customValidationMessages()
    {
        return [
            'hizmet_alan_isyeri_unvani.required' => 'Hizmet Alan İşyeri Unvanı boş olamaz.',
        ];
    }
}
