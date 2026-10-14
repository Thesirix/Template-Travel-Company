<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Réservation de vol : un vol aller (vol_id), éventuellement un second vol pour le retour
 * (vol_retour_id, type « aller_retour_diffrent_vol »), ses passagers et ses acomptes.
 * Places : adultes et enfants occupent un siège, les bébés non.
 */
class FlightReservation extends Model
{
    protected $table = 'vol_reservations';

    protected $fillable = ['id', 'vol_id', 'status', 'vol_retour_id', 'type_reservation',
        'type_voyage', 'prix_total', 'prix_payed', 'a_compte', 'client_id',
        'nb_adult', 'nb_enfant', 'nb_bebe', 'gent_id', 'etat', 'note', 'code', 'created_at'];

    public $timestamps = false;

    public function passagers()
    {
        return $this->hasMany(FlightPassenger::class, 'reservation_id', 'id');
    }

    public function vol()
    {
        return $this->hasOne(Flight::class, 'id', 'vol_id');
    }

    public function volRetour()
    {
        return $this->hasOne(Flight::class, 'id', 'vol_retour_id');
    }

    public function gent()
    {
        return $this->hasOne(Agent::class, 'id', 'gent_id');
    }

    public function acomptes()
    {
        return $this->hasMany(FlightDeposit::class, 'reservation_id', 'id');
    }

    public function bill()
    {
        return $this->hasOne(FlightDeposit::class, 'reservation_id', 'id')->oldest('created_at');
    }

    public function annulations()
    {
        return $this->hasMany(FlightCancellation::class, 'reservation_id', 'id');
    }

    public function histories()
    {
        return $this->hasMany(FlightReservationHistory::class, 'reservation_id', 'id');
    }

    /** Sièges occupés sur chaque vol (les bébés n'en occupent pas) */
    public function seats(): int
    {
        return (int) $this->nb_adult + (int) $this->nb_enfant;
    }

    /** Vols dont la réservation occupe des places : aller, et vol retour séparé s'il y en a un */
    public function bookedFlights(): array
    {
        return array_values(array_filter([$this->vol, $this->type_voyage === 'aller_retour_diffrent_vol' ? $this->volRetour : null]));
    }

    public function leadPassenger(): ?FlightPassenger
    {
        return $this->passagers->firstWhere('type', 'adulte') ?? $this->passagers->first();
    }

    /** Nom de la destination (pays du groupe de vols), pour les assurances proposées */
    public function destinationName(): ?string
    {
        return $this->vol?->volGroup?->country?->nom;
    }

    /** « Ville de départ → ville d'arrivée » du trajet aller */
    public function routeLabel(): string
    {
        $stops = $this->vol?->escaliers->where('type', 0);
        if (! $stops || $stops->isEmpty()) {
            return (string) $this->vol?->destination;
        }

        return trim(($stops->first()->airPortDe?->ville?->nom ?? '') . ' → ' . ($stops->last()->airPortA?->ville?->nom ?? ''), ' →');
    }

    public function outboundDate(): ?Carbon
    {
        return $this->type_voyage === 'retour_simple' || ! $this->vol?->date_aller ? null : Carbon::parse($this->vol->date_aller);
    }

    public function returnDate(): ?Carbon
    {
        $date = match ($this->type_voyage) {
            'aller_retour_same_vol' => $this->vol?->date_retour,
            'aller_retour_diffrent_vol' => $this->volRetour?->date_aller,
            'retour_simple' => $this->vol?->date_aller,
            default => null,
        };

        return $date ? Carbon::parse($date) : null;
    }

    /** Durées des trajets aller et retour, pour le contrat et l'e-billet */
    public function durations(): array
    {
        $parse = fn ($time) => $time ? Carbon::parse($time) : null;

        return match ($this->type_voyage) {
            'aller_simple' => [$parse($this->vol?->duration_aller), null],
            'aller_retour_same_vol' => [$parse($this->vol?->duration_aller), $parse($this->vol?->duration_retour)],
            'aller_retour_diffrent_vol' => [$parse($this->vol?->duration_aller), $parse($this->volRetour?->duration_aller)],
            'retour_simple' => [null, $parse($this->vol?->duration_aller)],
            default => [null, null],
        };
    }

    /**
     * Frais d'annulation par passager selon le barème du vol :
     * le jour de l'inscription, à 45 jours ou plus du départ, entre 44 jours et la veille ;
     * null le jour du départ et après (le billet entier est dû).
     */
    public function cancellationFee(): ?float
    {
        $departure = Carbon::parse($this->vol->date_aller)->startOfDay();
        if ($this->created_at && Carbon::parse($this->created_at)->isToday()) {
            return (float) $this->vol->j_inscription;
        }
        $days = (int) Carbon::today()->diffInDays($departure, false);

        return match (true) {
            $days >= 45 => (float) $this->vol->j_45,
            $days >= 1 => (float) $this->vol->j_44_j_1,
            default => null,
        };
    }

    /** Prix de vente d'un passager (aller, plus retour sur vol séparé) */
    public function farePrice(string $type): float
    {
        $column = ['adulte' => 'prix_vente_adult', 'enfant' => 'prix_vente_enfant'][$type] ?? 'prix_vente_bebe';
        $price = (float) $this->vol->$column;
        if ($this->type_voyage === 'aller_retour_diffrent_vol' && $this->volRetour) {
            $price += (float) $this->volRetour->$column;
        }

        return $price;
    }

    /** Recalcule le prix total (billets + assurances) et le montant déjà payé (acomptes) */
    public function refreshTotals(): void
    {
        $this->load(['vol', 'volRetour', 'passagers.assurences', 'acomptes']);
        $this->prix_total = $this->passagers->sum(fn ($passenger) => $this->farePrice($passenger->type) + (float) ($passenger->assurences->prix ?? 0));
        $this->prix_payed = $this->acomptes->sum('montant');
        $this->save();
    }
}
