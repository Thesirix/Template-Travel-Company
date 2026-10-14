<?php

namespace App\Services;

use App\Enums\FlightType;
use App\Models\Flight;
use App\Models\FlightReservation;
use Illuminate\Support\Collection;

/**
 * Tableaux de la page de vente d'une destination : meilleurs vendeurs, meilleures agences,
 * remplissage par ville. Une réservation sur deux vols compte ses passagers deux fois (un siège par vol).
 */
class FlightSalesStatistics
{
    /** Totaux et classement des agents : { totalReservations, totalReservedAdults…, agents: [{ agentName, numAdults… }] } */
    public function byAgent(int $groupId): ?object
    {
        $rows = $this->activeReservations($groupId)->groupBy('gent_id')
            ->map(fn (Collection $reservations) => $this->count($reservations, ['agentName' => $reservations->first()->gent?->prenom ?? '—']))
            ->sortByDesc('totalNumber')->values();
        if ($rows->isEmpty()) {
            return null;
        }

        return (object) [
            'totalReservations' => $rows->sum('totalNumber'),
            'totalReservedAdults' => $rows->sum('numAdults'),
            'totalReservedChildren' => $rows->sum('numChildren'),
            'totalReservedBabies' => $rows->sum('numBabies'),
            'agents' => $rows->all(),
        ];
    }

    /** Classement des agences : [{ agencyName, numAdults, numChildren, numBabies, totalNumber }] */
    public function byAgency(int $groupId): array
    {
        return $this->activeReservations($groupId)->groupBy(fn ($reservation) => $reservation->gent?->agence_id)
            ->map(fn (Collection $reservations) => $this->count($reservations, ['agencyName' => $reservations->first()->gent?->agence?->name ?? '—']))
            ->sortByDesc('totalNumber')->values()->all();
    }

    /** Places réservées / restantes par ville de destination, à l'aller et au retour */
    public function byCity(int $groupId): array
    {
        $flights = Flight::where('vol_group_id', $groupId)->with(['escaliers.airPortDe.ville', 'escaliers.airPortA.ville'])->get();
        $cities = [];
        foreach ($flights as $flight) {
            $outbound = $flight->escaliers->where('type', 0);
            $airport = $flight->type_voyage === FlightType::RETURN->value ? $outbound->first()?->airPortDe : $outbound->last()?->airPortA;
            $city = $airport?->ville?->nom ?? $flight->destination ?? '—';
            $cities[$city] ??= ['aller' => [0, 0, 0], 'retour' => [0, 0, 0]];

            $legs = match ($flight->type_voyage) {
                FlightType::OUTBOUND->value => ['aller'],
                FlightType::RETURN->value => ['retour'],
                default => ['aller', 'retour'],
            };
            foreach ($legs as $leg) {
                $cities[$city][$leg][0] += $flight->place_reserver;
                $cities[$city][$leg][1] += $flight->place_disponible;
                $cities[$city][$leg][2] += $flight->place_total;
            }
        }

        $rate = fn (int $part, int $total) => $total > 0 ? round($part / $total * 100, 2) : 0.0;
        $rows = [];
        foreach ($cities as $name => ['aller' => [$outReserved, $outRest, $outTotal], 'retour' => [$backReserved, $backRest, $backTotal]]) {
            $reserved = $outReserved + $backReserved;
            $rest = $outRest + $backRest;
            $rows[] = (object) [
                'cityName' => $name,
                'allerReservations' => $outReserved, 'allerRest' => $outRest, 'allerTotal' => $outTotal,
                'allerReservationsRate' => $rate($outReserved, $outTotal), 'allerRestRate' => $rate($outRest, $outTotal),
                'returnReservations' => $backReserved, 'returnRest' => $backRest, 'returnTotal' => $backTotal,
                'returnReservationsRate' => $rate($backReserved, $backTotal), 'returnRestRate' => $rate($backRest, $backTotal),
                'globalReservations' => $reserved, 'globalRest' => $rest, 'globalTotal' => $reserved + $rest,
                'globalReservationsRate' => $rate($reserved, $reserved + $rest), 'globalRestRate' => $rate($rest, $reserved + $rest),
            ];
        }

        return $rows;
    }

    private function activeReservations(int $groupId): Collection
    {
        return FlightReservation::with('gent.agence')->where('status', 1)
            ->where(fn ($q) => $q->whereHas('vol', fn ($v) => $v->where('vol_group_id', $groupId))
                ->orWhereHas('volRetour', fn ($v) => $v->where('vol_group_id', $groupId)))
            ->get();
    }

    private function count(Collection $reservations, array $label): object
    {
        $sum = fn (string $column) => (int) $reservations->sum(fn ($r) => $r->$column * ($r->vol_retour_id ? 2 : 1));
        $adults = $sum('nb_adult');
        $children = $sum('nb_enfant');
        $babies = $sum('nb_bebe');

        return (object) ($label + ['numAdults' => $adults, 'numChildren' => $children, 'numBabies' => $babies, 'totalNumber' => $adults + $children + $babies]);
    }
}
