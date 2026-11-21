<?php

namespace App\Models;

use App\Enums\FaturaDurumu;
use App\Enums\FaturaTipi;
use App\Enums\OdemeDurumu;
use App\Enums\TehlikeSinifi;
use App\Imports\CustomerRowMapper;
use App\Support\TurkishFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Müşteri kaydına yapılan işlemlerin erişim kaydı (KVKK).
 *
 * @property int $id
 * @property int|null $customer_id
 * @property int|null $user_id
 * @property string $action
 * @property array<string, mixed>|null $changed_fields
 * @property Carbon $created_at
 * @property-read User|null $user
 */
class CustomerAudit extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    /** KVKK saklama kuralı: denetim kaydı bu kadar gün sonra kalıcı silinir. */
    public const SAKLAMA_GUNU = 730;

    protected $fillable = ['customer_id', 'user_id', 'action', 'changed_fields'];

    protected function casts(): array
    {
        return [
            'changed_fields' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Süresi dolan kayıtlar ve kalıcı silinmiş müşterinin kayıtları silinir.
     * İçe aktarım özeti müşteriye bağlı değildir; yalnız süre dolunca silinir.
     *
     * @return Builder<CustomerAudit>
     */
    public function prunable(): Builder
    {
        return CustomerAudit::query()
            ->where('created_at', '<=', now()->subDays(self::SAKLAMA_GUNU))
            ->orWhere(fn (Builder $query) => $query->whereNull('customer_id')->where('action', '!=', 'imported'));
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'created' => 'Oluşturdu',
            'updated' => 'Güncelledi',
            'deleted' => 'Sildi',
            'restored' => 'Geri aldı',
            'imported' => 'İçe aktardı',
            default => $this->action,
        };
    }

    /**
     * Değişen alanları okunur biçimde döndürür.
     *
     * @return list<array{label: string, old: string, new: string}>
     */
    public function changeLines(): array
    {
        $labels = [
            ...CustomerRowMapper::attributeLabels(),
            'faaliyet_id' => 'Faaliyet',
            'telefon_numarasi' => 'Telefon',
            'hizmet_alan_isyeri_unvani' => 'Unvan',
            'calisan_sayisi' => 'Çalışan sayısı',
            'tehlike_sinifi' => 'Tehlike sınıfı',
            'il' => 'İl',
            'konum_url' => 'Konum',
            'odeme_durumu' => 'Ödeme durumu',
        ];
        $enums = [
            'tehlike_sinifi' => TehlikeSinifi::class,
            'fatura_tipi' => FaturaTipi::class,
            'fatura_durumu' => FaturaDurumu::class,
            'odeme_durumu' => OdemeDurumu::class,
        ];

        $lines = [];
        foreach ($this->changed_fields ?? [] as $field => $change) {
            if (! is_array($change) || ! array_key_exists('new', $change)) {
                continue;
            }

            $format = function (mixed $value) use ($field, $enums): string {
                if ($value === null || $value === '') {
                    return 'boş';
                }
                if ($field === 'telefon_numarasi') {
                    // Kayıt zaten maskeli yazılır; eski (maskesiz) kayıt da ekranda maskelenir.
                    return str_contains((string) $value, '*') ? (string) $value : (TurkishFormat::maskPhone((string) $value) ?? 'boş');
                }
                if ($field === 'faaliyet_id') {
                    return Faaliyet::find($value)->isim ?? "#{$value}";
                }
                if ($field === 'tutar' && is_numeric($value)) {
                    return TurkishFormat::tl($value, 2);
                }
                if ($field === 'sozlesme_onay_durumu') {
                    return $value ? 'Onaylandı' : 'Onaylanmadı';
                }
                if (isset($enums[$field])) {
                    return $enums[$field]::tryFrom((string) $value)?->getLabel() ?? (string) $value;
                }

                return is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE);
            };

            $lines[] = [
                'label' => $labels[$field] ?? $field,
                'old' => $format($change['old'] ?? null),
                'new' => $format($change['new']),
            ];
        }

        return $lines;
    }

    /** @param  array<string, mixed>|null  $changes */
    public static function record(string $action, ?Customer $customer = null, ?array $changes = null): self
    {
        return self::create([
            'customer_id' => $customer?->getKey(),
            'user_id' => auth()->id(),
            'action' => $action,
            'changed_fields' => $changes,
        ]);
    }
}
