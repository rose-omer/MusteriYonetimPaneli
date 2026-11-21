<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\CustomerAudit;
use App\Support\TurkishFormat;

class CustomerObserver
{
    /** Denetim kaydına yazılmayan teknik alanlar. */
    private const IGNORED = ['created_at', 'updated_at', 'deleted_at'];

    /** KVKK: bu alanların değeri denetim kaydına yalnız maskeli yazılır; ikinci açık kopya oluşmaz. */
    private const MASKED = ['telefon_numarasi'];

    public function created(Customer $customer): void
    {
        CustomerAudit::record('created', $customer);
    }

    public function updated(Customer $customer): void
    {
        $changes = [];

        foreach (array_keys($customer->getChanges()) as $field) {
            if (in_array($field, self::IGNORED, true)) {
                continue;
            }

            $changes[$field] = [
                'old' => $this->plain($field, $customer->getOriginal($field)),
                'new' => $this->plain($field, $customer->getAttribute($field)),
            ];
        }

        if ($changes !== []) {
            CustomerAudit::record('updated', $customer, $changes);
        }
    }

    public function deleted(Customer $customer): void
    {
        CustomerAudit::record('deleted', $customer);
    }

    public function restored(Customer $customer): void
    {
        CustomerAudit::record('restored', $customer);
    }

    private function plain(string $field, mixed $value): mixed
    {
        return match (true) {
            in_array($field, self::MASKED, true) => TurkishFormat::maskPhone($value === null ? null : (string) $value),
            $value instanceof \BackedEnum => $value->value,
            $value instanceof \DateTimeInterface => $value->format('Y-m-d'),
            default => $value,
        };
    }
}
