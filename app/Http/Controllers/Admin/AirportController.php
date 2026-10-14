<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airport;
use App\Models\City;
use Illuminate\Http\Request;

/**
 * Aéroports (code IATA, ville).
 */
class AirportController extends Controller
{
    public function index()
    {
        $airPorts = Airport::with('ville')->orderBy('nom')->get();
        $villes = City::orderBy('nom')->get();

        return view('admin.aeroports.index', compact('villes', 'airPorts'));
    }

    public function store(Request $request)
    {
        $airport = Airport::create($this->validated($request));

        return redirect()->route('admin.aeroports')->with('success', "L'aéroport « {$airport->nom} » a été ajouté.");
    }

    public function edit(int $airport)
    {
        $air = Airport::findOrFail($airport);
        $villes = City::orderBy('nom')->get();

        return view('admin.aeroports.edit', compact('air', 'villes'));
    }

    public function update(Request $request, int $airport)
    {
        $airport = Airport::findOrFail($airport);
        $airport->update($this->validated($request, $airport->id));

        return redirect()->route('admin.aeroports')->with('success', "L'aéroport « {$airport->nom} » a été mis à jour.");
    }

    public function destroy(int $airport)
    {
        $airport = Airport::findOrFail($airport);
        if ($airport->escaleDe()->exists() || $airport->escaleA()->exists()) {
            return back()->with('error', 'Cet aéroport est utilisé par des vols : il ne peut pas être supprimé.');
        }
        $airport->delete();

        return redirect()->route('admin.aeroports')->with('success', 'L\'aéroport a été supprimé.');
    }

    private function validated(Request $request, ?int $ignore = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abv' => ['required', 'string', 'size:3', 'unique:air_ports,abv' . ($ignore ? ',' . $ignore : '')],
            'ville' => ['required', 'exists:villes,id'],
        ]);

        return ['nom' => $data['name'], 'abv' => strtoupper($data['abv']), 'ville_id' => $data['ville']];
    }
}
