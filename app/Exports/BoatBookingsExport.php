<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Traversées en bateau d'un statut, avec les filtres de la liste.
 */
class BoatBookingsExport implements FromView, ShouldAutoSize
{
    public function __construct(private Collection $bookings, private string $status)
    {
    }

    public function view(): View
    {
        return view('agent.bateau.export', ['bookings' => $this->bookings, 'status' => $this->status]);
    }
}
