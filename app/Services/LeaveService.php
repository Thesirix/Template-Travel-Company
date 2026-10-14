<?php

namespace App\Services;

use App\Models\Agent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    /** Ouvertures manuelles par agent : [gent_id => [Y-m-d => ligne]] (chargées une fois par instance) */
    private array $openingsCache = [];

    /** Congés validés par agent : [gent_id => [[début, fin|null, jours_par_type], ...]] */
    private array $approvedCache = [];

    /**
     * Charge en deux requêtes les ouvertures et les congés validés de plusieurs agents
     * (au lieu de deux requêtes par agent), avant un calcul sur toute l'équipe.
     */
    public function preload(array $gentIds): void
    {
        $gentIds = array_values(array_diff($gentIds, array_keys($this->approvedCache)));
        if (! $gentIds) {
            return;
        }
        $openings = DB::table('leave_openings')->whereIn('gent_id', $gentIds)->orderBy('month_start')->get()->groupBy('gent_id');
        $approved = DB::table('calendars')->whereIn('gent_id', $gentIds)->where('status', 'approved')
            ->get(['gent_id', 'start_date', 'end_date', 'jours_par_type'])->groupBy('gent_id');
        foreach ($gentIds as $id) {
            $this->openingsCache[$id] = ($openings[$id] ?? collect())
                ->keyBy(fn ($row) => Carbon::parse($row->month_start)->toDateString())->all();
            $this->approvedCache[$id] = ($approved[$id] ?? collect())->map(fn ($ev) => $this->approvedRow($ev))->all();
        }
    }

    /** Ouverture manuelle d'un mois (ou null) */
    public function openingOf(int $gentId, Carbon $monthStart): ?object
    {
        return $this->openingsFor($gentId)[$monthStart->toDateString()] ?? null;
    }

    private function approvedRow(object $ev): array
    {
        return [
            Carbon::parse($ev->start_date),
            $ev->end_date ? Carbon::parse($ev->end_date) : null,
            $ev->jours_par_type ? json_decode($ev->jours_par_type, true) : null,
        ];
    }

    private function openingsFor(int $gentId): array
    {
        return $this->openingsCache[$gentId] ??= DB::table('leave_openings')
            ->where('gent_id', $gentId)
            ->orderBy('month_start')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->month_start)->toDateString())
            ->all();
    }

    private function firstOpening(int $gentId): ?object
    {
        $openings = $this->openingsFor($gentId);

        return $openings ? reset($openings) : null;
    }

    private function adjustFor(int $gentId, Carbon $month): float
    {
        return (float) ($this->openingsFor($gentId)[$month->toDateString()]->adjust ?? 0.0);
    }

    private function approvedFor(int $gentId): array
    {
        return $this->approvedCache[$gentId] ??= DB::table('calendars')
            ->where('gent_id', $gentId)
            ->where('status', 'approved')
            ->get(['start_date', 'end_date', 'jours_par_type'])
            ->map(fn ($ev) => $this->approvedRow($ev))
            ->all();
    }

    public function monthlyAccrual(Agent $gent, Carbon $monthStart): float
    {
        $base = 2.5;

        // Cas des anciens agents (sans date de contrat)
        if (empty($gent->contract_start_date)) {
            // On récupère la première ouverture enregistrée
            $firstOpening = $this->firstOpening($gent->id);

            if (!$firstOpening) {
                return 0.0;
            }

            $openingMonth = Carbon::parse($firstOpening->month_start)->startOfMonth();

            //  On crédite tous les mois à partir de l’ouverture
            return $monthStart->gte($openingMonth) ? $base : 0.0;
        }

        //  Cas des nouveaux agents (avec date de contrat)
        $start = Carbon::parse($gent->contract_start_date)->startOfDay();

        if ($monthStart->copy()->endOfMonth()->lt($start)) {
            return 0.0;
        }

        if ($monthStart->gt($start->copy()->startOfMonth())) {
            return round($base, 2, PHP_ROUND_HALF_UP);
        }

        //  Mois d’embauche → prorata exact
        $daysInMonth = $start->daysInMonth;
        // Carbon 3 : diffInDays() est signé et décimal, on garde l'entier positif de Carbon 2
        $daysCounted = (int) $start->copy()->endOfMonth()->diffInDays($start, true) + 1;
        $rawCotisation = $base * ($daysCounted / $daysInMonth);

        return round($rawCotisation, 2, PHP_ROUND_HALF_UP);
    }

    // -------------------------------------------------------------------------------
    public function accruedUpTo(Agent $gent, Carbon $included): float
    {
        if (empty($gent->contract_start_date)) return 0.0;

        $cursor = Carbon::parse($gent->contract_start_date)->startOfMonth();
        $total  = 0.0;

        while ($cursor < $included) {
            /* Arrondi RH juste AVANT d’ajouter le droit de juin */
            if ($cursor->month === 6) {
                if (!$this->isCleanHalfOrInteger($total)) {
                    $total = (float) ceil($total - 1e-9);
                }
            }

            $total += $this->monthlyAccrual($gent, $cursor);
            $cursor->addMonth();
        }

        return round($total, 2, PHP_ROUND_HALF_UP);
    }

    // -------------------------------------------------------------------------------
    public function takenInMonth(Agent $gent, Carbon $monthStart): float
    {
        $monthEnd = $monthStart->copy()->endOfMonth();
        $total = 0.0;

        foreach ($this->approvedFor($gent->id) as [$start, $end, $jours]) {
            $overlaps = $start->between($monthStart, $monthEnd)
                || ($end && $end->between($monthStart, $monthEnd))
                || ($end && $start->lt($monthStart) && $end->gt($monthEnd));
            if (! $overlaps || ! is_array($jours)) {
                continue;
            }
            foreach ($jours as $type => $nb) {
                if ($type === 'maladie') continue;
                $total += (float) $nb;
            }
        }

        return $total;
    }
    // -------------------------------------------------------------------------------

    // décompte els weekend

    // -------------------------------------------------------------------------------
    private function isCleanHalfOrInteger(float $value): bool
    {
        $dec = fmod(round($value, 2), 1.0);
        return abs($dec - 0.0) < 1e-9 || abs($dec - 0.5) < 1e-9;
    }

    // -------------------------------------------------------------------------------

    public function openingBalance(Agent $gent, Carbon $monthStart): float
    {
        // Ancien agent (pas de date de contrat)
        if (empty($gent->contract_start_date)) {
            // On cherche la première ouverture manuelle saisie par l’admin
            $firstOpening = $this->firstOpening($gent->id);

            if (!$firstOpening) {
                return 0.0;
            }

            $firstMonth = Carbon::parse($firstOpening->month_start)->startOfMonth();

            if ($monthStart->lt($firstMonth)) {
                return 0.0;
            }

            $cursor = $firstMonth->copy();
            $opening = (float) $firstOpening->opening;

            while ($cursor->lt($monthStart)) {
                if ($cursor->month === 6 && !$this->isCleanHalfOrInteger($opening)) {
                    $opening = (float) ceil($opening - 1e-9);
                }

                $taken = $this->takenInMonth($gent, $cursor);
                $cotised = $this->monthlyAccrual($gent, $cursor);
                $adjust = $this->adjustFor($gent->id, $cursor);

                $opening = $opening - $taken + $cotised + $adjust;
                $cursor->addMonth();
            }

            //  Ajouter l'ajustement du mois courant
            $adjust = $this->adjustFor($gent->id, $monthStart);
            $opening += $adjust;

            if ($monthStart->month === 6 && !$this->isCleanHalfOrInteger($opening)) {
                $opening = (float) ceil($opening - 1e-9);
            }

            return round($opening, 2, PHP_ROUND_HALF_UP);
        }

        // Nouveau agent : propagation depuis date de contrat
        $start = Carbon::parse($gent->contract_start_date)->startOfMonth();
        $cursor = $start->copy();
        $opening = 0.0;

        while ($cursor->lt($monthStart)) {
            if ($cursor->month === 6 && !$this->isCleanHalfOrInteger($opening)) {
                $opening = (float) ceil($opening - 1e-9);
            }

            $taken = $this->takenInMonth($gent, $cursor);
            $cotised = $this->monthlyAccrual($gent, $cursor);
            $adjust = $this->adjustFor($gent->id, $cursor);

            $opening = $opening - $taken + $cotised + $adjust;
            $cursor->addMonth();
        }

        // Ajouter l'ajustement du mois courant
        $adjust = $this->adjustFor($gent->id, $monthStart);
        $opening += $adjust;

        if ($monthStart->month === 6 && !$this->isCleanHalfOrInteger($opening)) {
            $opening = (float) ceil($opening - 1e-9);
        }

        return round($opening, 2, PHP_ROUND_HALF_UP);
    }

    public static function isJourFerie(Carbon $date): bool
    {
        $joursFeries = [
            '01-05', // Fête du Travail
            '14-07', // Fête Nationale
            '15-08', // Assomption
            '25-12', // Noël
        ];

        if ($date->dayOfWeek === Carbon::SUNDAY) {
            return true;
        }

        return in_array($date->format('d-m'), $joursFeries);
    }

    public static function joursFeriesJS(): array
    {
        return [
            'fixed' => ['01-05', '14-07', '15-08', '25-12'],
            'includeSunday' => true
        ];
    }
}
