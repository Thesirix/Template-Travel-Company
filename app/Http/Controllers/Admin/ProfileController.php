<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('admin.profil.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $user->name = $request->input('name');
        $user->prenom = $request->input('prenom');
        if ($request->hasfile('image')) {
            $this->validate($request, [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $file = $request->image;
            $ext = $file->getClientOriginalExtension();
            $filename = time() . "." . $ext;
            $file->move(public_path('uploads/agents'), $filename);
            $user->adresse = $filename;
        }
        $user->save();
//
        return redirect()->back();
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        $this->validate($request,
            [
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
        $user->password = Hash::make($request->input('password'));

        $user->save();
//
        return redirect()->route('admin.home');

    }
}
