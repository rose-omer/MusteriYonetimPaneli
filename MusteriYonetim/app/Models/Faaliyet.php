<?php

namespace App\Models;

use Database\Factories\FaaliyetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $isim
 * @property-read int|null $customers_count
 */
class Faaliyet extends Model
{
    /** @use HasFactory<FaaliyetFactory> */
    use HasFactory;

    protected $fillable = ['isim'];

    /** @return HasMany<Customer, $this> */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
