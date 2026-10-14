<?php

/*
|---------------------------------------------------------------------------
| Pages publiques : portail d'accueil et retours de paiement Paybox.
| Les espaces agent et administration sont dans routes/agent.php et routes/admin.php.
|---------------------------------------------------------------------------
*/

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PayboxController;
use Illuminate\Support\Facades\Route;

// Connexion unique : l'identifiant mène à l'administration ou à l'espace agent
Route::redirect('/', '/connexion')->name('home');
Route::get('connexion', [LoginController::class, 'show'])->name('login');
Route::post('connexion', [LoginController::class, 'login'])->name('login.submit');

// Langue de l'interface (anglais ou français)
Route::get('langue/{locale}', LocaleController::class)->name('locale')->whereIn('locale', ['en', 'fr']);

// Paiement Paybox (retours de la banque)
Route::prefix('paybox')->middleware('auth:gent')->controller(PayboxController::class)->group(function () {
    Route::get('accepted', 'accepted')->name('paybox.accepted');
    Route::get('waiting', 'waiting')->name('paybox.waiting');
    Route::get('refused', 'refused')->name('paybox.refused');
    Route::get('aborted', 'aborted')->name('paybox.aborted');
});
