<?php

use App\Http\Controllers\AmoCrmOAuthController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/amocrm/connect', [AmoCrmOAuthController::class, 'connect'])->name('amocrm.connect');

Route::get('/amocrm/callback', [AmoCrmOAuthController::class, 'handleRedirectCallback'])->name('amocrm.callback');
Route::post('/amocrm/callback', [AmoCrmOAuthController::class, 'handleWidgetCallback'])->name('amocrm.callback.widget');

require __DIR__.'/settings.php';
