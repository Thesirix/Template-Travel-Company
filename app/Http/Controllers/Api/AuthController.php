<?php

namespace App\Http\Controllers\Api;

use App\Enums\Api\GeneralReturnEnum;
use App\Enums\Api\OtpStatusEnum;
use App\Enums\OtpTypes;
use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationMail;
use App\Models\Client;
use App\Services\Api\AuthService;
use App\Services\Api\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Comptes clients du site public : inscription avec code reçu par e-mail, connexion, déconnexion.
 * Le jeton JWT est posé en cookie HttpOnly (access_token) avec un indicateur lisible « isLogged ».
 */
class AuthController extends Controller
{
    public function __construct(private OtpService $otp, private AuthService $auth)
    {
    }

    public function sendOtp(Request $request)
    {
        $email = $request->validate(['email' => ['required', 'email', 'max:255']])['email'];
        if (Client::where('email', $email)->exists()) {
            return response()->json(['code' => OtpStatusEnum::ClientExist]);
        }
        $code = $this->otp->generate($email, OtpTypes::SIGN_UP->value);
        if (! $code) {
            return response()->json(['code' => OtpStatusEnum::MAX_GENERATED], 429);
        }
        Mail::to($email)->send(new EmailVerificationMail($code));

        return response()->json(['code' => OtpStatusEnum::OtpGenerated]);
    }

    /** Code correct : le compte est créé et le client connecté */
    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:clients,email'],
            'otp' => ['required', 'string'],
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'phoneNumber' => ['required', 'string', 'max:30', 'unique:clients,phoneNumber'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        $status = $this->otp->verify($data['email'], $data['otp'], OtpTypes::SIGN_UP->value);
        if ($status !== OtpStatusEnum::Identical) {
            return response()->json(['code' => $status], 422);
        }

        $client = Client::create([
            'email' => $data['email'],
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'phoneNumber' => $data['phoneNumber'],
            'password' => Hash::make($data['password']),
            'provenance' => 'Site web',
        ]);

        return $this->auth->login($data['email'], $data['password'], OtpStatusEnum::AccountCreated, $client);
    }

    public function login(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        return $this->auth->login($data['email'], $data['password'], OtpStatusEnum::AccountCreated);
    }

    public function logout()
    {
        Auth::guard('client')->logout();

        return response()->json(['code' => GeneralReturnEnum::success])
            ->withCookie(cookie()->forget('isLogged'))
            ->withCookie(cookie()->forget('access_token'));
    }
}
