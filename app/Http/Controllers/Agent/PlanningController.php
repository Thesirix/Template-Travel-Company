<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\Agent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PlanningController extends Controller
{

    public function index()
    {
        $agents = Agent::all();

        // Données du mois affiché à l'ouverture, intégrées à la page : le planning s'affiche d'un coup,
        // sans attendre les appels AJAX (ils ne servent plus qu'en changeant de mois ou après une action).
        $start = now()->startOfMonth();
        $range = Request::create('', 'GET', ['start' => $start->toDateString(), 'end' => $start->copy()->addMonth()->toDateString()]);
        $yearPresences = DB::table('attendances')->where('date', '>=', $start->copy()->startOfYear()->toDateString())->count();
        $boot = [
            'start' => $start->toDateString(),
            'resources' => $this->resources()->getData(true),
            'events' => $this->events($range)->getData(true),
            'attendances' => $this->attendances($range)->getData(true),
            'metrics' => $this->metrics(Request::create('', 'GET', ['start' => $start->toDateString()]))->getData(true),
            'yearPresences' => $yearPresences,
        ];

        return view('agent.planning', compact('agents', 'boot'));
    }
    // -------------------------------------------------------------------------------
    public function events(Request $request)
    {
        $agences = \App\Models\Agency::all();

        // Seulement les congés qui touchent la période affichée par le calendrier
        $conges = LeaveRequest::with('agent.agence')
            ->when($request->query('end'), fn ($q, $end) => $q->where('start_date', '<', substr($end, 0, 10)))
            ->when($request->query('start'), fn ($q, $start) => $q->where('end_date', '>=', substr($start, 0, 10)))
            ->get();

        $events = collect();

        foreach ($conges as $conge) {
            $agent = $conge->agent;
            if (!$agent || !$agent->agence) {
                continue;
            }

            $backgroundColor = match (true) {
                $conge->type === 'maladie' || $conge->status === 'maladie' => '#f48bcc',
                $conge->status === 'approved' && $conge->type === 'repos'   => 'green',
                $conge->status === 'approved' && $conge->type === 'conge'   => '#27e6e6',
                $conge->status === 'rejected'                               => 'red',
                default                                                     => 'orange',
            };

            $resourceIds = [];

            if ($agent->is_super_agent) {
                //  l'événement du super agent est dupliqué pour chaque agence
                foreach ($agences as $ag) {
                    $resourceIds[] = 'gent-' . $agent->id . '-agence-' . $ag->id;
                }
            } else {
                //  agent normal : une seule ressource (sa propre agence)
                $resourceIds[] = 'gent-' . $agent->id . '-agence-' . $agent->agence->id;
            }

            // pousser un event par resourceId ciblée
            foreach ($resourceIds as $rid) {
                $events->push([
                    // id unique si dupliqué (évite collisions)
                    'id'               => $conge->id . '-' . md5($rid),
                    'title'            => $conge->name,
                    'start'            => $conge->start_date,
                    'end'              => $conge->end_date,
                    'resourceId'       => $rid,
                    'agent_name'       => $agent->name . ' ' . $agent->prenom,
                    'backgroundColor'  => $backgroundColor,
                    'status'           => $conge->status,
                    'rejection_reason' => $conge->rejection_reason,
                    'type'             => $conge->type,
                    'modification_token' => $conge->modification_token,
                    'jours_par_type'     => $conge->jours_par_type,
                ]);
            }
        }

        return response()->json($events->values());
    }

    // -------------------------------------------------------------------------------
    public function resources()
    {
        $agences = \App\Models\Agency::all();
        // Agents actifs seulement : un compte désactivé n'a plus sa place dans le planning
        $agents  = \App\Models\Agent::with('agence')->where('state', 'enable')->get();

        $resources = collect();

        foreach ($agents as $agent) {

            if (!$agent->agence) {
                continue;
            }

            if ($agent->is_super_agent) {
                //  le super agent apparaît dans TOUTES les agences
                foreach ($agences as $ag) {
                    $resources->push([
                        // id UNIQUE par agence
                        'id'      => 'gent-' . $agent->id . '-agence-' . $ag->id,
                        'title'   => $agent->name . ' ' . $agent->prenom,
                        'groupId' => $ag->name . '#' . $ag->id,
                    ]);
                }
            } else {
                // ➜agent normal : juste dans sa propre agence
                $resources->push([

                    'id'      => 'gent-' . $agent->id . '-agence-' . $agent->agence->id,
                    'title'   => $agent->name . ' ' . $agent->prenom,
                    'groupId' => $agent->agence->name . '#' . $agent->agence->id,
                ]);
            }
        }

        return response()->json($resources->values());
    }

    // -------------------------------------------------------------------------------

    public function attendances(Request $request)
    {
        $start  = $request->query('start');
        $end    = $request->query('end');
        $gentId = $request->query('gent_id');

        $agences = \App\Models\Agency::all();
        $rows = DB::table('attendances')
            ->select('gent_id', 'date')
            ->when($start, fn($q) => $q->where('date', '>=', $start))
            ->when($end, fn($q) => $q->where('date', '<=', $end))
            ->when($gentId, fn($q) => $q->where('gent_id', $gentId))
            ->orderBy('date')
            ->get();

        $byAgent = [];
        // Agents chargés une seule fois (et non une requête par ligne de présence)
        $agents = \App\Models\Agent::whereIn('id', $rows->pluck('gent_id')->unique())->get()->keyBy('id');

        foreach ($rows as $row) {
            $gent = $agents->get($row->gent_id);
            if (!$gent) continue;

            // Cas super agent présent dans toutes les agences
            if ($gent->is_super_agent) {
                foreach ($agences as $ag) {
                    $key = "gent-{$gent->id}-agence-{$ag->id}";
                    $byAgent[$key][] = \Carbon\Carbon::parse($row->date)->toDateString();
                }
            } elseif ($gent->agence_id) {
                // Agent normal  uniquement sa propre agence
                $key = "gent-{$gent->id}-agence-{$gent->agence_id}";
                $byAgent[$key][] = \Carbon\Carbon::parse($row->date)->toDateString();
            }
        }

        return response()->json($byAgent);
    }

    // -------------------------------------------------------------------------------
    public function agentCounters(Request $request)
    {
        // Agent demandé, ou l'agent connecté
        $id = $request->input('gent_id', auth('gent')->id());
        $gent = Agent::findOrFail($id);
        $monthStart = Carbon::now()->startOfMonth();

        $rows = DB::table('leave_counters')
            ->where('gent_id', $id)
            ->whereIn('type', ['conge', 'repos', 'ferie', 'autre'])
            ->pluck('balance', 'type');

        $balances = [
            'conge' => (float)($rows['conge'] ?? 0),
            'repos' => (float)($rows['repos'] ?? 0),
            'ferie' => (float)($rows['ferie'] ?? 0),
            'autre' => (float)($rows['autre'] ?? 0),
        ];

        $openingRow = DB::table('leave_openings')
            ->where('gent_id', $id)
            ->where('month_start', $monthStart->toDateString())
            ->first();

        $opening = $openingRow ? (float)$openingRow->opening : 0.0;
        $adjust  = $openingRow ? (float)$openingRow->adjust  : 0.0;

        $service = app(\App\Services\LeaveService::class);
        $cotise = $service->monthlyAccrual($gent, $monthStart);
        $pris   = $service->takenInMonth($gent, $monthStart);

        $restant = round($opening + $adjust + $cotise - $pris, 2);

        return response()->json([
            'balances'   => $balances,
            'ouverture'  => $opening,
            'ajustement' => $adjust,
            'cotise'     => $cotise,
            'pris'       => $pris,
            'restant'    => $restant,
        ]);
    }

    public function updateCounters(Request $request)
    {
        $data = $request->validate([
            'gent_id' => 'required|integer|exists:gents,id',
            'conge'   => 'required|numeric',
            'repos'   => 'required|numeric',
            'ferie'   => 'required|numeric',
            'autre'   => 'required|numeric',
        ]);

        $gentId = (int) $data['gent_id'];
        $monthStart = \Carbon\Carbon::now()->startOfMonth()->toDateString();
        $gent = \App\Models\Agent::findOrFail($gentId);
        $leaveService = app(\App\Services\LeaveService::class);

        $newBalances = [
            'conge' => max(0, (float) $data['conge']),
            'repos' => max(0, (float) $data['repos']),
            'ferie' => max(0, (float) $data['ferie']),
            'autre' => max(0, (float) $data['autre']),
        ];
        $newTotal = array_sum($newBalances);

        DB::beginTransaction();

        try {

            $previousBalances = DB::table('leave_counters')
                ->where('gent_id', $gentId)
                ->whereIn('type', ['conge', 'repos', 'ferie', 'autre'])
                ->pluck('balance', 'type')
                ->map(fn($v) => (float) $v);
            $previousTotal = $previousBalances->sum();

            foreach ($newBalances as $type => $value) {
                DB::table('leave_counters')->updateOrInsert(
                    ['gent_id' => $gentId, 'type' => $type],
                    ['balance' => $value]
                );
            }

            $openingRow = DB::table('leave_openings')
                ->where('gent_id', $gentId)
                ->where('month_start', $monthStart)
                ->first();

            if ($openingRow) {

                $delta = $newTotal - $previousTotal;
                $newAdjust = ((float) $openingRow->adjust) + $delta;

                DB::table('leave_openings')
                    ->where('id', $openingRow->id)
                    ->update([
                        'adjust'     => round($newAdjust, 2),
                        'updated_at' => now(),
                    ]);
            } else {

                if (!empty($gent->contract_start_date)) {

                    $opening = $leaveService->openingBalance($gent, \Carbon\Carbon::parse($monthStart));
                    $adjust = round($newTotal - $previousTotal, 2);
                } else {

                    $opening = round($newTotal, 2);
                    $adjust = 0.0;
                }

                DB::table('leave_openings')->insert([
                    'gent_id'     => $gentId,
                    'month_start' => $monthStart,
                    'opening'     => $opening,
                    'adjust'      => $adjust,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'balances' => $newBalances,
                'restant'  => round($newTotal, 2),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '⛔ Erreur lors de la mise à jour des soldes.',
            ], 500);
        }
    }

    // ---------------Approuve une demande de congé-----------------------

    public function approve(Request $request, $id)
    {
        $request->validate([
            'modification_token' => 'required|uuid',
        ]);

        $conge = LeaveRequest::findOrFail($id);

        if ($conge->modification_token !== $request->modification_token) {
            return response()->json([
                'success' => false,
                'concurrency_error' => true,
                'message' => '⛔ Cette proposition a déjà été modifiée. Merci de rafraîchir la page.'
            ], 409);
        }

        $conge->status = 'approved';
        $conge->modification_token = Str::uuid()->toString();
        $conge->save();

        return response()->json([
            'success' => true,
            'status' => 'approved',
            'modification_token' => $conge->modification_token
        ]);
    }

    // ---------------------------Rejette une demande de congé-------------------------

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:255']);

        $conge = LeaveRequest::findOrFail($id);

        // Mise à jour du statut et du motif
        $conge->status = 'rejected';
        $conge->rejection_reason = $request->reason;

        $conge->modification_token = Str::uuid()->toString();

        $conge->save();

        return response()->json([
            'success' => true,
            'status' => 'rejected',
            'modification_token' => $conge->modification_token,
        ]);
    }

    // ----------------------------Met à jour un congé-------------------------

    public function update(Request $request, $id)
    {

        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'status'     => 'required|in:pending,approved,rejected,maladie',
            'reason'     => 'nullable|string|max:255',
            'modification_token' => 'required|uuid',
            'jours_par_type' => 'required|string',
        ]);
        return DB::transaction(function () use ($request, $id) {
            $conge = LeaveRequest::findOrFail($id);

            if ($conge->modification_token !== $request->input('modification_token')) {
                return response()->json([
                    'success' => false,
                    'concurrency_error' => true,
                    'message' => '⛔ Cette proposition a déjà été modifiée. Merci de rafraîchir la page.'
                ], 409);
            }

            $ancien_jours_par_type = (json_decode($conge->jours_par_type, true) ?: []) + [
                'conge' => 0,
                'repos' => 0,
                'ferie' => 0,
                'autre' => 0,
                'maladie' => 0
            ];

            $conge->start_date = $request->start_date;
            $conge->end_date = $request->end_date;
            $conge->rejection_reason = $request->reason;

            $startInc     = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $endExclusive = \Carbon\Carbon::parse($request->end_date)->startOfDay();
            $endInc       = $endExclusive->copy()->subDay(); // borne inclusive

            // Jours ouvrés (sans dimanche ni fériés), inclusif — identique au JS
            $nbJours = $this->businessDaysInclusive($startInc, $endInc);

            $joursParType = json_decode($request->jours_par_type, true) ?? [];

            if ($request->status === 'maladie') {

                // On crédite  tous les anciens types 
                foreach ($ancien_jours_par_type as $type => $nb) {
                    if ($type !== 'maladie' && intval($nb) > 0) {
                        DB::table('leave_counters')
                            ->where('gent_id', $conge->gent_id)
                            ->where('type', $type)
                            ->increment('balance', intval($nb));
                    }
                }
                // On enlève l'ancien de maladie 
                if (intval($ancien_jours_par_type['maladie']) > 0) {
                    DB::table('leave_counters')
                        ->where('gent_id', $conge->gent_id)
                        ->where('type', 'maladie')
                        ->decrement('balance', intval($ancien_jours_par_type['maladie']));
                }
                // On ajoute le nouveau solde maladie
                if ($nbJours > 0) {
                    DB::table('leave_counters')
                        ->where('gent_id', $conge->gent_id)
                        ->where('type', 'maladie')
                        ->increment('balance', $nbJours);
                }

                $conge->jours_par_type = json_encode([
                    'conge'   => 0,
                    'repos'   => 0,
                    'ferie'   => 0,
                    'autre'   => 0,
                    'maladie' => $nbJours
                ]);
                $conge->type = 'maladie';
                $conge->status = 'maladie';
            } else {

                $totalSaisi = array_sum($joursParType);
                $delta = $nbJours - $totalSaisi;

                if ($request->status !== 'rejected' && $delta !== 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "⛔ La répartition ne correspond pas à la durée ($nbJours jour(s))."
                    ], 422);
                }

                //  -------------répartition---------
                foreach ($ancien_jours_par_type as $type => $nb) {
                    if ($nb > 0) {
                        DB::table('leave_counters')
                            ->where('gent_id', $conge->gent_id)
                            ->where('type', $type)
                            ->increment('balance', $nb);
                    }
                }

                foreach ($joursParType as $type => $nb) {
                    if ($nb > 0) {
                        DB::table('leave_counters')
                            ->where('gent_id', $conge->gent_id)
                            ->where('type', $type)
                            ->decrement('balance', $nb);
                    }
                }

                $conge->jours_par_type = json_encode($joursParType);

                $mainType = $request->input('type') ?? 'conge';
                $conge->type = $mainType;
                $conge->status = $request->status;
            }

            $conge->modification_token = Str::uuid()->toString();
            $conge->save();

            return response()->json([
                'success' => true,
                'start_date' => $conge->start_date,
                'end_date' => $conge->end_date,
                'status' => $conge->status,
                'reason' => $conge->rejection_reason,
                'type' => $conge->type,
                'jours_par_type' => $conge->jours_par_type,
                'modification_token' => $conge->modification_token,
            ]);
        });
    }

    // ---------------------------ajoute un congé---------------

    public function store(Request $request)
    {
        $validated = $request->validate([
            'resourceId'      => 'required|integer|exists:gents,id',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'title'           => 'required|string|max:255',
            'jours_par_type'  => 'required|string',
        ]);

        return DB::transaction(function () use ($request, $validated) {

            $jours_par_type = json_decode($validated['jours_par_type'], true);
            if (!$jours_par_type || !is_array($jours_par_type)) {
                return response()->json(['success' => false, 'message' => 'Format jours_par_type invalide.']);
            }

            $start = $validated['start_date'];
            $end   = $validated['end_date'];

            $title = $validated['title'];
            $gent_id = $validated['resourceId'];
            $type  = $request->input('type');

            if ($type === 'maladie' || (!empty($jours_par_type['maladie']) && $jours_par_type['maladie'] > 0)) {
                $nbJours = (int) (\Carbon\Carbon::parse($end)->startOfDay()
                    ->diffInDays(\Carbon\Carbon::parse($start)->startOfDay(), true));

                $jours_par_type_maladie = [
                    'conge'   => 0,
                    'repos'   => 0,
                    'ferie'   => 0,
                    'autre'   => 0,
                    'maladie' => $nbJours
                ];

                $calendar = LeaveRequest::create([
                    'gent_id'        => $gent_id,
                    'name'           => $title,
                    'start_date'     => $start,
                    'end_date'       => $end,
                    'type'           => 'maladie',
                    'status'         => 'maladie',
                    'jours_par_type' => json_encode($jours_par_type_maladie),
                    'rejection_reason' => null,
                    'modification_token' => Str::uuid()->toString(),
                ]);

                // On crédite le solde maladie 
                DB::table('leave_counters')->updateOrInsert(
                    ['gent_id' => $gent_id, 'type' => 'maladie'],
                    ['balance' => DB::raw("COALESCE(balance,0)+$nbJours")]
                );

                return response()->json(['success' => true, 'id' => $calendar->id]);
            }

            if (!empty($jours_par_type)) {
                foreach ($jours_par_type as $typeKey => $nb) {
                    if ($typeKey === 'maladie') continue;
                    $nb = intval($nb);
                    if ($nb > 0) {
                        $leaveCounter = DB::table('leave_counters')
                            ->where('gent_id', $gent_id)
                            ->where('type', $typeKey)
                            ->first();

                        $balance = $leaveCounter ? intval($leaveCounter->balance) : 0;

                        if (!$leaveCounter || $leaveCounter->balance < $nb) {
                            return response()->json(['success' => false,   'message' => "Solde insuffisant pour le type : $typeKey (disponible $balance, demandé $nb)"]);
                        }
                    }
                }

                foreach ($jours_par_type as $typeKey => $nb) {
                    if ($typeKey === 'maladie') continue;
                    $nb = intval($nb);
                    if ($nb > 0) {
                        DB::table('leave_counters')
                            ->where('gent_id', $gent_id)
                            ->where('type', $typeKey)
                            ->decrement('balance', $nb);
                    }
                }
            }

            $mainType = $type ?? array_key_first(array_filter($jours_par_type, fn($v) => intval($v) > 0));
            $calendar = LeaveRequest::create([
                'gent_id'        => $gent_id,
                'name'           => $title,
                'start_date'     => $start,
                'end_date'       => $end,
                'type'           => $mainType,
                'status'         => 'approved',
                'jours_par_type' => json_encode($jours_par_type),
                'rejection_reason' => null,
                'modification_token' => Str::uuid()->toString(),
            ]);
            return response()->json(['success' => true, 'id' => $calendar->id]);
        });
    }

    public function destroy(Request $request, $id)
    {
        $request->validate([
            'modification_token' => 'required|uuid',
        ]);

        return DB::transaction(function () use ($request, $id) {

            $calendar = LeaveRequest::find($id);

            if (!$calendar) {
                return response()->json([
                    'success' => false,
                    'message' => '⛔ Événement introuvable ou déjà supprimé.'
                ], 404);
            }

            // Vérification du token de modification
            if ($calendar->modification_token !== $request->input('modification_token')) {
                return response()->json([
                    'success' => false,
                    'concurrency_error' => true,
                    'message' => '⛔ Cette proposition a déjà été modifiée ou supprimée. Merci de rafraîchir la page pour voir la version à jour.'
                ], 409);
            }

            // Traitement des jours à réinjecter selon les types
            $joursParType = json_decode($calendar->jours_par_type, true);

            if (is_array($joursParType)) {
                foreach ($joursParType as $type => $nb) {
                    $nb = intval($nb);
                    if ($nb > 0) {
                        if ($type === 'maladie') {
                            DB::table('leave_counters')
                                ->where('gent_id', $calendar->gent_id)
                                ->where('type', 'maladie')
                                ->decrement('balance', $nb);
                        } else {
                            DB::table('leave_counters')
                                ->where('gent_id', $calendar->gent_id)
                                ->where('type', $type)
                                ->increment('balance', $nb);
                        }
                    }
                }
            } else {
                // Récupération manuelle si l'objet ne contient pas de détails
                $start = \Carbon\Carbon::parse($calendar->start_date)->startOfDay();
                $end = \Carbon\Carbon::parse($calendar->end_date)->startOfDay();
                $nbJours = (int) ($end->diffInDays($start, true));

                if ($calendar->type === 'maladie') {
                    DB::table('leave_counters')
                        ->where('gent_id', $calendar->gent_id)
                        ->where('type', 'maladie')
                        ->decrement('balance', $nbJours);
                } else {
                    DB::table('leave_counters')
                        ->where('gent_id', $calendar->gent_id)
                        ->where('type', $calendar->type)
                        ->increment('balance', $nbJours);
                }
            }

            $calendar->delete();

            return response()->json([
                'success' => true,
                'id'      => (int) $id,
                'message' => '✅ Événement supprimé avec succès.'
            ]);
        });
    }

    public function metrics(Request $request)
    {
        $service = app(\App\Services\LeaveService::class);

        $refDate = $request->query('start')
            ? \Carbon\Carbon::parse($request->query('start'))->startOfMonth()
            : \Carbon\Carbon::now()->startOfMonth();
        $monthStart = $refDate->copy();

        $agents = \App\Models\Agent::select('id', 'name', 'prenom', 'contract_start_date')->where('state', 'enable')->get();
        $service->preload($agents->pluck('id')->all());

        $result = [];

        foreach ($agents as $agent) {
            $openingRow = $service->openingOf($agent->id, $monthStart);

            if ($openingRow) {
                // Lecture manuelle admin (agent ancien)
                $opening = (float) $openingRow->opening;
                $adjust  = (float) $openingRow->adjust;
            } else {
                //  Calcul dynamique (agent récent)
                $opening = $service->openingBalance($agent, $monthStart);
                $adjust  = 0.0;
            }

            // Reste inchangé
            $cotise  = $service->monthlyAccrual($agent, $monthStart);
            $pris    = $service->takenInMonth($agent, $monthStart);
            $restant = round($opening + $adjust + $cotise - $pris, 2);

            $result[] = [
                'agent_id'     => $agent->id,
                'agent_nom'    => $agent->name,
                'agent_prenom' => $agent->prenom,
                'solde'        => round($opening, 2),
                'ajustement'   => round($adjust, 2),
                'cotiser'      => round($cotise, 2),
                'pris'         => round($pris, 2),
                'restant'      => $restant,
            ];
        }

        return response()->json($result);
    }

    private function businessDaysInclusive(\Carbon\Carbon $startInc, \Carbon\Carbon $endInc): int
    {

        $holidays = ['01-05', '14-07', '15-08', '25-12'];
        if ($endInc->lt($startInc)) return 0;

        $count = 0;
        $cursor = $startInc->copy();
        while ($cursor->lte($endInc)) {
            $isSunday  = $cursor->isSunday();
            $isHoliday = in_array($cursor->format('d-m'), $holidays, true);
            if (!$isSunday && !$isHoliday) {
                $count++;
            }
            $cursor->addDay();
        }
        return $count;
    }
}
