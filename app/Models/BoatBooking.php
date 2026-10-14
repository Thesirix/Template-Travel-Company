<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Demande de traversée en bateau : trajet, passagers, véhicule éventuel.
 * Son statut suit la réservation auprès de la compagnie : à traiter (pas encore de réservation),
 * confirmée, vendue ou bon d'échange.
 */
class BoatBooking extends Model
{
    public const STATUSES = [
        'pending' => 'À traiter',
        'confirmer' => 'Confirmées',
        'final' => 'Vendues',
        'echange' => "Bons d'échange",
    ];

    /** Catégories de passagers : [libellé, âge minimum, âge maximum] */
    public const PASSENGER_TYPES = [
        'adulte' => ['Adulte', 25, 54],
        'senior' => ['Senior', 55, 105],
        'jeune' => ['Jeune', 11, 24],
        'enfant' => ['Enfant', 2, 10],
        'bebe' => ['Bebe', 0, 1],
    ];

    protected $table = 'boat_bookings';

    protected $fillable = ['trajet', 'insurance_id', 'destination_id', 'departure_port_id', 'arrival_port_id', 'company_id',
        'accommodation_id', 'fare_id', 'date_aller', 'date_retour', 'nb_adulte', 'nb_senior', 'nb_jeune', 'nb_enfant', 'nb_bebe',
        'vehicle_id', 'vehicle_size_id', 'vehicle_insurance_id', 'voiture_marque', 'voiture_modele', 'voiture_matricule',
        'voiture_anne', 'carte_grise', 'note', 'gent_id'];

    public const UPDATED_AT = null;

    public function destination()
    {
        return $this->belongsTo(BoatDestination::class, 'destination_id');
    }

    public function departurePort()
    {
        return $this->belongsTo(BoatDeparturePort::class, 'departure_port_id');
    }

    public function arrivalPort()
    {
        return $this->belongsTo(BoatArrivalPort::class, 'arrival_port_id');
    }

    public function company()
    {
        return $this->belongsTo(BoatCompany::class, 'company_id');
    }

    public function accommodation()
    {
        return $this->belongsTo(BoatAccommodation::class, 'accommodation_id');
    }

    public function fare()
    {
        return $this->belongsTo(BoatFare::class, 'fare_id');
    }

    public function insurance()
    {
        return $this->belongsTo(BoatPassengerInsurance::class, 'insurance_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(BoatVehicle::class, 'vehicle_id');
    }

    public function vehicleSize()
    {
        return $this->belongsTo(BoatVehicleSize::class, 'vehicle_size_id');
    }

    public function vehicleInsurance()
    {
        return $this->belongsTo(BoatVehicleInsurance::class, 'vehicle_insurance_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'gent_id');
    }

    public function passengers()
    {
        return $this->hasMany(BoatPassenger::class, 'booking_id');
    }

    public function reservation()
    {
        return $this->hasOne(BoatReservation::class, 'booking_id');
    }

    /** Réservations d'un statut : « pending » (sans réservation), « confirmer », « final », « echange » */
    public function scopeInStatus(Builder $query, string $status): Builder
    {
        return $status === 'pending'
            ? $query->whereDoesntHave('reservation')
            : $query->whereHas('reservation', fn ($q) => $q->where('etat', $status));
    }

    public function status(): string
    {
        return $this->reservation?->etat ?? 'pending';
    }

    public function isRoundTrip(): bool
    {
        return $this->trajet === 'aller-retour';
    }

    public function hasVehicle(): bool
    {
        return (bool) $this->vehicle_id;
    }

    public function vehicleDocumentUrl(): ?string
    {
        return $this->carte_grise ? Storage::disk('public')->url($this->carte_grise) : null;
    }

    /** « 2A 1S 0J 1E 0B » */
    public function passengerSummary(): string
    {
        return "{$this->nb_adulte}A {$this->nb_senior}S {$this->nb_jeune}J {$this->nb_enfant}E {$this->nb_bebe}B";
    }
}
