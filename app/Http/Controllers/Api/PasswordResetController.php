<?php

namespace App\Http\Controllers\Api;

use App\Enums\Api\OtpStatusEnum;
use App\Enums\OtpTypes;
use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\Client;
use App\Services\Api\AuthService;
use App\Services\Api\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Mot de passe oublié : code par e-mail, puis nouveau mot de passe et connexion.
 */
class PasswordResetController extends Controller
{
    public function __construct(private OtpService $otp, private AuthService $auth)
    {
    }

    public function sendOtp(Request $request)
    {
        $email = $request->validate(['email' => ['required', 'email', 'max:255']])['email'];
        if (! Client::where('email', $email)->exists()) {
            return response()->json(['code' => OtpStatusEnum::ClientNoExist], 404);
        }
        $code = $this->otp->generate($email, OtpTypes::RESET_PASSWORD->value);
        if (! $code) {
            return response()->json(['code' => OtpStatusEnum::MAX_GENERATED], 429);
        }
        Mail::to($email)->send(new ResetPasswordMail($code));

        return response()->json(['code' => OtpStatusEnum::OtpGenerated]);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:clients,email'],
            'otp' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        $status = $this->otp->verify($data['email'], $data['otp'], OtpTypes::RESET_PASSWORD->value);
        if ($status !== OtpStatusEnum::Identical) {
            return response()->json(['code' => $status], 422);
        }

        $client = Client::where('email', $data['email'])->firstOrFail();
        $client->update(['password' => Hash::make($data['password'])]);

        return $this->auth->login($data['email'], $data['password'], OtpStatusEnum::AccountCreated, $client);
    }
}
