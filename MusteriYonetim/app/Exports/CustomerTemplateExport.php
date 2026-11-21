<?php

namespace App\Exports;

use App\Imports\CustomerRowMapper;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Örnek içe aktarım şablonu. Bütün değerler sahtedir.
 */
class CustomerTemplateExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    /** @param  list<array<string, mixed>>|null  $rows  Alan adı → değer; boşsa örnek satırlar kullanılır. */
    public function __construct(private readonly ?array $rows = null) {}

    /** @return list<list<mixed>> */
    public function array(): array
    {
        $sheet = [
            ['Başlıklar 2. satırda. Veriyi 3. satırdan başlayarak girin. Tutar: 1.250,50 · Tarih: 15.03.2025 · Sözleşme onayı: Evet/Hayır'],
            array_column(CustomerRowMapper::COLUMNS, 1),
        ];

        foreach ($this->rows ?? self::sampleRows() as $row) {
            $line = [];
            foreach (CustomerRowMapper::COLUMNS as [$attribute]) {
                $line[] = $row[$attribute] ?? null;
            }
            $sheet[] = $line;
        }

        return $sheet;
    }

    /** @return list<array<string, mixed>> */
    public static function sampleRows(): array
    {
        return [
            [
                'firma_no' => '10001', 'hizmet_alan_isyeri_unvani' => 'Örnek Metal San. ve Tic. Ltd. Şti.', 'gorunur_unvani' => 'Örnek Metal',
                'faaliyet' => 'Metal işleme', 'calisan_sayisi' => 48, 'tehlike_sinifi' => 'Çok tehlikeli', 'firma_sorumlusu' => 'Ayşe Yılmaz',
                'telefon_numarasi' => '0532 000 00 01', 'il' => 'Bursa', 'mahalle' => 'Organize Sanayi Mah.', 'konum_url' => 'https://maps.google.com/?q=40.2,29.0',
                'fatura_tipi' => 'Aylık', 'fatura_durumu' => 'Kesildi', 'tutar' => '12.500,00', 'odeme_durumu' => 'Ödenmedi',
                'sozlesme_onay_durumu' => 'Evet', 'igu' => 'Mehmet Kaya', 'ih' => 'Dr. Elif Demir', 'firma_ziyareti' => '15.03.2025', 'defter' => 'Var', 'ra' => 'Hazır',
            ],
            [
                'firma_no' => '10002', 'hizmet_alan_isyeri_unvani' => 'Deneme Gıda A.Ş.', 'gorunur_unvani' => 'Deneme Gıda',
                'faaliyet' => 'Gıda üretimi', 'calisan_sayisi' => 120, 'tehlike_sinifi' => 'Tehlikeli', 'firma_sorumlusu' => 'Can Öztürk',
                'telefon_numarasi' => '0555 000 00 02', 'il' => 'İzmir', 'mahalle' => 'Atatürk Mah.', 'konum_url' => null,
                'fatura_tipi' => 'Yıllık', 'fatura_durumu' => 'Kesilmedi', 'tutar' => '48.000', 'odeme_durumu' => 'Kısmi ödendi',
                'sozlesme_onay_durumu' => 'Hayır', 'igu' => 'Zeynep Arslan', 'ih' => 'Dr. Ali Çelik', 'firma_ziyareti' => null, 'defter' => 'Yok', 'ra' => 'Güncellenecek',
            ],
        ];
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
