<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

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
// web.php içinde bunu DEĞİŞTİR:
Route::get('/customers/import', [CustomerController::class, 'createImportForm'])->name('customers.import.form');

// Auth istemeyen routes
Route::resource('customers', CustomerController::class);
Route::get('/activities/manage', [CustomerController::class, 'manageActivities'])->name('activities.manage');
Route::get('/customers/import', [CustomerController::class, 'showImportForm'])->name('customers.import.form');
Route::post('/customers/import', [CustomerController::class, 'import'])->name('customers.import');

require __DIR__.'/auth.php';
