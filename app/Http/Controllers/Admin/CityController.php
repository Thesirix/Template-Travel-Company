<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;

/**
 * Villes (rattachées à un pays, couleur utilisée dans les listes de vols).
 */
class CityController extends Controller
{
    public function index()
    {
        $villes = City::with('country')->orderBy('nom')->get();
        $countries = Country::orderBy('nom')->get();

        return view('admin.villes.index', compact('villes', 'countries'));
    }

    public function store(Request $request)
    {
        $city = City::create($this->validated($request));

        return redirect()->route('admin.villes')->with('success', "La ville « {$city->nom} » a été ajoutée.");
    }

    public function edit(int $city)
    {
        $ville = City::findOrFail($city);
        $countries = Country::orderBy('nom')->get();

        return view('admin.villes.edit', compact('ville', 'countries'));
    }

    public function update(Request $request, int $city)
    {
        $city = City::findOrFail($city);
        $city->update($this->validated($request));

        return redirect()->route('admin.villes')->with('success', "La ville « {$city->nom} » a été mise à jour.");
    }

    public function destroy(int $city)
    {
        $city = City::findOrFail($city);
        if ($city->airPorts()->exists()) {
            return back()->with('error', 'Cette ville a des aéroports : supprimez-les d\'abord.');
        }
        $city->delete();

        return redirect()->route('admin.villes')->with('success', 'La ville a été supprimée.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'exists:countries,id'],
            'coleur' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        return ['nom' => $data['name'], 'country_id' => $data['country'], 'coleur' => $data['coleur']];
    }
}
