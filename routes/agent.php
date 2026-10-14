<?php

/*
|---------------------------------------------------------------------------
| Espace agent (/agent) : vente et suivi des vols, bateau, demandes clients, allotements, planning.
| Garde « gent ». Noms des routes : agent.*
|---------------------------------------------------------------------------
*/

use App\Http\Controllers\Agent\BlockPowerController;
use App\Http\Controllers\Agent\Boat\BookingController;
use App\Http\Controllers\Agent\Boat\BookingListController;
use App\Http\Controllers\Agent\CalendarController;
use App\Http\Controllers\Agent\ClientRequestController;
use App\Http\Controllers\Agent\DashboardController;
use App\Http\Controllers\Agent\ExtractionController;
use App\Http\Controllers\Agent\Flight\FileController;
use App\Http\Controllers\Agent\Flight\IssuanceController;
use App\Http\Controllers\Agent\Flight\PaymentController as FlightPaymentController;
use App\Http\Controllers\Agent\Flight\ReservationController;
use App\Http\Controllers\Agent\Flight\SaleController;
use App\Http\Controllers\Agent\NoteController;
use App\Http\Controllers\Agent\PaymentController as AgentPaymentController;
use App\Http\Controllers\Agent\PlanningController;
use App\Http\Controllers\Agent\ProcedureController;
use App\Http\Controllers\Agent\ProfileController;
use App\Http\Controllers\Agent\QuoteController;
use App\Http\Controllers\Agent\ScheduleController;
use App\Http\Controllers\Auth\AgentLoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('agent')->name('agent.')->group(function () {

    // Tableau de bord, profil et notes
    Route::middleware('auth:gent')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('home');
        Route::get('deconnexion', [AgentLoginController::class, 'logout'])->name('logout');
        Route::get('profil', [ProfileController::class, 'show'])->name('profil');
        Route::put('profil', [ProfileController::class, 'update'])->name('profil.update');
        Route::post('notes', [NoteController::class, 'store'])->name('notes.store');
        Route::post('notes/supprimer', [NoteController::class, 'destroy'])->name('notes.destroy');
    });

    // Calendrier personnel (congés, présences)
    Route::prefix('calendrier')->middleware('auth:gent')->controller(CalendarController::class)->group(function () {
        Route::get('evenements', 'events')->name('calendrier.events');
        Route::get('compteurs', 'counters')->name('calendrier.counters');
        Route::get('presences', 'attendances')->name('calendrier.attendances');
        Route::post('/', 'store')->name('calendrier.store');
        Route::post('modifier', 'update')->name('calendrier.update');
        Route::post('supprimer', 'destroy')->name('calendrier.destroy');
    });

    // Emploi du temps
    Route::prefix('emploi-du-temps')->middleware('auth:gent')->controller(ScheduleController::class)->group(function () {
        Route::get('evenements', 'events')->name('emploi.events');
        Route::post('/', 'store')->name('emploi.store');
        Route::post('modifier', 'update')->name('emploi.update');
        Route::post('supprimer', 'destroy')->name('emploi.destroy');
        Route::post('couleur', 'updateColor')->name('emploi.color');
    });

    // Planning de l'équipe (super agents)
    Route::prefix('planning')->middleware(['auth:gent', 'can:gerer Planning'])->controller(PlanningController::class)->group(function () {
        Route::get('/', 'index')->name('planning');
        Route::get('evenements', 'events')->name('planning.events');
        Route::get('agents', 'resources')->name('planning.resources');
        Route::get('statistiques', 'metrics')->name('planning.metrics');
        Route::get('compteurs', 'agentCounters')->name('planning.counters');
        Route::post('compteurs', 'updateCounters')->name('planning.counters.update');
        Route::get('presences', 'attendances')->name('planning.attendances');
        Route::post('demandes', 'store')->name('planning.store');
        Route::post('demandes/{id}/valider', 'approve')->name('planning.approve')->whereNumber('id');
        Route::post('demandes/{id}/refuser', 'reject')->name('planning.reject')->whereNumber('id');
        Route::post('demandes/{id}', 'update')->name('planning.update')->whereNumber('id');
        Route::delete('demandes/{id}', 'destroy')->name('planning.destroy')->whereNumber('id');
    });

    // Vols : vente
    Route::prefix('vols/{group}/vente')->middleware('auth:gent')->controller(SaleController::class)->group(function () {
        Route::get('/', 'index')->name('vols.vente')->whereNumber('group');
        Route::get('filtre', 'filter')->name('vols.vente.filter')->whereNumber('group');
        Route::get('verification-passager', 'checkPassenger')->name('vols.vente.check-passenger')->whereNumber('group');
        Route::get('inscription', 'create')->name('vols.vente.create')->whereNumber('group');
        Route::post('inscription', 'store')->name('vols.vente.store')->whereNumber('group');
        Route::get('confirmation/{reservation}', 'confirmation')->name('vols.vente.confirmation')->whereNumber(['group', 'reservation']);
        Route::get('contrat/{reservation}', 'contract')->name('vols.vente.contrat')->whereNumber(['group', 'reservation']);
        Route::get('e-billet/{reservation}', 'eTicket')->name('vols.vente.e-billet')->whereNumber(['group', 'reservation']);
    });

    // Vols : réservations (recherche, modification, annulation)
    Route::prefix('vols/{group}/reservations')->middleware('auth:gent')->controller(ReservationController::class)->group(function () {
        Route::get('/', 'index')->name('vols.reservations')->whereNumber('group');
        Route::get('changer-vol/apercu', 'previewFlightChange')->name('vols.reservations.change-flight.preview')->whereNumber('group');
        Route::post('changer-vol', 'applyFlightChange')->name('vols.reservations.change-flight.apply')->whereNumber('group');
        Route::get('{reservation}/changer-vol', 'changeFlight')->name('vols.reservations.change-flight')->whereNumber(['group', 'reservation']);
        Route::post('enregistrer', 'update')->name('vols.reservations.update')->whereNumber('group');
        Route::post('annuler-passager', 'cancelPassenger')->name('vols.reservations.cancel-passenger')->whereNumber('group');
        Route::post('annuler', 'cancel')->name('vols.reservations.cancel')->whereNumber('group');
        Route::post('supprimer', 'destroy')->name('vols.reservations.destroy')->whereNumber('group');
        Route::post('supprimer-passeport', 'destroyPassportImage')->name('vols.reservations.passport.destroy')->whereNumber('group');
        Route::get('{reservation}', 'show')->name('vols.reservations.show')->whereNumber(['group', 'reservation']);
    });

    // Vols : paiements d'une réservation
    Route::prefix('vols/{group}/paiements')->middleware('auth:gent')->controller(FlightPaymentController::class)->group(function () {
        Route::post('/', 'store')->name('vols.paiements.store')->whereNumber('group');
        Route::post('modifier', 'update')->name('vols.paiements.update')->whereNumber('group');
        Route::post('supprimer', 'destroy')->name('vols.paiements.destroy')->whereNumber('group');
    });

    // Vols : gestion des dossiers (agence principale)
    Route::prefix('vols/{group}/dossiers')->middleware(['auth:gent', 'agency.partner'])->controller(FileController::class)->group(function () {
        Route::get('/', 'index')->name('vols.dossiers')->whereNumber('group');
        Route::post('enregistrer', 'update')->name('vols.dossiers.update')->whereNumber('group');
        Route::get('{reservation}', 'show')->name('vols.dossiers.show')->whereNumber(['group', 'reservation']);
    });

    // Vols : émission des billets (agence principale)
    Route::prefix('vols/{group}/emission')->middleware(['auth:gent', 'agency.partner'])->controller(IssuanceController::class)->group(function () {
        Route::get('/', 'index')->name('vols.emission')->whereNumber('group');
        Route::get('vols/{flight}', 'flight')->name('vols.emission.flight')->whereNumber(['group', 'flight']);
        Route::get('reservations/{reservation}', 'reservation')->name('vols.emission.reservation')->whereNumber(['group', 'reservation']);
        Route::post('reservations/infos', 'updateReservation')->name('vols.emission.reservation.update')->whereNumber('group');
        Route::post('reservations/passagers', 'updatePassengers')->name('vols.emission.passengers.update')->whereNumber('group');
        Route::post('verifier-passager', 'verifyPassenger')->name('vols.emission.verify')->whereNumber('group');
        Route::post('export', 'export')->name('vols.emission.export')->whereNumber('group');
        Route::post('export/{format}', 'export')->name('vols.emission.export.format')->whereNumber('group')->whereIn('format', ['amadeus', 'ethiopian', 'volotea']);
    });

    // Bateau : demandes de traversée, réservation auprès de la compagnie, vente ou bon d'échange
    Route::prefix('bateau')->middleware(['auth:gent', 'can:gerer Bateau'])->group(function () {
        Route::get('a-traiter', [BookingListController::class, 'index'])->name('bateau.pending')->defaults('status', 'pending');
        Route::get('confirmees', [BookingListController::class, 'index'])->name('bateau.confirmed')->defaults('status', 'confirmer');
        Route::get('vendues', [BookingListController::class, 'index'])->name('bateau.sold')->defaults('status', 'final');
        Route::get('bons-echange', [BookingListController::class, 'index'])->name('bateau.vouchers')->defaults('status', 'echange');
        Route::get('export', [BookingListController::class, 'export'])->name('bateau.export');

        Route::controller(BookingController::class)->group(function () {
            Route::get('inscription', 'create')->name('bateau.create');
            Route::post('inscription', 'store')->name('bateau.store');
            Route::get('destinations/{boatDestination}/catalogue', 'catalog')->name('bateau.catalog')->whereNumber('boatDestination');
            Route::post('passagers/{passenger}/supprimer', 'destroyPassenger')->name('bateau.passenger.destroy')->whereNumber('passenger');
            Route::get('{booking}', 'show')->name('bateau.show')->whereNumber('booking');
            Route::get('{booking}/modifier', 'edit')->name('bateau.edit')->whereNumber('booking');
            Route::post('{booking}/modifier', 'update')->name('bateau.update')->whereNumber('booking');
            Route::get('{booking}/reserver', 'reserveForm')->name('bateau.reserve-form')->whereNumber('booking');
            Route::post('{booking}/reserver', 'reserve')->name('bateau.reserve')->whereNumber('booking');
            Route::post('{booking}/suivi', 'finalize')->name('bateau.finalize')->whereNumber('booking');
            Route::post('{booking}/supprimer', 'destroy')->name('bateau.destroy')->whereNumber('booking');
        });
    });

    // Demandes clients et devis
    Route::prefix('demandes')->middleware(['auth:gent', 'can:gerer Clients'])->controller(ClientRequestController::class)->group(function () {
        Route::get('nouvelle', 'create')->name('demandes.create');
        Route::get('referentiel', 'referenceData')->name('demandes.reference-data');
        Route::post('/', 'store')->name('demandes.store');
        Route::get('clients/recherche', 'searchClient')->name('demandes.clients.search');
        Route::post('clients', 'storeClient')->name('demandes.clients.store');
        Route::put('clients/{client}', 'updateClient')->name('demandes.clients.update')->whereNumber('client');
        Route::post('devis', 'storeQuote')->name('demandes.devis.store');
        Route::post('devis/sms', 'sendQuoteSms')->name('demandes.devis.sms');
    });

    // Devis
    Route::prefix('devis')->middleware(['auth:gent', 'can:gerer devis'])->controller(QuoteController::class)->group(function () {
        Route::get('/', 'index')->name('devis');
    });

    // Paiements en ligne
    Route::prefix('paiements')->middleware(['auth:gent', 'can:gerer Payer'])->controller(AgentPaymentController::class)->group(function () {
        Route::get('/', 'index')->name('paiements');
        Route::post('/', 'pay')->name('paiements.pay');
    });

    // Extractions de contacts
    Route::prefix('extractions')->middleware(['auth:gent', 'can:gerer Extraction'])->controller(ExtractionController::class)->group(function () {
        Route::get('/', 'index')->name('extractions');
        Route::post('export', 'export')->name('extractions.export');
    });

    // Procédures internes
    Route::prefix('procedures')->middleware(['auth:gent', 'can:gerer Procedure'])->controller(ProcedureController::class)->group(function () {
        Route::get('/', 'index')->name('procedures');
        Route::get('{procedure}', 'show')->name('procedures.show')->whereNumber('procedure');
        Route::get('{procedure}/telecharger', 'download')->name('procedures.download')->whereNumber('procedure');
    });

    // Allotements : options de places auprès des compagnies, passagers, confirmation, émission ; demandes de groupes
    Route::prefix('allotements')->middleware(['auth:gent', 'can:gerer Block power'])->controller(BlockPowerController::class)->group(function () {
        Route::get('/', 'index')->name('block-power');
        Route::get('guide', 'guide')->name('block-power.guide');
        Route::get('options/nouvelle', 'create')->name('block-power.create');
        Route::post('options', 'store')->name('block-power.store');
        Route::get('options/{option}', 'show')->name('block-power.show')->whereNumber('option');
        Route::get('options/{option}/modifier', 'edit')->name('block-power.edit')->whereNumber('option');
        Route::post('options/{option}/modifier', 'update')->name('block-power.update')->whereNumber('option');
        Route::get('options/{option}/passagers', 'passengersForm')->name('block-power.passengers')->whereNumber('option');
        Route::post('options/{option}/passagers', 'storePassengers')->name('block-power.passengers.store')->whereNumber('option');
        Route::post('passagers/{passenger}/supprimer', 'destroyPassenger')->name('block-power.passengers.destroy')->whereNumber('passenger');
        Route::get('options/{option}/confirmer', 'confirmForm')->name('block-power.confirm-form')->whereNumber('option');
        Route::post('options/{option}/confirmer', 'confirm')->name('block-power.confirm')->whereNumber('option');
        Route::post('options/{option}/relacher', 'release')->name('block-power.release')->whereNumber('option');
        Route::post('options/{option}/emettre', 'issue')->name('block-power.issue')->whereNumber('option');
        Route::get('demandes', 'requests')->name('block-power.requests');
        Route::get('demandes/nouvelle', 'createRequest')->name('block-power.requests.create');
        Route::post('demandes', 'storeRequest')->name('block-power.requests.store');
        Route::post('demandes/{blockPowerRequest}/supprimer', 'destroyRequest')->name('block-power.requests.destroy')->whereNumber('blockPowerRequest');
    });
});
