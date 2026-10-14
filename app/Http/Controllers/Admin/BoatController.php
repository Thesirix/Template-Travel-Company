<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoatAccommodation;
use App\Models\BoatArrivalPort;
use App\Models\BoatBooking;
use App\Models\BoatCompany;
use App\Models\BoatDeparturePort;
use App\Models\BoatDestination;
use App\Models\BoatFare;
use App\Models\BoatPassengerInsurance;
use App\Models\BoatVehicle;
use App\Models\BoatVehicleInsurance;
use App\Models\BoatVehicleSize;
use Illuminate\Http\Request;

/**
 * Catalogue du module bateau : destinations et, pour chacune, ports et compagnies ;
 * installations, tarifs, véhicules, gabarits et assurances communs à toutes les destinations.
 * Un élément déjà utilisé par une demande de traversée ne peut pas être supprimé.
 */
class BoatController extends Controller
{
    /** clé d'URL => [modèle, titre, colonne de boat_bookings qui l'utilise, par destination ?] */
    public const CATALOGS = [
        'destinations' => [BoatDestination::class, 'Destinations', 'destination_id', false],
        'ports-depart' => [BoatDeparturePort::class, 'Ports de départ', 'departure_port_id', true],
        'ports-arrivee' => [BoatArrivalPort::class, "Ports d'arrivée", 'arrival_port_id', true],
        'compagnies' => [BoatCompany::class, 'Compagnies', 'company_id', true],
        'installations' => [BoatAccommodation::class, 'Installations', 'accommodation_id', false],
        'tarifs' => [BoatFare::class, 'Tarifs', 'fare_id', false],
        'vehicules' => [BoatVehicle::class, 'Véhicules', 'vehicle_id', false],
        'gabarits' => [BoatVehicleSize::class, 'Gabarits de véhicule', 'vehicle_size_id', false],
        'assurances-passagers' => [BoatPassengerInsurance::class, 'Assurances passagers', 'insurance_id', false],
        'assurances-vehicules' => [BoatVehicleInsurance::class, 'Assurances véhicules', 'vehicle_insurance_id', false],
    ];

    public function index()
    {
        $catalogs = collect(self::CATALOGS)->map(fn ($catalog, $key) => [
            'title' => $catalog[1],
            'perDestination' => $catalog[3],
            'items' => $catalog[0]::query()->when($catalog[3], fn ($q) => $q->with('destination'))->orderBy('name')->get(),
        ]);

        return view('admin.bateau.gestion', ['catalogs' => $catalogs, 'destinations' => $catalogs['destinations']['items']]);
    }

    public function store(Request $request, string $catalog)
    {
        [$model, , , $perDestination] = self::CATALOGS[$catalog];
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'destination_id' => [$perDestination ? 'required' : 'prohibited', 'exists:boat_destinations,id'],
        ], [], ['name' => 'nom', 'destination_id' => 'destination']);
        $model::create($data);

        return redirect()->route('admin.bateau')->with('success', 'Élément ajouté.');
    }

    public function destroy(Request $request, string $catalog)
    {
        [$model, , $column] = self::CATALOGS[$catalog];
        $item = $model::findOrFail($request->validate(['id' => ['required', 'integer']])['id']);

        $used = BoatBooking::where($column, $item->id)->exists()
            || ($catalog === 'destinations' && ($item->departurePorts()->exists() || $item->arrivalPorts()->exists() || $item->companies()->exists()));
        if ($used) {
            return redirect()->route('admin.bateau')->with('error', "« {$item->name} » est utilisé : il ne peut pas être supprimé.");
        }
        $item->delete();

        return redirect()->route('admin.bateau')->with('success', 'Élément supprimé.');
    }
}
