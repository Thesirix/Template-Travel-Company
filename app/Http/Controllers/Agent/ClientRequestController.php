<?php

namespace App\Http\Controllers\Agent;

use App\Constants\AirlinesConstants;
use App\Constants\AirportsConstants;
use App\Constants\MaritimeConstants;
use App\Constants\WorldPortIndexConstants;
use App\Http\Controllers\Controller;
use App\Models\Airport;
use App\Models\Client;
use App\Models\ClientQuote;
use App\Models\ClientRequest;
use App\Services\ClientQuoteService;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use ReflectionClass;

/**
 * Page « Devis clients » : demande du client, fiche client (recherche par téléphone), devis et envoi du devis par SMS.
 * Les appels AJAX reçoivent du JSON avec « status » (code) et « message » (texte affiché à l'agent).
 */
class ClientRequestController extends Controller
{
    public function __construct(private ClientQuoteService $quotes)
    {
    }

    public function create()
    {
        // Favoris : aéroports configurés par l'agence (catalogue Aéroports de l'admin)
        $favorites = Airport::with('ville')->orderBy('nom')->get()
            ->map(fn ($airport) => [$airport->ville->nom ?? $airport->nom, $airport->abv])->unique(0)->values();

        return view('agent.demandes.add2', ['favorites' => $favorites]);
    }

    /** Aéroports, ports et compagnies du monde (format compact), mis en cache côté serveur et navigateur */
    public function referenceData()
    {
        $data = Cache::rememberForever('client-quotes.reference-data.v1', function () {
            $constants = fn (string $class) => array_values((new ReflectionClass($class))->getConstants());

            return [
                'airports' => array_map(fn ($a) => [$a[2], $a[3], $a[4]], $constants(AirportsConstants::class)),
                'ports' => array_map(fn ($p) => [$p[5], $p[6]], $constants(WorldPortIndexConstants::class)),
                'airlines' => array_values(array_map(fn ($a) => [$a[1], $a[3]], array_filter($constants(AirlinesConstants::class), fn ($a) => $a[7] === 'Y'))),
                'maritimes' => array_values(array_map(fn ($m) => [$m[1], $m[3]], array_filter($constants(MaritimeConstants::class), fn ($m) => $m[7] === 'Y'))),
            ];
        });

        return response()->json($data)->setPublic()->setMaxAge(86400);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'formule' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'departure_city' => ['required', 'string', 'max:255'],
            'arrived_city' => ['required', 'string', 'max:255'],
            'departure_date' => ['required', 'date_format:Y-m-d'],
            'return_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:departure_date'],
            'nb_passagers_adultes' => ['required', 'integer', 'min:0'],
            'nb_passagers_childs' => ['nullable', 'integer', 'min:0'],
            'nb_passagers_babies' => ['nullable', 'integer', 'min:0'],
            'demande_bagage' => ['nullable', 'in:Sans,Avec'],
        ]);
        $agent = Auth::guard('gent')->user();

        $clientRequest = ClientRequest::create(collect($data)->except('demande_bagage')->all() + [
            'agence_id' => $agent->agence_id,
            'gent_id' => $agent->id,
            'nb_passagers_childs' => $data['nb_passagers_childs'] ?? 0,
            'nb_passagers_babies' => $data['nb_passagers_babies'] ?? 0,
            'bagage' => ($data['demande_bagage'] ?? null) === 'Avec',
            'created_at' => now(),
        ]);

        return $this->ok('Demande client enregistrée.', ['client_request_id' => $clientRequest->id]);
    }

    public function searchClient(Request $request): JsonResponse
    {
        $phone = $request->validate(['phoneNumber' => ['required', 'string', 'max:30']])['phoneNumber'];
        $client = Client::where('phoneNumber', $phone)->first();

        return $client
            ? $this->ok('Client trouvé.', ['client' => $client, 'isNewClient' => false])
            : response()->json(['status' => 404, 'message' => 'Client non trouvé.', 'isNewClient' => true]);
    }

    /** Nouveau client, ou mise à jour de la provenance d'un client existant (client_id) */
    public function storeClient(Request $request): JsonResponse
    {
        if ($request->filled('client_id')) {
            $data = $request->validate([
                'client_id' => ['required', 'exists:clients,id'],
                'provenance' => ['nullable', 'string', 'max:255'],
            ]);
            $client = Client::findOrFail($data['client_id']);
            if (! empty($data['provenance'])) {
                $client->update(['provenance' => $data['provenance']]);
            }

            return $this->ok('Provenance mise à jour.', ['client' => $client]);
        }

        $data = $request->validate([
            'phoneNumber' => ['required', 'string', 'max:30', 'unique:clients,phoneNumber'],
            'firstName' => ['nullable', 'string', 'max:255'],
            'lastName' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:clients,email'],
        ], ['phoneNumber.unique' => 'Ce client existe déjà.', 'email.unique' => 'Cet e-mail est déjà utilisé par un autre client.']);
        $client = new Client($data);
        $client->gent_id = Auth::guard('gent')->id();
        $client->save();

        return $this->ok('Nouveau client ajouté.', ['client' => $client]);
    }

    public function updateClient(Request $request, Client $client): JsonResponse
    {
        $data = $request->validate([
            'lastName' => ['nullable', 'string', 'max:255'],
            'firstName' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('clients', 'email')->ignore($client->id)],
        ], ['email.unique' => 'Cet e-mail est déjà utilisé par un autre client.']);
        $client->update($data);

        return $this->ok('Client mis à jour.', ['client' => $client]);
    }

    /** Nouveau devis, ou suite d'un devis (client_vol_estimate_id) : commentaire, vente, marge */
    public function storeQuote(Request $request): JsonResponse
    {
        if ($request->filled('client_vol_estimate_id')) {
            $data = $request->validate([
                'client_vol_estimate_id' => ['required', 'exists:client_vol_estimates,id'],
                'comment' => ['nullable', 'string', 'max:2000'],
                'is_vente' => ['nullable', 'boolean'],
                'marge' => ['nullable', 'numeric'],
            ]);
            $quote = ClientQuote::findOrFail($data['client_vol_estimate_id']);
            $quote->update(collect($data)->except('client_vol_estimate_id')->filter(fn ($value) => $value !== null)->all());

            return $this->ok('Devis mis à jour.', ['devis' => $quote]);
        }

        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'Compagnie' => ['required', 'string', 'max:255'],
            'Installation' => ['nullable', 'string', 'max:255'],
            'Véhicule' => ['nullable', 'string', 'max:255'],
            'bagage' => ['nullable', 'in:Sans,Avec'],
            'depart' => ['required', 'string', 'max:255'],
            'arrived_city' => ['required', 'string', 'max:255'],
            'devis_departure_date' => ['required', 'date_format:Y-m-d'],
            'devis_return_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:devis_departure_date'],
            'nb_passagers_adultes' => ['required', 'integer', 'min:0'],
            'nb_passagers_childs' => ['nullable', 'integer', 'min:0'],
            'nb_passagers_babies' => ['nullable', 'integer', 'min:0'],
            'proposed_price' => ['required', 'numeric', 'min:0'],
            'destination' => ['required', 'string', 'max:255'],
            'formule' => ['required', 'string', 'max:255'],
        ]);
        $agent = Auth::guard('gent')->user();
        $quote = ClientQuote::create([
            'client_id' => $data['client_id'],
            'company' => $data['Compagnie'],
            'installation' => $data['Installation'] ?? null,
            'vehicule' => $data['Véhicule'] ?? null,
            'bagage' => ($data['bagage'] ?? null) === 'Avec',
            'departure_city' => $data['depart'],
            'arrived_city' => $data['arrived_city'],
            'departure_date' => $data['devis_departure_date'],
            'return_date' => $data['devis_return_date'] ?? null,
            'nb_passagers_adultes' => $data['nb_passagers_adultes'],
            'nb_passagers_childs' => $data['nb_passagers_childs'] ?? 0,
            'nb_passagers_babies' => $data['nb_passagers_babies'] ?? 0,
            'proposed_price' => $data['proposed_price'],
            'selled' => false,
            'created_at' => now(),
            'destination' => $data['destination'],
            'formule' => $data['formule'],
            'gent_id' => $agent->id,
            'agence_id' => $agent->agence_id,
        ]);

        return $this->ok('Devis créé.', ['client_vol_estimate_id' => $quote->id]);
    }

    public function sendQuoteSms(Request $request, SmsService $sms): JsonResponse
    {
        $id = $request->validate(['client_vol_estimate_id' => ['required', 'exists:client_vol_estimates,id']])['client_vol_estimate_id'];
        $quote = ClientQuote::with('client')->findOrFail($id);
        if (! $quote->client?->phoneNumber) {
            return response()->json(['code' => 422, 'message' => "Le client n'a pas de numéro de téléphone."], 422);
        }

        $result = $sms->sendSms([$quote->client->phoneNumber], $this->quotes->smsMessage($quote, Auth::guard('gent')->user()));

        return $result['sent'] || ! config('services.isendpro.id')
            ? response()->json(['code' => 200, 'message' => $result['sent'] ? 'SMS envoyé.' : 'SMS préparé (envoi désactivé : iSendPro non configuré).'])
            : response()->json(['code' => 502, 'message' => "Le SMS n'a pas pu être envoyé."], 502);
    }

    private function ok(string $message, array $data = []): JsonResponse
    {
        return response()->json(['status' => 200, 'message' => $message] + $data);
    }
}
