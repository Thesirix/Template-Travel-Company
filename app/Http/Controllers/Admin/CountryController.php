<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StoresLogo;
use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

/**
 * Pays du catalogue (départs, destinations, compagnies).
 */
class CountryController extends Controller
{
    use StoresLogo;

    public function index()
    {
        $pays = Country::orderBy('nom')->get();

        return view('admin.pays.index', compact('pays'));
    }

    public function store(Request $request)
    {
        $country = new Country();
        $this->fill($country, $request);
        $country->save();

        return redirect()->route('admin.pays')->with('success', "Le pays « {$country->nom} » a été ajouté.");
    }

    public function edit(int $country)
    {
        $country = Country::findOrFail($country);

        return view('admin.pays.edit', compact('country'));
    }

    public function update(Request $request, int $country)
    {
        $country = Country::findOrFail($country);
        $this->fill($country, $request);
        $country->save();

        return redirect()->route('admin.pays')->with('success', "Le pays « {$country->nom} » a été mis à jour.");
    }

    public function destroy(int $country)
    {
        $country = Country::findOrFail($country);
        if ($country->villes()->exists() || $country->companies()->exists()) {
            return back()->with('error', 'Ce pays a des villes ou des compagnies : supprimez-les d\'abord.');
        }
        $country->delete();

        return redirect()->route('admin.pays')->with('success', 'Le pays a été supprimé.');
    }

    private function fill(Country $country, Request $request): void
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'image' => self::LOGO_RULE]);
        $country->nom = $data['name'];
        $country->show = $request->boolean('show');
        $country->chart = $request->boolean('chart');
        $country->image = $this->storeLogo($request) ?? $country->getRawOriginal('image') ?? '';
    }
}
