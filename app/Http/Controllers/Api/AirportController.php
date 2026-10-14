<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Airport;
use Illuminate\Http\Request;

class AirportController extends Controller
{
    /**
     * Aéroports d'un pays (nom du pays dans « country ») pour les listes de départ / d'arrivée.
     */
    public function search(Request $request)
    {
        $airports = Airport::query()
            ->join('villes', 'air_ports.ville_id', '=', 'villes.id')
            ->join('countries', 'villes.country_id', '=', 'countries.id')
            ->where('countries.nom', $request->input('country'))
            ->select('air_ports.id', 'air_ports.abv', 'villes.nom as cityName')
            ->orderBy('villes.nom')
            ->get();

        return response()->json(['data' => $airports]);
    }
}
