<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Déconnexion de l'espace agent (la connexion passe par LoginController, page commune).
 */
class AgentLoginController extends Controller
{
    public function logout(Request $request)
    {
        Auth::guard('gent')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
