<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class FaaliyetManager
{
    const FILE_PATH = 'app/storage/app/faaliyetler.json'; // Bu, storage/app/faaliyetler.json anlamına gelir
    /**
     * Faaliyet listesini JSON dosyasından okur.
     *
     * @return array
     */
    public function getFaaliyetler(): array
    {
        // Dosya mevcut değilse veya boşsa boş bir dizi döndür
        if (!Storage::exists(self::FILE_PATH) || Storage::size(self::FILE_PATH) === 0) {
            return [];
        }

        $contents = Storage::get(self::FILE_PATH);
        $faaliyetler = json_decode($contents, true);

        // JSON çözülmezse boş dizi döndür
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        // Listeyi alfabetik olarak sırala
        sort($faaliyetler);

        return $faaliyetler;
    }

    /**
     * Yeni bir faaliyeti listeye ekler.
     *
     * @param string $faaliyet
     * @return bool
     */
    public function addFaaliyet(string $faaliyet): bool
    {
        $faaliyet = trim($faaliyet); // Başındaki ve sonundaki boşlukları temizle

        if (empty($faaliyet)) {
            return false; // Boş faaliyet eklenemez
        }

        $currentFaaliyetler = $this->getFaaliyetler();

        // Zaten varsa ekleme
        if (in_array($faaliyet, $currentFaaliyetler)) {
            return false;
        }

        $currentFaaliyetler[] = $faaliyet;
        return $this->saveFaaliyetler($currentFaaliyetler);
    }

    /**
     * Bir faaliyeti listeden siler.
     *
     * @param string $faaliyet
     * @return bool
     */
    public function removeFaaliyet(string $faaliyet): bool
    {
        $currentFaaliyetler = $this->getFaaliyetler();
        $key = array_search($faaliyet, $currentFaaliyetler);

        if ($key !== false) {
            unset($currentFaaliyetler[$key]);
            // Diziyi yeniden indeksle
            $currentFaaliyetler = array_values($currentFaaliyetler);
            return $this->saveFaaliyetler($currentFaaliyetler);
        }
        return false; // Faaliyet bulunamadı
    }

    /**
     * Güncel faaliyet listesini JSON dosyasına yazar.
     *
     * @param array $faaliyetler
     * @return bool
     */
    protected function saveFaaliyetler(array $faaliyetler): bool
    {
        // Klasör mevcut değilse oluştur (örneğin storage/app)
        if (!Storage::exists(dirname(self::FILE_PATH))) {
            Storage::makeDirectory(dirname(self::FILE_PATH));
        }

        $jsonContents = json_encode($faaliyetler, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($jsonContents === false) {
            return false; // JSON kodlama hatası
        }

        return Storage::put(self::FILE_PATH, $jsonContents);
    }
}