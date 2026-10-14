<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StoresLogo;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Agences (agence principale et agences partenaires).
 */
class AgencyController extends Controller
{
    use StoresLogo;

    public function index()
    {
        $agences = Agency::withCount('agents')->orderBy('name')->get();

        return view('admin.agences.index', compact('agences'));
    }

    public function store(Request $request)
    {
        $agency = new Agency();
        $this->fill($agency, $request);
        $agency->save();

        return redirect()->route('admin.agences')->with('success', "L'agence « {$agency->name} » a été créée.");
    }

    public function edit(int $agency)
    {
        $agence = Agency::findOrFail($agency);

        return view('admin.agences.edit', compact('agence'));
    }

    public function update(Request $request, int $agency)
    {
        $agency = Agency::findOrFail($agency);
        $this->fill($agency, $request);
        $agency->save();

        return redirect()->route('admin.agences')->with('success', "L'agence « {$agency->name} » a été mise à jour.");
    }

    private function fill(Agency $agency, Request $request): void
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('agences', 'email')->ignore($agency->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'rue' => ['nullable', 'string', 'max:255'],
            'ville' => ['nullable', 'string', 'max:255'],
            'image' => self::LOGO_RULE,
        ]);
        $agency->name = $data['name'];
        $agency->email = $data['email'];
        $agency->phone = $data['phone'] ?? null;
        $agency->rue = $data['rue'] ?? null;
        $agency->ville = $data['ville'] ?? null;
        $agency->is_partner_agency = $request->boolean('is_partner_agency');
        $agency->image = $this->storeLogo($request) ?? $agency->getRawOriginal('image') ?? '';
    }
}
