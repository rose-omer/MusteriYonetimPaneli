<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Livewire\ActivityManager;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Auth gereksinimi olanlar
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 🚫 Guest rolü erişemesin
Route::middleware(['auth'])->group(function () {
    Route::get('/customers/import', function () {
        if (Auth::user()->role === 'guest') {
            return redirect()->route('unauthorized');
        }
        return app(CustomerController::class)->showImportForm();
    })->name('customers.import.form');

    Route::post('/customers/import', function () {
        if (Auth::user()->role === 'guest') {
            return redirect()->route('unauthorized');
        }
        return app(CustomerController::class)->import(request());
    })->name('customers.import');

    Route::get('/customers/{customer}/edit', function (Customer $customer) {
        if (Auth::user()->role === 'guest') {
            return redirect()->route('unauthorized');
        }
        return app(CustomerController::class)->edit($customer);
    })->name('customers.edit');

    Route::put('/customers/{customer}', function (Request $request, Customer $customer) {
        if (Auth::user()->role === 'guest') {
            return redirect()->route('unauthorized');
        }
        return app(CustomerController::class)->update($request, $customer);
    })->name('customers.update');

    Route::delete('/customers/{customer}', function (Customer $customer) {
        if (Auth::user()->role === 'guest') {
            return redirect()->route('unauthorized');
        }
        return app(CustomerController::class)->destroy($customer);
    })->name('customers.destroy');
});

// Diğer routes bozulmadan bırakıldı
Route::resource('customers', CustomerController::class)->except([
    'edit', 'update', 'destroy' // bunlar üstte özel kontrolle tanımlandı
]);

Route::get('/activities/manage', [CustomerController::class, 'manageActivities'])->name('activities.manage');


// Yetkisiz sayfası
Route::get('/unauthorized', function () {
    return view('errors.unauthorized');
})->name('unauthorized');

require __DIR__.'/auth.php';
