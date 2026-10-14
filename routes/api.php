<?php

/*
|---------------------------------------------------------------------------
| API du site public (/api) : comptes clients, vols, devis et paiement en ligne.
| Réponses JSON, clients authentifiés par JWT (garde « client »). Noms des routes : api.*
|---------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AirportController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FlightController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\QuoteController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {

    // Inscription (code par e-mail) et connexion
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('otp', 'sendOtp')->name('auth.otp');
        Route::post('otp/verify', 'verifyOtp')->name('auth.otp.verify');
        Route::post('login', 'login')->name('auth.login');
        Route::post('logout', 'logout')->middleware('client.auth')->name('auth.logout');
    });

    // Mot de passe oublié
    Route::prefix('password-reset')->controller(PasswordResetController::class)->group(function () {
        Route::post('otp', 'sendOtp')->name('password-reset.otp');
        Route::post('otp/verify', 'verifyOtp')->name('password-reset.otp.verify');
    });

    // Compte du client connecté
    Route::prefix('me')->middleware('client.auth')->controller(AccountController::class)->group(function () {
        Route::get('/', 'show')->name('me');
        Route::post('/', 'update')->name('me.update');
    });

    // Catalogue : aéroports et vols d'une destination
    Route::post('airports/search', [AirportController::class, 'search'])->name('airports.search');
    Route::prefix('destinations/{destination}/flights')->controller(FlightController::class)->group(function () {
        Route::get('/', 'index')->name('flights.index');
        Route::post('search', 'search')->name('flights.search');
        Route::get('{flight}', 'show')->whereNumber('flight')->name('flights.show');
    });

    // Demande de devis
    Route::post('quotes', [QuoteController::class, 'store'])->name('quotes.store');

    // Paiement en ligne (Paybox) : paramètres signés, retours navigateur et notification serveur
    Route::prefix('payments')->controller(PaymentController::class)->group(function () {
        Route::post('/', 'store')->middleware('client.auth')->name('payments.store');
        Route::get('confirmed', 'confirmed')->name('payments.confirmed');
        Route::get('canceled', 'canceled')->name('payments.canceled');
        Route::get('refused', 'refused')->name('payments.refused');
        Route::match(['get', 'post'], 'notify', 'notify')->name('payments.notify');
    });
});
