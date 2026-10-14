<?php

namespace App\Http\Controllers\Agent;

use App\Exports\ContactsExport;
use App\Http\Controllers\Controller;
use App\Models\ClientQuote;
use App\Models\Destination;
use App\Models\FlightPassenger;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Extraction des contacts (passagers des réservations et demandes de devis), filtrable par destination,
 * pour les campagnes SMS / e-mail.
 */
class ExtractionController extends Controller
{
    public function index(Request $request)
    {
        $destination = $this->destination($request);
        $contacts = $this->contacts($destination);

        return view('agent.extractions.home', compact('contacts', 'destination'));
    }


    public function export(Request $request)
    {
        $destination = $this->destination($request);
        $phones = $this->contacts($destination)->pluck('phone')->filter()->unique()->values();

        return Excel::download(new ContactsExport($phones), 'telephones' . ($destination ? '-' . $destination->slug : '') . '.xlsx');
    }

    private function destination(Request $request): ?Destination
    {
        $value = $request->input('destination');

        return $value ? Destination::where('slug', $value)->orWhere('name', $value)->first() : null;
    }

    /**
     * Contacts : passagers adultes des réservations de vols, puis demandes de devis des clients.
     *
     * @return Collection<int, array{nom: ?string, prenom: ?string, phone: ?string, email: ?string, destination: ?string, source: string, agence: ?string, vendeur: ?string, date: mixed}>
     */
    private function contacts(?Destination $destination): Collection
    {
        $passengers = FlightPassenger::with(['reservation.gent.agence', 'reservation.vol.volGroup.country'])
            ->whereIn('type', ['adult', 'adulte'])
            ->whereNotNull('tele1')
            ->when($destination, fn ($q) => $q->whereHas('reservation.vol.volGroup', fn ($q2) => $q2->where('country_id', $destination->country_id)))
            ->latest('id')
            ->get()
            ->map(fn ($p) => [
                'nom' => $p->nom,
                'prenom' => $p->prenom,
                'phone' => $p->tele1,
                'email' => $p->email,
                'destination' => $p->reservation?->vol?->volGroup?->country?->nom,
                'source' => 'Réservation',
                'agence' => $p->reservation?->gent?->agence?->name,
                'vendeur' => $p->reservation?->gent ? $p->reservation->gent->name . ' ' . $p->reservation->gent->prenom : null,
                'date' => $p->created_at,
            ]);

        $requests = ClientQuote::with(['client', 'agenceRelation', 'gentRelation'])
            ->when($destination, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('destination', 'like', '%' . $destination->name . '%')
                ->orWhere('destination', 'like', '%' . ($destination->country?->nom ?? $destination->name) . '%')))
            ->latest('id')
            ->get()
            ->map(fn ($c) => [
                'nom' => $c->client?->lastName,
                'prenom' => $c->client?->firstName,
                'phone' => $c->client?->phoneNumber,
                'email' => $c->client?->email,
                'destination' => $c->destination,
                'source' => 'Demande de devis',
                'agence' => $c->agenceRelation?->name,
                'vendeur' => $c->gentRelation ? $c->gentRelation->name . ' ' . $c->gentRelation->prenom : null,
                'date' => $c->created_at,
            ]);

        return $passengers->concat($requests)->values();
    }
}
