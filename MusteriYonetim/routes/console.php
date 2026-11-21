<?php

use App\Models\Customer;
use App\Models\CustomerAudit;
use Illuminate\Support\Facades\Schedule;

// KVKK saklama kuralı: 90 günden eski silinmiş müşteri kaydı kalıcı silinir.
// Sonra kalıcı silinen müşterinin ve 2 yıldan eski denetim kayıtları silinir.
Schedule::command('model:prune', ['--model' => [Customer::class, CustomerAudit::class]])->dailyAt('03:00');
