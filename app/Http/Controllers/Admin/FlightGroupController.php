<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GroupType;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\FlightGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Groupes de vols : regroupent les vols vendus ensemble pour une destination (aller-retour ou segments).
 */
class FlightGroupController extends Controller
{
    public function index()
    {
        return view('admin.groupes-vols.index', ['volGroups' => FlightGroup::with('country')->latest('created_at')->get()]);
    }

    public function create()
    {
        return view('admin.groupes-vols.create', ['countries' => Country::orderBy('nom')->get()]);
    }

    public function store(Request $request)
    {
        FlightGroup::create($this->validated($request));

        return redirect()->route('admin.groupes-vols')->with('success', 'Groupe créé.');
    }

    public function edit(FlightGroup $group)
    {
        return view('admin.groupes-vols.edit', ['countries' => Country::orderBy('nom')->get(), 'volGroup' => $group]);
    }

    public function update(Request $request)
    {
        $group = FlightGroup::findOrFail($request->validate(['id' => ['required', 'integer']])['id']);
        $group->update($this->validated($request));

        return redirect()->route('admin.groupes-vols')->with('success', 'Groupe modifié.');
    }

    public function destroy(Request $request)
    {
        $group = FlightGroup::withCount('flights')->findOrFail($request->validate(['id' => ['required', 'integer']])['id']);
        if ($group->flights_count > 0) {
            return redirect()->route('admin.groupes-vols')->with('error', 'Ce groupe contient des vols : supprimez-les ou déplacez-les d\'abord.');
        }
        $group->delete();

        return redirect()->route('admin.groupes-vols')->with('success', 'Groupe supprimé.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'type' => ['required', Rule::in(array_column(GroupType::cases(), 'value'))],
            'same_itineraire' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ], [], ['name' => 'nom', 'country_id' => 'pays', 'same_itineraire' => 'même itinéraire', 'is_active' => 'actif']);
    }
}
