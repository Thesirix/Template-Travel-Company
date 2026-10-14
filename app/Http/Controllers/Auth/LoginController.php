<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Page de connexion unique : l'identifiant décide de l'espace.
 * Un compte administrateur arrive dans l'administration, un compte agent dans l'espace agent.
 */
class LoginController extends Controller
{
    /** Garde => [préfixe d'URL, accueil] */
    private const SPACES = [
        'gestion' => ['admin', 'admin.home'],
        'gent' => ['agent', 'agent.home'],
    ];

    public function show()
    {
        foreach (self::SPACES as $guard => [, $home]) {
            if (Auth::guard($guard)->check()) {
                return redirect()->route($home);
            }
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Espaces et caractères invisibles collés avec l'identifiant ou le mot de passe (copier-coller) : ignorés
        $clean = fn ($value) => preg_replace('/^[\s\x{00A0}\x{200B}\x{FEFF}]+|[\s\x{00A0}\x{200B}\x{FEFF}]+$/u', '', (string) $value);
        $request->merge([
            'email' => mb_strtolower($clean($request->input('email'))),
            'password' => $clean($request->input('password')),
        ]);

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        foreach (self::SPACES as $guard => [$prefix, $home]) {
            if (! Auth::guard($guard)->attempt($credentials, $request->boolean('remember'))) {
                continue;
            }
            if ($guard === 'gent' && Auth::guard('gent')->user()->state === 'disable') {
                Auth::guard('gent')->logout();

                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => 'Ce compte est désactivé. Contactez votre administrateur.']);
            }
            $request->session()->regenerate();

            // Page demandée avant la connexion, seulement si elle appartient au bon espace
            $intended = $request->session()->pull('url.intended');
            $path = $intended ? ltrim((string) parse_url($intended, PHP_URL_PATH), '/') : '';

            return $path === $prefix || str_starts_with($path, $prefix . '/')
                ? redirect()->to($intended)
                : redirect()->route($home);
        }

        return back()->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'Adresse e-mail ou mot de passe incorrect.']);
    }
}
