<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;

/** Liste de numéros de téléphone (une colonne) pour les campagnes SMS */
class ContactsExport implements FromView
{
    public function __construct(private Collection $phones)
    {
    }

    public function view(): View
    {
        return view('agent.extractions.download', ['phones' => $this->phones]);
    }
}
