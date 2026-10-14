<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Profil de l'agent connecté : nom, prénom et photo (public/uploads/agents).
 */
class ProfileController extends Controller
{
    public function show()
    {
        return view('agent.profil', ['user' => Auth::guard('gent')->user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [], ['name' => 'nom', 'image' => 'photo']);
        $agent = Auth::guard('gent')->user();

        if ($request->hasFile('image')) {
            $previous = $agent->image ? public_path('uploads/agents/' . $agent->image) : null;
            $filename = $agent->id . '-' . time() . '.' . $request->file('image')->extension();
            $request->file('image')->move(public_path('uploads/agents'), $filename);
            if ($previous && is_file($previous)) {
                @unlink($previous);
            }
            $agent->image = $filename;
        }
        $agent->fill(['name' => $data['name'], 'prenom' => $data['prenom']])->save();

        return back()->with('success', 'Profil mis à jour.');
    }
}
