<?php

use App\Enums\FaturaDurumu;
use App\Enums\FaturaTipi;
use App\Enums\OdemeDurumu;
use App\Enums\TehlikeSinifi;
use App\Support\TurkishFormat;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Veri göçü (tip değişikliğinden önce çalışır):
 * - faaliyet metni → faaliyet_id (eksik faaliyetler eklenir)
 * - seçenek metinleri → enum değerleri
 * - sözleşme onayı → 1/0, firma ziyareti → Y-m-d
 * - boş unvan doldurulur, mükerrer firma_no temizlenir
 *
 * Tanınmayan değer boşaltılır ve günlüğe yazılır. Canlı veride önce yedekte deneyin.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $faaliyetIds = DB::table('faaliyets')->pluck('id', 'isim')->all();
        $seenFirmaNo = [];
        $unknown = [];

        DB::table('customers')->orderBy('id')->chunkById(500, function ($rows) use (&$faaliyetIds, &$seenFirmaNo, &$unknown, $now) {
            foreach ($rows as $row) {
                $update = [];

                $isim = trim((string) $row->faaliyet);
                if ($isim !== '') {
                    if (! isset($faaliyetIds[$isim])) {
                        $faaliyetIds[$isim] = DB::table('faaliyets')->insertGetId(['isim' => $isim, 'created_at' => $now, 'updated_at' => $now]);
                    }
                    $update['faaliyet_id'] = $faaliyetIds[$isim];
                }

                foreach ([
                    'tehlike_sinifi' => TehlikeSinifi::class,
                    'fatura_tipi' => FaturaTipi::class,
                    'fatura_durumu' => FaturaDurumu::class,
                    'odeme_durumu' => OdemeDurumu::class,
                ] as $column => $enum) {
                    if ($row->{$column} === null || $row->{$column} === '') {
                        continue;
                    }

                    $case = $enum::fromLabel($row->{$column});
                    if ($case === null) {
                        $unknown[] = "#{$row->id} {$column}={$row->{$column}}";
                    }
                    $update[$column] = $case?->value;
                }

                $update['sozlesme_onay_durumu'] = TurkishFormat::bool($row->sozlesme_onay_durumu) ? 1 : 0;
                $update['firma_ziyareti'] = TurkishFormat::date($row->firma_ziyareti);

                if (trim((string) $row->hizmet_alan_isyeri_unvani) === '') {
                    $update['hizmet_alan_isyeri_unvani'] = $row->gorunur_unvani ?: "Adsız firma #{$row->id}";
                }

                $firmaNo = trim((string) $row->firma_no);
                if ($firmaNo === '' || isset($seenFirmaNo[$firmaNo])) {
                    if ($firmaNo !== '') {
                        $unknown[] = "#{$row->id} mükerrer firma_no={$firmaNo}";
                    }
                    $update['firma_no'] = null;
                } else {
                    $seenFirmaNo[$firmaNo] = true;
                    $update['firma_no'] = $firmaNo;
                }

                DB::table('customers')->where('id', $row->id)->update($update);
            }
        });

        if ($unknown !== []) {
            Log::warning('Müşteri veri göçü: tanınmayan değerler boşaltıldı.', ['kayitlar' => $unknown]);
        }
    }

    public function down(): void
    {
        // Geri alınamaz veri dönüşümü. Şema göçlerinin down() adımı sütunları eski tiplerine döndürür.
    }
};
