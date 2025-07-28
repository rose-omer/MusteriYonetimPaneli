<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    // Toplu atama için izin verilen alanlar
    protected $fillable = [
        'firma_no',
        'hizmet_alan_isyeri_unvani',
        'gorunur_unvani',
        'faaliyet',
        'calisan_sayisi',
        'tehlike_sinifi',
        'firma_sorumlusu',
        'telefon_numarasi',
        'il',
        'mahalle',
        'konum_url',
        'fatura_tipi',
        'fatura_durumu',
        'tutar',
        'odeme_durumu',
        'sozlesme_onay_durumu',
        'igu',
        'ih',
        'firma_ziyareti',
        'defter',
        'ra',
    ];
}