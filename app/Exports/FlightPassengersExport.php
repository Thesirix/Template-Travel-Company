<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Passagers d'un vol pour l'émission des billets : liste interne ou fichier au format
 * attendu par la compagnie (Amadeus, Ethiopian, Volotea).
 */
class FlightPassengersExport implements FromView, ShouldAutoSize, WithStyles
{
    public const FORMATS = ['list', 'amadeus', 'ethiopian', 'volotea'];

    public function __construct(private Collection $reservations, private string $format = 'list')
    {
    }

    public function view(): View
    {
        return view('agent.vols.emission.exports.' . $this->format, ['reservations' => $this->reservations]);
    }

    public function styles(Worksheet $sheet): array
    {
        if ($this->format !== 'amadeus') {
            return [];
        }

        return [1 => [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A61A9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]];
    }
}
