<?php

namespace App\Models;

use App\Enums\FaturaDurumu;
use App\Enums\FaturaTipi;
use App\Enums\OdemeDurumu;
use App\Enums\TehlikeSinifi;
use App\Observers\CustomerObserver;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $firma_no
 * @property string $hizmet_alan_isyeri_unvani
 * @property string|null $gorunur_unvani
 * @property int|null $faaliyet_id
 * @property int|null $calisan_sayisi
 * @property TehlikeSinifi|null $tehlike_sinifi
 * @property string|null $firma_sorumlusu
 * @property string|null $telefon_numarasi
 * @property string|null $il
 * @property string|null $mahalle
 * @property string|null $konum_url
 * @property FaturaTipi|null $fatura_tipi
 * @property FaturaDurumu|null $fatura_durumu
 * @property string|null $tutar
 * @property OdemeDurumu|null $odeme_durumu
 * @property bool $sozlesme_onay_durumu
 * @property string|null $igu
 * @property string|null $ih
 * @property Carbon|null $firma_ziyareti
 * @property string|null $defter
 * @property string|null $ra
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Faaliyet|null $faaliyet
 */
#[ObservedBy(CustomerObserver::class)]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    use MassPrunable;
    use SoftDeletes;

    /** Silinen kayıt bu kadar gün sonra kalıcı silinir (KVKK saklama kuralı). */
    public const SAKLAMA_GUNU = 90;

    protected $fillable = [
        'firma_no',
        'hizmet_alan_isyeri_unvani',
        'gorunur_unvani',
        'faaliyet_id',
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

    protected $attributes = [
        'sozlesme_onay_durumu' => false,
    ];

    protected function casts(): array
    {
        return [
            'calisan_sayisi' => 'integer',
            'tehlike_sinifi' => TehlikeSinifi::class,
            'fatura_tipi' => FaturaTipi::class,
            'fatura_durumu' => FaturaDurumu::class,
            'odeme_durumu' => OdemeDurumu::class,
            'tutar' => 'decimal:2',
            'sozlesme_onay_durumu' => 'boolean',
            'firma_ziyareti' => 'date',
        ];
    }

    /** @return BelongsTo<Faaliyet, $this> */
    public function faaliyet(): BelongsTo
    {
        return $this->belongsTo(Faaliyet::class);
    }

    /** @return HasMany<CustomerAudit, $this> */
    public function audits(): HasMany
    {
        return $this->hasMany(CustomerAudit::class)->latest('id');
    }

    /** @return Builder<Customer> */
    public function prunable(): Builder
    {
        return static::onlyTrashed()->where('deleted_at', '<=', now()->subDays(self::SAKLAMA_GUNU));
    }
}
