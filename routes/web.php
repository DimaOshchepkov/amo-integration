<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AmoCrmOAuthController;


Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});


Route::get('/amocrm/connect', [AmoCrmOAuthController::class, 'connect'])->name('amocrm.connect');
Route::get('/amocrm/callback', [AmoCrmOAuthController::class, 'callback'])->name('amocrm.callback');
Route::post('/amocrm/callback', [AmoCrmOAuthController::class, 'callback'])->name('amocrm.callback.post');

require __DIR__.'/settings.php';
