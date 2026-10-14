<?php

/*
|---------------------------------------------------------------------------
| Espace administration (/admin) : catalogue, vols, agents, agences et réglages de l'agence.
| Garde « gestion ». Noms des routes : admin.*
|---------------------------------------------------------------------------
*/

use App\Http\Controllers\Admin\AgencyController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\AirportController;
use App\Http\Controllers\Admin\BlockPowerController;
use App\Http\Controllers\Admin\BoatController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ConditionController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\FlightController;
use App\Http\Controllers\Admin\FlightGroupController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\InsuranceController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProcedureController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SeatController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Auth\AdminLoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {

    // Tableau de bord, profil et outils
    Route::middleware('auth:gestion')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('home');
        Route::get('deconnexion', [AdminLoginController::class, 'logout'])->name('logout');
        Route::get('profil', [ProfileController::class, 'show'])->name('profil');
        Route::put('profil', [ProfileController::class, 'update'])->name('profil.update');
        Route::post('profil/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('profil.password');
        Route::get('taches', [TaskController::class, 'index'])->name('taches');
        Route::post('taches', [TaskController::class, 'store'])->name('taches.store');
        Route::post('taches/supprimer', [TaskController::class, 'destroy'])->name('taches.destroy');
        Route::get('historique', [HistoryController::class, 'index'])->name('historique');
        Route::get('paiements', [PaymentController::class, 'index'])->name('paiements');
    });

    // Réglages de l'agence
    Route::middleware('auth:gestion')->group(function () {
        Route::get('parametres', [SettingsController::class, 'index'])->name('parametres');
        Route::post('parametres', [SettingsController::class, 'update'])->name('parametres.update');
        Route::get('destinations', [DestinationController::class, 'index'])->name('destinations');
        Route::post('destinations', [DestinationController::class, 'store'])->name('destinations.store');
        Route::put('destinations/{destination}', [DestinationController::class, 'update'])->name('destinations.update');
        Route::delete('destinations/{destination}', [DestinationController::class, 'destroy'])->name('destinations.destroy');
    });

    // Vols
    Route::prefix('vols/{destination}')->middleware('auth:gestion')->controller(FlightController::class)->group(function () {
        Route::get('/', 'index')->name('vols');
        Route::get('nouveau', 'create')->name('vols.create');
        Route::post('/', 'store')->name('vols.store');
        Route::get('{flight}/copier', 'copy')->name('vols.copy')->whereNumber('flight');
        Route::post('copier', 'storeCopy')->name('vols.copy.store');
        Route::post('modifier', 'update')->name('vols.update');
        Route::post('supprimer', 'destroy')->name('vols.destroy');
        Route::get('{flight}/modifier', 'edit')->name('vols.edit')->whereNumber('flight');
    });

    // Groupes de vols
    Route::prefix('groupes-vols')->middleware('auth:gestion')->controller(FlightGroupController::class)->group(function () {
        Route::get('/', 'index')->name('groupes-vols');
        Route::get('nouveau', 'create')->name('groupes-vols.create');
        Route::post('/', 'store')->name('groupes-vols.store');
        Route::post('modifier', 'update')->name('groupes-vols.update');
        Route::post('supprimer', 'destroy')->name('groupes-vols.destroy');
        Route::get('{group}/modifier', 'edit')->name('groupes-vols.edit')->whereNumber('group');
    });

    // Places des vols
    Route::prefix('places')->middleware('auth:gestion')->controller(SeatController::class)->group(function () {
        Route::get('/', 'index')->name('places');
        Route::post('/', 'update')->name('places.update');
        Route::get('{flight}', 'edit')->name('places.edit')->whereNumber('flight');
    });

    // Conditions de vente
    Route::prefix('conditions')->middleware('auth:gestion')->controller(ConditionController::class)->group(function () {
        Route::get('/', 'index')->name('conditions');
        Route::get('nouvelle', 'create')->name('conditions.create');
        Route::post('/', 'store')->name('conditions.store');
        Route::post('modifier', 'update')->name('conditions.update');
        Route::post('supprimer', 'destroy')->name('conditions.destroy');
        Route::get('{condition}/modifier', 'edit')->name('conditions.edit')->whereNumber('condition');
    });

    // Assurances
    Route::prefix('assurances')->middleware('auth:gestion')->controller(InsuranceController::class)->group(function () {
        Route::get('/', 'index')->name('assurances');
        Route::get('nouvelle', 'create')->name('assurances.create');
        Route::post('/', 'store')->name('assurances.store');
        Route::post('supprimer', 'destroy')->name('assurances.destroy');
        Route::get('{insurance}/modifier', 'edit')->name('assurances.edit')->whereNumber('insurance');
        Route::post('{insurance}', 'update')->name('assurances.update')->whereNumber('insurance');
    });

    // Bateau : catalogue
    Route::prefix('bateau')->middleware('auth:gestion')->controller(BoatController::class)->group(function () {
        Route::get('/', 'index')->name('bateau');
        Route::post('{catalog}', 'store')->name('bateau.store')->whereIn('catalog', array_keys(BoatController::CATALOGS));
        Route::post('{catalog}/supprimer', 'destroy')->name('bateau.destroy')->whereIn('catalog', array_keys(BoatController::CATALOGS));
    });

    // Allotements : réglages
    Route::prefix('allotements')->middleware('auth:gestion')->controller(BlockPowerController::class)->group(function () {
        Route::get('/', 'index')->name('block-power');
        Route::post('guide', 'storeGuide')->name('block-power.guide.store');
        Route::post('{catalog}', 'store')->name('block-power.store')->whereIn('catalog', array_keys(BlockPowerController::CATALOGS));
        Route::post('{catalog}/supprimer', 'destroy')->name('block-power.destroy')->whereIn('catalog', array_keys(BlockPowerController::CATALOGS));
    });

    // Procédures internes
    Route::prefix('procedures')->middleware('auth:gestion')->controller(ProcedureController::class)->group(function () {
        Route::get('/', 'index')->name('procedures');
        Route::get('nouvelle', 'create')->name('procedures.create');
        Route::post('/', 'store')->name('procedures.store');
        Route::post('supprimer', 'destroy')->name('procedures.destroy');
        Route::get('{procedure}/telecharger', 'download')->name('procedures.download')->whereNumber('procedure');
    });

    // Fichier clients
    Route::prefix('clients')->middleware('auth:gestion')->controller(ClientController::class)->group(function () {
        Route::get('/', 'index')->name('clients');
        Route::get('{client}', 'show')->name('clients.show')->whereNumber('client');
    });

    // Agents
    Route::prefix('agents')->middleware('auth:gestion')->controller(AgentController::class)->group(function () {
        Route::get('/', 'index')->name('agents');
        Route::get('nouveau', 'create')->name('agents.create');
        Route::post('/', 'store')->name('agents.store');
        Route::post('desactiver', 'toggle')->name('agents.toggle');
        Route::get('{agent}/modifier', 'edit')->name('agents.edit')->whereNumber('agent');
        Route::put('{agent}', 'update')->name('agents.update')->whereNumber('agent');
        Route::delete('{agent}', 'destroy')->name('agents.destroy')->whereNumber('agent');
        Route::get('{agent}/mot-de-passe', 'editPassword')->name('agents.password')->whereNumber('agent');
        Route::put('{agent}/mot-de-passe', 'updatePassword')->name('agents.password.update')->whereNumber('agent');
    });

    // Agences
    Route::prefix('agences')->middleware('auth:gestion')->controller(AgencyController::class)->group(function () {
        Route::get('/', 'index')->name('agences');
        Route::post('/', 'store')->name('agences.store');
        Route::get('{agency}/modifier', 'edit')->name('agences.edit')->whereNumber('agency');
        Route::put('{agency}', 'update')->name('agences.update')->whereNumber('agency');
    });

    // Catalogue : pays
    Route::prefix('pays')->middleware('auth:gestion')->controller(CountryController::class)->group(function () {
        Route::get('/', 'index')->name('pays');
        Route::post('/', 'store')->name('pays.store');
        Route::get('{country}/modifier', 'edit')->name('pays.edit')->whereNumber('country');
        Route::put('{country}', 'update')->name('pays.update')->whereNumber('country');
        Route::delete('{country}', 'destroy')->name('pays.destroy')->whereNumber('country');
    });

    // Catalogue : villes
    Route::prefix('villes')->middleware('auth:gestion')->controller(CityController::class)->group(function () {
        Route::get('/', 'index')->name('villes');
        Route::post('/', 'store')->name('villes.store');
        Route::get('{city}/modifier', 'edit')->name('villes.edit')->whereNumber('city');
        Route::put('{city}', 'update')->name('villes.update')->whereNumber('city');
        Route::delete('{city}', 'destroy')->name('villes.destroy')->whereNumber('city');
    });

    // Catalogue : aéroports
    Route::prefix('aeroports')->middleware('auth:gestion')->controller(AirportController::class)->group(function () {
        Route::get('/', 'index')->name('aeroports');
        Route::post('/', 'store')->name('aeroports.store');
        Route::get('{airport}/modifier', 'edit')->name('aeroports.edit')->whereNumber('airport');
        Route::put('{airport}', 'update')->name('aeroports.update')->whereNumber('airport');
        Route::delete('{airport}', 'destroy')->name('aeroports.destroy')->whereNumber('airport');
    });

    // Catalogue : compagnies
    Route::prefix('compagnies')->middleware('auth:gestion')->controller(CompanyController::class)->group(function () {
        Route::get('/', 'index')->name('compagnies');
        Route::post('/', 'store')->name('compagnies.store');
        Route::get('{company}/modifier', 'edit')->name('compagnies.edit')->whereNumber('company');
        Route::put('{company}', 'update')->name('compagnies.update')->whereNumber('company');
        Route::delete('{company}', 'destroy')->name('compagnies.destroy')->whereNumber('company');
    });
});
