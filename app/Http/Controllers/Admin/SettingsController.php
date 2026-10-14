<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Branding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * Identité de l'agence : nom, logo, couleur de la bannière et couleur d'accent.
 */
class SettingsController extends Controller
{


    public function index(Branding $brand)
    {
        return view('admin.parametres.index', [
            'presets' => [
                ['Indigo', '#4f46e5', '#f59e0b'],
                ['Océan', '#0369a1', '#22d3ee'],
                ['Lagon', '#0f766e', '#facc15'],
                ['Corail', '#e11d48', '#fb923c'],
                ['Nuit', '#1e293b', '#38bdf8'],
                ['Sahara', '#b45309', '#16a34a'],
            ],
        ]);
    }

    public function update(Request $request, Branding $brand)
    {
        $data = $request->validate([
            'agency_name' => ['required', 'string', 'max:80'],
            'color_primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'banner_style' => ['required', 'in:gradient,solid,glass'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ], [], [
            'agency_name' => 'nom de l\'agence',
            'color_primary' => 'couleur de la bannière',
            'color_accent' => 'couleur d\'accent',
            'logo' => 'logo',
        ]);

        $values = collect($data)->only(['agency_name', 'color_primary', 'color_accent', 'banner_style'])->all();

        if ($request->hasFile('logo')) {
            $this->deleteLogo($brand->get('agency_logo'));
            $file = $request->file('logo');
            $name = 'logo-' . time() . '.' . $file->extension();
            $file->move(public_path('uploads/branding'), $name);
            $values['agency_logo'] = 'uploads/branding/' . $name;
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteLogo($brand->get('agency_logo'));
            $values['agency_logo'] = null;
        }

        $brand->save($values);

        return redirect()->route('admin.parametres')->with('success', 'L\'identité de l\'agence a été mise à jour.');
    }

    private function deleteLogo(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/branding/')) {
            File::delete(public_path($path));
        }
    }
}
