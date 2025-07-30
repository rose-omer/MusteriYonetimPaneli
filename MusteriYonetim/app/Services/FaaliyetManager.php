<?php

namespace App\Services;

use App\Models\Faaliyet;

class FaaliyetManager
{
    /**
     * Veritabanından tüm faaliyetleri alır.
     *
     * @return array
     */
    public function getFaaliyetler(): array
    {
        // 'isim' alanına göre sıralı olarak tüm faaliyetleri döndür
        return Faaliyet::orderBy('isim')->pluck('isim')->toArray();
    }

    /**
     * Yeni bir faaliyeti veritabanına ekler.
     *
     * @param string $faaliyet
     * @return bool
     */
    public function addFaaliyet(string $faaliyet): bool
    {
        $faaliyet = trim($faaliyet);

        if (empty($faaliyet)) {
            return false; // Boş faaliyet eklenemez
        }

        // Aynı faaliyet zaten varsa ekleme
        if (Faaliyet::where('isim', $faaliyet)->exists()) {
            return false;
        }

        // Veritabanına kaydet
        Faaliyet::create(['isim' => $faaliyet]);
        return true;
    }

    /**
     * Belirtilen faaliyeti veritabanından siler.
     *
     * @param string $faaliyet
     * @return bool
     */
    public function removeFaaliyet(string $faaliyet): bool
    {
        $record = Faaliyet::where('isim', $faaliyet)->first();

        if ($record) {
            $record->delete();
            return true;
        }

        return false; // Kayıt bulunamadı
    }
}
