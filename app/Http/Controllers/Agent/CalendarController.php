<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Services\LeaveService;

class CalendarController extends Controller
{

    public function events(Request $request)
    {
        $gent = Auth::guard('gent')->user();
        $agenceId = $request->query('agence_id');

        // Cas 1 : Super agent ou agent ayant le droit admin (Gate) ---
        if ($gent->is_super_agent || Gate::forUser($gent)->allows('gerer Planning')) {

            $query = LeaveRequest::with('agent.agence');

            // Si on a demandé une agence spécifique, on filtre dessus
            if (!empty($agenceId)) {
                $query->whereHas('agent', function ($q) use ($agenceId) {
                    $q->where('agence_id', $agenceId);
                });
            }

            $events = $query->get();
        } else {
            //  Cas 2 : Agent normal  uniquement les congés de son agence ---
            $events = LeaveRequest::whereHas('agent', function ($query) use ($gent) {
                $query->where('agence_id', $gent->agence_id);
            })->with('agent.agence')->get();
        }


        $result = $events->map(function ($event) use ($gent) {

            if ($event->type === 'maladie') {
                return [
                    'id'              => $event->id,
                    'title'           => $event->name,
                    'start'           => $event->start_date,
                    'end'             => $event->end_date,
                    'backgroundColor' => '#f48bcc',
                    'extendedProps'   => [
                        'status'           => 'maladie',
                        'rejection_reason' => null,
                        'gent_id'          => $event->gent_id,
                        'jours_par_type'   => $event->jours_par_type,
                    ],
                ];
            }

            $backgroundColor = match (true) {
                $event->status === 'approved' && $event->type === 'repos' => 'green',
                $event->status === 'approved' && $event->type === 'conge' => '#27e6e6',
                $event->status === 'rejected' => 'red',
                default => 'orange',
            };

            return [
                'id'              => $event->id,
                'title'           => $event->name,
                'start'           => $event->start_date,
                'end'             => $event->end_date,
                'backgroundColor' => $backgroundColor,
                'extendedProps'   => [
                    'status'           => $event->status,
                    'rejection_reason' => ($event->gent_id === $gent->id) ? $event->rejection_reason : null,
                    'gent_id'          => $event->gent_id,
                    'jours_par_type'   => $event->jours_par_type,
                    'type'             => $event->type,
                ],
            ];
        });

        return response()->json($result);
    }
    public function counters(Request $request)
    {
        $gentId = auth()->id();

        $counters = DB::table('leave_counters')
            ->where('gent_id', $gentId)
            ->pluck('balance', 'type');

        return response()->json([
            'success' => true,
            'balances' => $counters
        ]);
    }



    public function store(Request $request)
    {
        $gent = Auth::guard('gent')->user();

        $request->validate([
            'title'      => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);

        return DB::transaction(function () use ($request, $gent, $start, $end) {

            //on repère les jours fériés pour les exclure du décompte
            $joursFeriesTrouves = [];
            $current = $start->copy();
            while ($current->lte($end)) {
                if (LeaveService::isJourFerie($current)) {
                    $joursFeriesTrouves[] = $current->format('d/m/Y');
                }
                $current->addDay();
            }


            $type = $request->input('type');
            $id   = $request->input('id');

            if ($id) {
                // pas utilisé ici
            }

            if ($type !== 'maladie') {
                $overlap = LeaveRequest::where('gent_id', $gent->id)
                    ->whereNotIn('status', ['rejected'])
                    ->where('type', '!=', 'maladie')
                    ->where('start_date', '<', $end)
                    ->where('end_date',   '>', $start)
                    ->exists();

                if ($overlap) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Chevauchement détecté avec un autre congé.',
                    ]);
                }
            }

            if ($type === 'maladie') {
                $calendar = LeaveRequest::create([
                    'gent_id'        => $gent->id,
                    'name'           => $request->title,
                    'start_date'     => $start,
                    'end_date'       => $end,
                    'type'           => 'maladie',
                    'status'         => 'maladie',
                    'jours_par_type' => null,
                ]);

                $nbJours = (int) (\Carbon\Carbon::parse($end)->startOfDay()
                    ->diffInDays(
                        \Carbon\Carbon::parse($start)->startOfDay()
                    , true));

                DB::table('leave_counters')
                    ->updateOrInsert(
                        ['gent_id' => $gent->id, 'type' => 'maladie'],
                        ['balance' => DB::raw("COALESCE(balance,0) + $nbJours")]
                    );


                return response()->json(['success' => true, 'id' => $calendar->id]);
            }

            $joursParType = [];
            if ($request->has('jours_par_type')) {
                foreach ($request->jours_par_type as $k => $nb) {
                    $jourType = match ($k) {
                        'conge' => 'conge',
                        'ferie' => 'ferie',
                        'repos' => 'repos',
                        'autre' => 'autre',
                        default => $k,
                    };
                    $joursParType[$jourType] = (int)$nb;
                }
            }


            $totalSel = array_sum($joursParType);
            $nbRepos  = $joursParType['repos'] ?? 0;

            $primaryType = ($totalSel > 0 && $nbRepos === $totalSel)
                ? 'repos'
                : 'conge';

            if (!empty($joursParType)) {
                foreach ($joursParType as $jourType => $nb) {
                    if ($nb > 0) {
                        $leaveCounter = DB::table('leave_counters')
                            ->where('gent_id', $gent->id)
                            ->where('type', $jourType)
                            ->first();
                        if (!$leaveCounter || $leaveCounter->balance < $nb) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Solde insuffisant pour le type : ' . $jourType
                            ]);
                        }
                    }
                }
                foreach ($joursParType as $jourType => $nb) {
                    if ($nb > 0) {
                        DB::table('leave_counters')
                            ->where('gent_id', $gent->id)
                            ->where('type', $jourType)
                            ->decrement('balance', $nb);
                    }
                }
            }

            $calendar = LeaveRequest::create([
                'gent_id'        => $gent->id,
                'name'           => $request->title,
                'start_date'     => $start,
                'end_date'       => $end,
                'type'           => $primaryType,
                'status'         => 'pending',
                'jours_par_type' => json_encode($joursParType),
            ]);

            if (!empty($joursFeriesTrouves)) {
                return response()->json([
                    'success' => true,
                    'id' => $calendar->id,
                    'warning' => '⚠️ Les jours fériés suivants ne sont pas comptés : ' . implode(', ', $joursFeriesTrouves)
                ]);
            }


            return response()->json(['success' => true, 'id' => $calendar->id]);
        });
    }



    // --------------------------delete--------------------------------
    public function destroy(Request $request)
    {
        $calendar = LeaveRequest::find($request->id);
        if (!$calendar) {
            return response()->json(['error' => '⛔ événement déja supprimer'], 404);
        }


        if ($calendar->gent_id !== Auth::guard('gent')->id()) {
            return response()->json(['error' => 'Accès refusé'], 403);
        }

        if ($calendar->status === 'approved') {
            return response()->json(['error' => '⛔ Vous ne pouvez pas supprimer un événement validé'], 403);
        }
        return DB::transaction(function () use ($request, $calendar) {

            $joursParType = json_decode($calendar->jours_par_type, true);
            if (is_array($joursParType)) {
                foreach ($joursParType as $type => $nb) {
                    if ($nb > 0) {
                        if ($type === 'maladie') {
                            // Pour la maladie decrement
                            DB::table('leave_counters')
                                ->where('gent_id', $calendar->gent_id)
                                ->where('type', 'maladie')
                                ->decrement('balance', $nb);
                        } else {
                            // Pour les autres types increment
                            DB::table('leave_counters')
                                ->where('gent_id', $calendar->gent_id)
                                ->where('type', $type)
                                ->increment('balance', $nb);
                        }
                    }
                }
            } else {

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
            return response()->json(['success' => true, 'id' => $request->id]);
        });
    }


    // ---------------------------update--------------------------------


    public function update(Request $request)
    {
        $gent = Auth::guard('gent')->user();

        $request->validate([
            'id'             => 'required|integer',
            'title'          => 'required|string|max:255',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'jours_par_type' => 'sometimes|array',
            'jours_par_type.*' => 'integer|min:0',
        ]);

        $calendar = LeaveRequest::find($request->id);
        if (!$calendar) {
            return response()->json(['success' => false, 'message' => '⛔ événement déja supprimer'], 404);
        }
        if ($calendar->gent_id !== $gent->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        if ($calendar->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => '⛔ Cet événement a été validé entre-temps. Vous ne pouvez plus le modifier.'
            ], 403);
        }

        return DB::transaction(function () use ($request, $gent, $calendar) {
            // ---------------chevauchement---------------

            $nouveauType = $request->type ?? ($request->has('jours_par_type')
                ? array_key_first(array_filter($request->jours_par_type, fn($v) => $v > 0))
                : $calendar->type);

            if ($nouveauType !== 'maladie') {
                $start = $request->start_date;
                $end   = $request->end_date;
                $calendarId = $calendar->id;

                $chevauchement = LeaveRequest::where('gent_id', $gent->id)
                    ->whereNotIn('status', ['rejected'])
                    ->where('type', '!=', 'maladie') // on exclut les congés rejetés et maladie
                    ->where('id', '!=', $calendarId)          // on exclut l’événement qu’on modifie
                    ->where('start_date', '<', $end)
                    ->where('end_date',   '>', $start)
                    ->exists();

                if ($chevauchement) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Chevauchement détecté avec un autre congé.'
                    ]);
                }
            }


            // Restaurer les jours déjà débités 
            $old = json_decode($calendar->jours_par_type, true);
            if ($calendar->type === 'maladie') {
                // retire le crédit de maladie
                $nb = (int) (\Carbon\Carbon::parse($calendar->end_date)
                    ->diffInDays(\Carbon\Carbon::parse($calendar->start_date), true));
                DB::table('leave_counters')
                    ->where('gent_id', $calendar->gent_id)
                    ->where('type', 'maladie')
                    ->decrement('balance', $nb);
            } elseif (is_array($old)) {
                // ré‐ajoute chaque type
                foreach ($old as $type => $nb) {
                    if ($nb > 0) {
                        DB::table('leave_counters')
                            ->where('gent_id', $calendar->gent_id)
                            ->where('type', $type)
                            ->increment('balance', $nb);
                    }
                }
            }

            if ($request->has('jours_par_type')) {
                $new = $request->jours_par_type;

                foreach ($new as $type => $nb) {
                    if ($nb > 0) {
                        $ctr = DB::table('leave_counters')
                            ->where('gent_id', $gent->id)
                            ->where('type', $type)
                            ->value('balance');
                        if ($ctr === null || $ctr < $nb) {
                            return response()->json([
                                'success' => false,
                                'message' => "Solde insuffisant pour le type $type"
                            ]);
                        }
                    }
                }

                foreach ($new as $type => $nb) {
                    if ($nb > 0) {
                        DB::table('leave_counters')
                            ->where('gent_id', $gent->id)
                            ->where('type', $type)
                            ->decrement('balance', $nb);
                    }
                }


                $calendar->jours_par_type = json_encode($new);
                $calendar->type = $request->type ?? array_key_first(array_filter($new, fn($v) => $v > 0));
            }


            $calendar->name       = $request->title;
            $calendar->start_date = $request->start_date;
            $calendar->end_date   = $request->end_date;

            if ($request->has('type') && $request->type === 'maladie') {
                $calendar->status = 'maladie';
            } else {
                $calendar->status = 'pending';
            }

            $calendar->save();


            return response()->json(['success' => true]);
        });
    }


    // ------------------drag n drop----------------------------------




    public function attendances()
    {
        $gentId = Auth::guard('gent')->id();

        $dates = DB::table('attendances')
            ->where('gent_id', $gentId)
            ->pluck('date')
            ->map(function ($date) {
                return Carbon::parse($date)->toDateString();
            });

        return response()->json($dates);
    }
}
