<?php

namespace App\Http\Controllers\Api;

use App\Enums\Api\GeneralReturnEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Compte du client connecté au site public.
 */
class AccountController extends Controller
{
    public function show()
    {
        return response()->json(['code' => GeneralReturnEnum::success, 'data' => Auth::guard('client')->user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'firstName' => 'required|string|max:100',
            'lastName' => 'required|string|max:100',
            'phoneNumber' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
        ]);

        $client = Auth::guard('client')->user();
        $client->firstName = $data['firstName'];
        $client->lastName = $data['lastName'];
        $client->phoneNumber = $data['phoneNumber'] ?? $client->phoneNumber;
        if (! empty($data['password'])) {
            $client->password = Hash::make($data['password']);
        }
        $client->save();

        return response()->json(['code' => GeneralReturnEnum::success, 'data' => $client]);
    }
}
