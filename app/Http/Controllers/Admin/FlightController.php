<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airport;
use App\Models\City;
use App\Models\Company;
use App\Models\Destination;
use App\Models\Flight;
use App\Models\FlightGroup;
use App\Models\FlightStop;
use App\Models\SalesCondition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Vols d'une destination : liste (en vente ou archivés), création, modification, copie et suppression.
 *
 * Le formulaire envoie les escales par tronçon et par numéro :
 * aller « air_de_a_0, air_a_a_0, date_de_a_0, heure_de_a_0, heure_a_a_0, temp_a_0, modele_a_0 » (0 à 2),
 * retour « ..._r_0 » à « ..._r_2 ».
 */
class FlightController extends Controller
{
    private const STOPS = 3;

    public function index(Request $request, $destination)
    {
        $vols = Flight::with(['escaliers', 'ville', 'company'])
            ->where('destination', $destination)
            ->when($request->query('compagnie_id'), fn ($q, $company) => $q->where('company_id', $company))
            ->when($request->query('itineraire'), fn ($q, $route) => $q->where('itineraire', 'like', $route))
            ->when($request->query('type_voyage'), fn ($q, $type) => $q->where('type_voyage', $type))
            ->when($request->query('date_aller'), fn ($q, $date) => $q->whereDate('date_aller', $date))
            ->when($request->query('date_retour'), fn ($q, $date) => $q->whereDate('date_retour', $date))
            ->when($request->query('ville_id'), fn ($q, $city) => $q->where('ville_id', $city))
            ->where(function ($q) use ($request) {
                // Archives : vols dont le dernier trajet est passé
                $operator = $request->boolean('archive') ? '<' : '>=';
                $q->where(fn ($q2) => $q2->where('type_voyage', 'aller_retour')->where('date_retour', $operator, Carbon::today()))
                    ->orWhere(fn ($q2) => $q2->where('type_voyage', '!=', 'aller_retour')->where('date_aller', $operator, Carbon::today()));
            })
            ->orderByDesc('id')
            ->get();
        $compagnies = Company::orderBy('nom')->get();
        $villes = City::orderBy('nom')->get();

        return view('admin.vols.allvols', compact('vols', 'destination', 'compagnies', 'villes'));
    }

    public function create($destination)
    {
        return view('admin.vols.add', $this->formData($destination));
    }

    public function store(Request $request, $destination)
    {
        DB::transaction(fn () => $this->save(new Flight(), $request, $destination));

        return redirect()->route('admin.vols', $destination)->with('success', 'Le vol a été créé.');
    }

    public function edit($destination, int $flight)
    {
        $vol = Flight::with(['escaliers' => fn ($q) => $q->orderBy('type')->orderBy('num')])->findOrFail($flight);

        return view('admin.vols.edit', $this->formData($destination) + compact('vol'));
    }

    public function update(Request $request, $destination)
    {
        $flight = Flight::findOrFail($request->input('vol_id'));
        DB::transaction(fn () => $this->save($flight, $request, $destination));

        return redirect()->route('admin.vols', $destination)->with('success', 'Le vol a été mis à jour.');
    }

    public function copy($destination, int $flight)
    {
        $vol = Flight::with(['escaliers' => fn ($q) => $q->orderBy('type')->orderBy('num')])->findOrFail($flight);

        return view('admin.vols.copier', $this->formData($destination) + compact('vol'));
    }

    /** La copie est un nouveau vol prérempli avec les données d'un vol existant */
    public function storeCopy(Request $request, $destination)
    {
        return $this->store($request, $destination);
    }

    public function destroy(Request $request)
    {
        $flight = Flight::findOrFail($request->input('vol_id'));
        if ($flight->place_reserver > 0 || $flight->reservations()->exists()) {
            return back()->with('error', 'Ce vol a des réservations : archivez-le plutôt que de le supprimer.');
        }
        $flight->escaliers()->delete();
        $flight->delete();

        return back()->with('success', 'Le vol a été supprimé.');
    }

    private function save(Flight $flight, Request $request, string $destination): void
    {
        $data = $request->validate([
            'group_id' => ['required', 'exists:vol_groups,id'],
            'company' => ['required', 'exists:companies,id'],
            'type_voyage' => ['required', 'in:aller_retour,aller_simple,retour_simple'],
            'ville' => ['required', 'exists:villes,id'],
            'condition' => ['nullable', 'exists:conditions,id'],
            'place' => ['required', 'integer', 'min:' . (int) $flight->place_reserver],
            'pnr' => ['nullable', 'string', 'max:100'],
            'date_de_a_0' => ['required', 'date'],
            'air_de_a_0' => ['required', 'exists:air_ports,id'],
            'air_a_a_0' => ['required', 'exists:air_ports,id'],
            'heur_depart_aller' => ['required'],
            'heur_arriver_aller' => ['required'],
            'prix_achat_adult' => ['required', 'numeric', 'min:0'], 'prix_achat_enfant' => ['required', 'numeric', 'min:0'],
            'prix_achat_bebe' => ['required', 'numeric', 'min:0'], 'prix_vente_adult' => ['required', 'numeric', 'min:0'],
            'prix_vente_enfant' => ['required', 'numeric', 'min:0'], 'prix_vente_bebe' => ['required', 'numeric', 'min:0'],
            'date_de_r_0' => ['required_if:type_voyage,aller_retour', 'nullable', 'date', 'after_or_equal:date_de_a_0'],
        ]);
        $outbound = Carbon::parse($data['date_de_a_0']);

        $flight->fill([
            'vol_group_id' => $data['group_id'], 'company_id' => $data['company'], 'type_voyage' => $data['type_voyage'],
            'destination' => $destination, 'ville_id' => $data['ville'], 'condition_id' => $data['condition'] ?? null,
            'is_archive' => $request->boolean('is_archive'),
            'place_total' => $data['place'], 'place_disponible' => $data['place'] - (int) $flight->place_reserver,
            'place_reserver' => (int) $flight->place_reserver,
            'pnr' => $data['pnr'] ?? null, 'bagage' => $request->input('bagage'), 'soute_num' => $request->input('soute'),
            'bg_hand' => $request->input('hand'), 'itineraire' => $request->input('itineraire'),
            'itineraire_color' => $request->input('itineraire_color'), 'period_voyage' => $request->input('period_voyage'),
            'j_inscription' => $request->input('j_inscription'), 'j_45' => $request->input('j_45'),
            'j_44_j_1' => $request->input('j_44_j_1'), 'days_before_closure' => (int) $request->input('days_before_closure'),
            'prix_comprend' => $request->input('prix_comprend'), 'prix_comprend_pas' => $request->input('prix_comprend_pas'),
            'formalite' => $request->input('formalite'), 'total_a_payer' => $request->input('total_a_payer'),
            'date_aller' => $outbound->toDateString(), 'd' => $outbound->day, 'm' => $outbound->month, 'y' => $outbound->year,
            'duration_aller' => $request->input('duration_aller'),
            'heur_depart_aller' => $data['heur_depart_aller'], 'heur_arriver_aller' => $data['heur_arriver_aller'],
        ] + collect(['prix_achat_adult', 'prix_achat_enfant', 'prix_achat_bebe', 'prix_vente_adult', 'prix_vente_enfant', 'prix_vente_bebe'])
            ->mapWithKeys(fn ($k) => [$k => $data[$k]])->all());

        $roundTrip = $data['type_voyage'] === 'aller_retour';
        $flight->fill($roundTrip ? [
            'date_retour' => $data['date_de_r_0'],
            'duration_retour' => $request->input('duration_retour'),
            'heur_depart_retour' => $request->input('heur_depart_retour'),
            'heur_arriver_retour' => $request->input('heur_arriver_retour'),
        ] : ['date_retour' => null]);
        $flight->save();

        // Escales : on remplace celles du vol par celles du formulaire
        $flight->escaliers()->delete();
        $this->saveStops($flight, $request, 'a', (int) $request->input('type', $data['type_voyage'] === 'retour_simple' ? 1 : 0));
        if ($roundTrip) {
            $this->saveStops($flight, $request, 'r', 1);
        }
    }

    private function saveStops(Flight $flight, Request $request, string $leg, int $type): void
    {
        for ($i = 0; $i < self::STOPS; $i++) {
            if (! $request->filled("air_de_{$leg}_{$i}")) {
                continue;
            }
            FlightStop::create([
                'vol_id' => $flight->id, 'type' => $type, 'num' => $i + 1,
                'air_port_id' => $request->input("air_de_{$leg}_{$i}"), 're_air_port_id' => $request->input("air_a_{$leg}_{$i}"),
                'date_depart' => $request->input("date_de_{$leg}_{$i}"), 'heure_depart' => $request->input("heure_de_{$leg}_{$i}"),
                'heure_arriver' => $request->input("heure_a_{$leg}_{$i}"), 'temp' => $request->input("temp_{$leg}_{$i}"),
                'model' => $request->input("modele_{$leg}_{$i}"),
            ]);
        }
    }

    private function formData(string $destination): array
    {
        $countries = $this->countries($destination);

        return [
            'destination' => $destination,
            'AirPorts' => Airport::with('ville')->orderBy('nom')->get(),
            'companies' => Company::whereHas('country', fn ($q) => $q->whereIn('nom', $countries))->orderBy('nom')->get(),
            'conditions' => SalesCondition::orderBy('nom')->get(),
            'groups' => FlightGroup::orderBy('name')->get(),
            'villes' => City::whereHas('country', fn ($q) => $q->whereIn('nom', $countries))->orderBy('nom')->get(),
        ];
    }

    /**
     * Pays proposés dans le formulaire d'un vol : celui de la destination (slug de l'URL)
     * et le pays de départ de l'agence.
     */
    private function countries(string $destination): array
    {
        $country = Destination::with('country')->where('slug', $destination)->first()?->country?->nom ?? $destination;

        return array_values(array_unique([$country, config('agency.origin_country', 'France')]));
    }
}
