<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * Logo envoyé par un formulaire (champ « image ») : enregistré dans public/uploads/logos,
 * chemin relatif renvoyé pour la base (voir App\Models\Concerns\HasPublicImage).
 */
trait StoresLogo
{
    protected const LOGO_RULE = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'];

    protected function storeLogo(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }
        $filename = uniqid('logo-') . '.' . $request->file('image')->getClientOriginalExtension();
        $request->file('image')->move(public_path('uploads/logos'), $filename);

        return 'uploads/logos/' . $filename;
    }
}
