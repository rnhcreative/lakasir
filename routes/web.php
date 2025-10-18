<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrinterController;
use App\Livewire\Forms\Auth\RegisterTenantForm;

Volt::route('/', 'pages/welcome');

Route::view('/offline', 'offline');

Route::get('/serviceworker.js', function () {
    return response()->file(public_path('serviceworker.js'))
        ->header('Content-Type', 'application/javascript');
});

Route::get('/auth/register', RegisterTenantForm::class)
    ->name('auth.register');

Route::get('/test/print/{sellingId}', [\App\Http\Controllers\UtilityController::class, 'print']);

Route::get('/signing', [PrinterController::class, 'signing'])
            ->can('read printer')
            ->name('api.printer.signing');

Route::middleware([
    'web',
])
    ->prefix('admin')
    ->group(function () {
        //
    });
