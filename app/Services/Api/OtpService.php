<?php

namespace App\Services\Api;

use App\Enums\Api\OtpStatusEnum;
use App\Models\Otp;
use Illuminate\Support\Carbon;

/**
 * Codes à usage unique envoyés par e-mail (inscription, mot de passe oublié) :
 * 6 chiffres, valables 15 minutes, 3 codes maximum par quart d'heure et 3 essais par code.
 */
class OtpService
{
    private const VALIDITY_MINUTES = 15;

    private const MAX_CODES = 3;

    private const MAX_ATTEMPTS = 3;

    /** Nouveau code, ou null si le client en a déjà demandé trop récemment */
    public function generate(string $email, string $type): ?string
    {
        $recent = Otp::where('email', $email)->where('created_at', '>=', Carbon::now()->subMinutes(self::VALIDITY_MINUTES))->count();
        if ($recent >= self::MAX_CODES) {
            return null;
        }

        $code = (string) random_int(100000, 999999);
        Otp::create([
            'email' => $email,
            'otp' => $code,
            'expiration_date' => Carbon::now()->addMinutes(self::VALIDITY_MINUTES),
            'nb_attempts' => 0,
            'type' => $type,
        ]);

        return $code;
    }

    public function verify(string $email, ?string $code, string $type): OtpStatusEnum
    {
        $otp = Otp::where('email', $email)->where('type', $type)->latest('created_at')->first();
        if (! $otp) {
            return OtpStatusEnum::Invalid;
        }
        if (Carbon::parse($otp->created_at)->lt(Carbon::now()->subMinutes(self::VALIDITY_MINUTES))) {
            return OtpStatusEnum::Expired;
        }
        if ($otp->nb_attempts >= self::MAX_ATTEMPTS) {
            return OtpStatusEnum::MAX_ATTEMPTS;
        }
        $otp->increment('nb_attempts');

        return hash_equals($otp->otp, (string) $code) ? OtpStatusEnum::Identical : OtpStatusEnum::Invalid;
    }
}
