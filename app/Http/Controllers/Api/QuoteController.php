<?php

namespace App\Http\Controllers\Api;

use App\Enums\Api\DevisReturnEnum;
use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    /**
     * Demande de devis envoyée depuis le site public.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'departure_city' => 'required|string|max:255',
            'arrival_city' => 'required|string|max:255',
            'departure_date' => 'required|date',
            'return_date' => 'nullable|date|after_or_equal:departure_date',
            'passagers_nb' => 'required|integer|min:1',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone_num' => 'required|string|max:30',
            'email' => 'required|email|max:150',
        ]);

        QuoteRequest::create([
            'departure_city' => $data['departure_city'],
            'arrival_city' => $data['arrival_city'],
            'departure_date' => $data['departure_date'],
            'return_date' => $data['return_date'] ?? null,
            'passengers_nb' => $data['passagers_nb'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone_num' => $data['phone_num'],
            'email' => $data['email'],
        ]);

        return response()->json(['code' => DevisReturnEnum::success]);
    }
}
