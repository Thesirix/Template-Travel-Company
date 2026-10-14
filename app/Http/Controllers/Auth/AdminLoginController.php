<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Déconnexion de l'espace administration (la connexion passe par LoginController, page commune).
 */
class AdminLoginController extends Controller
{
    public function logout(Request $request)
    {
        Auth::guard('gestion')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
