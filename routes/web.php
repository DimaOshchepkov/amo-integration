<?php

use App\Http\Controllers\AmoCrmOAuthController;
use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::post('/lead', [LeadController::class, 'store'])->name('lead.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/amocrm/connect', [AmoCrmOAuthController::class, 'connect'])->name('amocrm.connect');

Route::get('/amocrm/callback', [AmoCrmOAuthController::class, 'handleRedirectCallback'])->name('amocrm.callback');

require __DIR__.'/settings.php';
