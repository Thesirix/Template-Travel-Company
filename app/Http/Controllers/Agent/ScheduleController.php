<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ScheduleEvent;

class ScheduleController extends Controller
{

    public function events()
    {
        $events = ScheduleEvent::where('gent_id', Auth::guard('gent')->id())->get();

        return response()->json($events->map(function ($event) {
            return [
                'id'    => $event->id,
                'title' => $event->title,
                'start' => $event->start_date,
                'end'   => $event->end_date,
                'backgroundColor' => $event->color,
            ];
        }));
    }

    public function store(Request $request)
    {
        $colors = [
            '#1067c4', '#007bff', '#28a745', '#ffc107', '#17a2b8', '#6f42c1',
            '#fd7e14', '#6610f2', '#e83e8c', '#20c997', '#d63384', '#198754',
            '#0dcaf0', '#ff6f61', '#6f42c1', '#ff9f00', '#795548', '#009688',
            '#3f51b5', '#9c27b0', '#00bcd4', '#ff5722', '#4caf50', '#cddc39'
        ];

        // Récupération du dernier événement de l'agent pour éviter la répétition
        $last = ScheduleEvent::where('gent_id', Auth::guard('gent')->id())->orderByDesc('id')->first();
        $lastColor = $last->color ?? null;

        // Filtre pour retirer la dernière couleur utilisée
        $availableColors = array_values(array_filter($colors, fn($c) => $c !== $lastColor));
        $color = $availableColors[array_rand($availableColors)];

        // Création de l'événement en stockant la couleur sous la clé 'color'
        $event = ScheduleEvent::create([
            'gent_id'    => Auth::guard('gent')->id(),
            'title'      => $request->title,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'color'      => $color,
        ]);

        return response()->json([
            'success' => true,
            'id'      => $event->id,
            'color'   => $event->color
        ]);
    }

    public function update(Request $request)
    {
        $event = ScheduleEvent::where('id', $request->id)
            ->where('gent_id', Auth::guard('gent')->id())
            ->firstOrFail();

        $event->update([
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        ScheduleEvent::where('id', $request->id)
            ->where('gent_id', Auth::guard('gent')->id())
            ->delete();

        return response()->json(['success' => true]);
    }

    public function updateColor(Request $request)
    {
        $request->validate([
            'id'    => 'required|exists:emplois,id',
            'color' => 'required|string'
        ]);

        $event = ScheduleEvent::where('id', $request->id)
            ->where('gent_id', Auth::guard('gent')->id())
            ->first();

        if (!$event) {
            return response()->json(['success' => false, 'error' => "Événement non trouvé ou accès refusé."]);
        }

        $event->update(['color' => $request->color]);

        return response()->json(['success' => true]);
    }
}
