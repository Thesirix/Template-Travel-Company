<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\BlockPowerArrivalCity;
use App\Models\BlockPowerCompany;
use App\Models\BlockPowerConfirmation;
use App\Models\BlockPowerCountry;
use App\Models\BlockPowerDepartureCity;
use App\Models\BlockPowerGuide;
use App\Models\BlockPowerOption;
use App\Models\BlockPowerPassenger;
use App\Models\BlockPowerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Allotements : options de places auprès des compagnies.
 * Créer une option → placer les passagers (nominative) → confirmer (nouveau PNR, nouvelle date limite) → émettre.
 * Une option peut être relâchée à tout moment avant confirmation ; ses places restantes peuvent être reprises
 * dans une nouvelle option. Les statuts « bientôt échue » et « expirée » sont posés chaque jour (block-power:refresh).
 */
class BlockPowerController extends Controller
{
    /** Onglets de la liste : clé d'URL => [titre, statuts] */
    public const TABS = [
        'en-vente' => ['En vente', ['available', 'expiring']],
        'nominatives' => ['Nominatives', ['named']],
        'a-emettre' => ['À émettre', ['confirmed']],
        'emises' => ['Émises', ['issued']],
        'archives' => ['Archives', ['issued']],
        'relachees' => ['Relâchées', ['released']],
        'expirees' => ['Expirées', ['expired']],
    ];

    private const SORTS = ['option_date', 'date_aller', 'created_at', 'price', 'seats'];

    public function index(Request $request)
    {
        $tab = array_key_exists($request->query('statut'), self::TABS) ? $request->query('statut') : 'en-vente';
        $sort = in_array($request->query('tri'), self::SORTS, true) ? $request->query('tri') : 'option_date';
        $direction = $request->query('sens') === 'desc' ? 'desc' : 'asc';

        $options = BlockPowerOption::inStatus(self::TABS[$tab][1])->with(['agent', 'passengers', 'release.agent'])
            ->when($tab === 'emises', fn ($q) => $q->whereDate('date_aller', '>=', today()))
            ->when($tab === 'archives', fn ($q) => $q->whereDate('date_aller', '<', today()))
            ->when($request->filled('pays'), fn ($q) => $q->where('country', $request->pays))
            ->when($request->filled('depart'), fn ($q) => $q->where('departure_city', $request->depart))
            ->when($request->filled('arrivee'), fn ($q) => $q->where('arrival_city', $request->arrivee))
            ->when($request->filled('date_aller'), fn ($q) => $q->whereDate('date_aller', $request->date_aller))
            ->orderBy($sort, $direction)
            ->get();
        $counts = BlockPowerOption::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('agent.block-power.index', $this->catalog() + [
            'tab' => $tab,
            'options' => $options,
            'counts' => $counts,
            'filters' => $request->only(['pays', 'depart', 'arrivee', 'date_aller']),
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function guide()
    {
        $guide = BlockPowerGuide::first();
        if (! $guide || ! Storage::disk('public')->exists('guide/' . $guide->name)) {
            return redirect()->route('agent.block-power')->with('error', "Aucun guide des allotements n'a encore été déposé.");
        }

        return response()->file(Storage::disk('public')->path('guide/' . $guide->name));
    }

    /** Nouvelle option ; « depuis » reprend une option relâchée ou confirmée (mêmes informations, places restantes) */
    public function create(Request $request)
    {
        $option = new BlockPowerOption();
        if ($source = BlockPowerOption::find($request->query('depuis'))) {
            $option->fill($source->only(['name', 'company', 'country', 'departure_city', 'arrival_city', 'date_aller', 'date_retour',
                'cabin_class', 'price', 'note']));
            $option->seats = max(1, (int) $request->query('places', $source->freeSeats() ?: $source->seats));
        }

        return view('agent.block-power.form', $this->catalog() + ['option' => $option]);
    }

    public function store(Request $request)
    {
        BlockPowerOption::create($this->validateOption($request) + ['agent_id' => Auth::guard('gent')->id(), 'status' => 'available']);

        return redirect()->route('agent.block-power')->with('success', 'Option créée.');
    }

    public function show(BlockPowerOption $option)
    {
        return view('agent.block-power.show', ['option' => $option->load(['agent', 'passengers', 'release.agent'])]);
    }

    public function edit(BlockPowerOption $option)
    {
        abort_unless($option->isActive(), 404);

        return view('agent.block-power.form', $this->catalog() + ['option' => $option]);
    }

    public function update(Request $request, BlockPowerOption $option)
    {
        abort_unless($option->isActive(), 404);
        $data = $this->validateOption($request, $option);
        if ($data['seats'] < $option->passengers()->count()) {
            throw ValidationException::withMessages(['seats' => 'Il y a déjà ' . $option->passengers()->count() . ' passagers placés sur cette option.']);
        }
        $option->update($data);

        return redirect()->route('agent.block-power.show', $option)->with('success', 'Option modifiée.');
    }

    public function passengersForm(BlockPowerOption $option)
    {
        abort_unless($option->isActive() && $option->freeSeats() > 0, 404);

        return view('agent.block-power.passengers', ['option' => $option->load('passengers'), 'types' => BlockPowerPassenger::TYPES]);
    }

    /** Passagers placés sur l'option : contrôle des places et des âges (au retour, ou à l'aller sans retour) */
    public function storePassengers(Request $request, BlockPowerOption $option)
    {
        abort_unless($option->isActive(), 404);
        $data = $request->validate([
            'passagers' => ['required', 'array', 'min:1', 'max:' . $option->freeSeats()],
            'passagers.*.type' => ['required', Rule::in(array_keys(BlockPowerPassenger::TYPES))],
            'passagers.*.last_name' => ['required', 'string', 'max:150'],
            'passagers.*.first_name' => ['required', 'string', 'max:150'],
            'passagers.*.birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'passagers.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'passagers.*.deposit' => ['nullable', 'numeric', 'min:0'],
            'passagers.*.deposit_number' => ['nullable', 'string', 'max:50'],
        ], ['passagers.max' => 'Il ne reste que :max place(s) sur cette option.'], ['passagers' => 'passagers']);
        $this->checkPassengerAges($data['passagers'], Carbon::parse($option->date_retour ?? $option->date_aller));

        DB::transaction(function () use ($option, $data) {
            foreach ($data['passagers'] as $passenger) {
                $option->passengers()->create(collect($passenger)->except('birth_date')->all());
            }
            $option->update(['status' => 'named']);
        });

        return redirect()->route('agent.block-power.show', $option)->with('success', count($data['passagers']) . ' passager(s) placé(s).');
    }

    public function destroyPassenger(BlockPowerPassenger $passenger)
    {
        $option = $passenger->option;
        abort_unless($option->isActive(), 404);
        DB::transaction(function () use ($passenger, $option) {
            $passenger->delete();
            if ($option->passengers()->doesntExist()) {
                $option->update(['status' => 'available']);
            }
        });

        return back()->with('success', 'Passager retiré.');
    }

    public function confirmForm(BlockPowerOption $option)
    {
        abort_unless($option->status === 'named', 404);

        return view('agent.block-power.confirm', ['option' => $option->load('passengers')]);
    }

    /** Confirmation auprès de la compagnie : nouveau PNR et date limite ; les places non vendues peuvent être reprises */
    public function confirm(Request $request, BlockPowerOption $option)
    {
        abort_unless($option->status === 'named', 404);
        $data = $request->validate([
            'pnr' => ['required', 'string', 'max:50'],
            'option_date' => ['required', 'date'],
            'reprendre' => ['nullable', 'boolean'],
        ], [], ['pnr' => 'PNR', 'option_date' => 'date limite']);
        $free = $option->freeSeats();

        DB::transaction(function () use ($option, $data) {
            $option->update(['pnr' => $data['pnr'], 'option_date' => $data['option_date'], 'status' => 'confirmed']);
            foreach ($option->passengers as $passenger) {
                BlockPowerConfirmation::create(['agent_id' => Auth::guard('gent')->id(), 'passenger_id' => $passenger->id]);
            }
        });

        if (! empty($data['reprendre']) && $free > 0) {
            return redirect()->route('agent.block-power.create', ['depuis' => $option->id, 'places' => $free])
                ->with('success', "Option confirmée. Créez l'option des $free place(s) restante(s).");
        }

        return redirect()->route('agent.block-power', ['statut' => 'a-emettre'])->with('success', 'Option confirmée.');
    }

    /** Relâche de l'option ; « reprendre » ouvre une nouvelle option avec les mêmes informations */
    public function release(Request $request, BlockPowerOption $option)
    {
        abort_unless($option->isActive(), 404);
        DB::transaction(function () use ($option) {
            $option->update(['status' => 'released']);
            $option->release()->create(['agent_id' => Auth::guard('gent')->id()]);
        });

        return $request->boolean('reprendre')
            ? redirect()->route('agent.block-power.create', ['depuis' => $option->id])->with('success', 'Option relâchée : saisissez la nouvelle option.')
            : redirect()->route('agent.block-power')->with('success', 'Option relâchée.');
    }

    public function issue(BlockPowerOption $option)
    {
        abort_unless($option->status === 'confirmed', 404);
        $option->update(['status' => 'issued']);

        return redirect()->route('agent.block-power', ['statut' => 'emises'])->with('success', 'Billets émis.');
    }

    public function requests()
    {
        return view('agent.block-power.requests', ['requests' => BlockPowerRequest::with('agent')->latest()->get()]);
    }

    public function createRequest()
    {
        return view('agent.block-power.request-form', $this->catalog());
    }

    public function storeRequest(Request $request)
    {
        $data = $this->validateOption($request, isRequest: true);
        BlockPowerRequest::create($data + ['agent_id' => Auth::guard('gent')->id(), 'status' => 'available']);

        return redirect()->route('agent.block-power.requests')->with('success', 'Demande enregistrée.');
    }

    public function destroyRequest(BlockPowerRequest $blockPowerRequest)
    {
        $blockPowerRequest->delete();

        return back()->with('success', 'Demande supprimée.');
    }

    /** Champs d'une option (ou d'une demande : PNR facultatif, objectifs de classe et de prix) */
    private function validateOption(Request $request, ?BlockPowerOption $option = null, bool $isRequest = false): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'pnr' => [$isRequest ? 'nullable' : 'required', 'string', 'max:50', Rule::unique('block_power_options', 'pnr')->ignore($option?->id)],
            'company' => ['nullable', 'string', 'max:150'],
            'country' => ['required', 'string', 'max:150'],
            'departure_city' => ['required', 'string', 'max:150'],
            'arrival_city' => ['required', 'string', 'max:150'],
            'date_aller' => ['required_without:date_retour', 'nullable', 'date'],
            'date_retour' => ['required_without:date_aller', 'nullable', 'date', 'after_or_equal:date_aller'],
            'seats' => ['required', 'integer', 'min:1', 'max:500'],
            'cabin_class' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'option_date' => ['required', 'date'],
            'pnr_created_on' => [$isRequest ? 'nullable' : 'required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
        if ($isRequest) {
            unset($rules['pnr'][3], $rules['note']);
            $rules += ['target_class' => ['required', 'string', 'max:50'], 'target_price' => ['required', 'numeric', 'min:0']];
        }

        return $request->validate($rules, [], [
            'name' => 'nom du groupe', 'country' => 'pays', 'departure_city' => 'ville de départ', 'arrival_city' => "ville d'arrivée",
            'seats' => 'nombre de places', 'cabin_class' => 'classe', 'price' => 'prix', 'option_date' => 'date limite',
            'pnr_created_on' => 'date de création du PNR', 'target_class' => 'classe visée', 'target_price' => 'prix visé',
        ]);
    }

    /** Types de passagers : Mr / Mrs à partir de 12 ans, CHD de 2 à 11 ans, INF de moins de 2 ans */
    private function checkPassengerAges(array $passengers, Carbon $travelDate): void
    {
        foreach ($passengers as $index => $passenger) {
            if (empty($passenger['birth_date'])) {
                continue;
            }
            $age = (int) Carbon::parse($passenger['birth_date'])->diffInYears($travelDate);
            $valid = match ($passenger['type']) {
                'INF' => $age < 2,
                'CHD' => $age >= 2 && $age <= 11,
                default => $age >= 12,
            };
            if (! $valid) {
                throw ValidationException::withMessages([
                    "passagers.$index.birth_date" => "{$passenger['first_name']} {$passenger['last_name']} : l'âge ($age ans) ne correspond pas au type {$passenger['type']}.",
                ]);
            }
        }
    }

    private function catalog(): array
    {
        $names = fn ($model) => $model::orderBy('name')->pluck('name');

        return [
            'countries' => $names(BlockPowerCountry::class),
            'companies' => $names(BlockPowerCompany::class),
            'departureCities' => $names(BlockPowerDepartureCity::class),
            'arrivalCities' => $names(BlockPowerArrivalCity::class),
        ];
    }
}
