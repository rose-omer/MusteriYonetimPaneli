<?php

use App\Exports\CustomerTemplateExport;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

// Bütün ekranlar Filament panelinde (/admin). Panel kendi oturum ve Policy kontrolünü yapar.
Route::redirect('/', '/admin');

// Eski adresler panele yönlenir; panel oturumsuz isteği giriş sayfasına gönderir.
Route::redirect('/dashboard', '/admin');
Route::redirect('/customers', '/admin/customers');
Route::redirect('/activities/manage', '/admin/faaliyetler');

Route::middleware('auth')->group(function () {
    Route::get('/sablon/musteri-listesi', fn () => Excel::download(new CustomerTemplateExport, 'ornek-musteri-listesi.xlsx'))
        ->middleware('can:import,'.Customer::class)
        ->name('customers.import.template');
});
