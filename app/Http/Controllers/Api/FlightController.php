<?php

namespace App\Http\Controllers\Api;

use App\Enums\Api\FlightApiCallEnum;
use App\Http\Controllers\Controller;
use App\Models\Flight;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Vols à venir d'une destination (identifiant d'URL de la destination, ex. « italie »).
 */
class FlightController extends Controller
{
    public function index(string $destination)
    {
        $flights = Flight::with(['escaliers', 'ville', 'company'])
            ->where('destination', $destination)
            ->where('date_aller', '>', Carbon::today())
            ->orderBy('date_aller')
            ->get();

        return response()->json($flights);
    }

    public function search(Request $request, string $destination)
    {
        $stops = fn ($q) => $q->with('airPortDe', 'airPortA');
        $departureAirport = $request->input('checkInAirport');
        $returnAirport = $request->input('checkOutAirport');

        $flights = Flight::with(['escaliers' => $stops, 'escaliers_aller' => $stops, 'escaliers_retour' => $stops, 'ville', 'company'])
            ->where('destination', $destination)
            ->where('date_aller', '>', Carbon::today())
            ->when($request->input('departureDate'), fn ($q, $date) => $q->whereDate('date_aller', $date))
            ->when($request->input('returnDate'), fn ($q, $date) => $q->whereDate('date_retour', $date))
            ->when($departureAirport && $departureAirport !== 'any', fn ($q) => $q->whereHas('escaliers',
                fn ($s) => $s->where(['type' => 0, 'num' => 1])->where('air_port_id', $departureAirport)))
            ->when($returnAirport && $returnAirport !== 'any', fn ($q) => $q->whereHas('escaliers',
                fn ($s) => $s->where(['type' => 1, 'num' => 1])->where('air_port_id', $returnAirport)))
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $flights]);
    }

    public function show(string $destination, int $flight)
    {
        $flight = Flight::with(['escaliers' => fn ($q) => $q->with('airPortDe', 'airPortA'), 'ville', 'company', 'conditions'])
            ->where('destination', $destination)
            ->find($flight);

        return $flight
            ? response()->json(['data' => $flight])
            : response()->json(FlightApiCallEnum::NOT_FLIGHT_FOUND, 404);
    }
}
