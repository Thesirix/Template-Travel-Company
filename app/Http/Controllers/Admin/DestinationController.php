<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Destination;
use App\Models\Flight;
use App\Support\Branding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

/**
 * Destinations vendues : elles construisent les menus admin et agent,
 * et chacune a sa permission agent « gerer {pays} ».
 */
class DestinationController extends Controller
{


    public function index()
    {
        $destinations = Destination::with('country')->withCount('volGroups')->orderBy('position')->orderBy('name')->get();
        $volCounts = Flight::select('destination', DB::raw('count(*) as total'), DB::raw('sum(place_disponible) as seats'))
            ->where('is_archive', false)->groupBy('destination')->get()->keyBy('destination');

        return view('admin.destinations.index', [
            'destinations' => $destinations,
            'volCounts' => $volCounts,
            'countries' => Country::orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request, Branding $brand)
    {
        $data = $this->validated($request);
        $data['position'] = $data['position'] ?? (Destination::max('position') + 1);
        $destination = Destination::create($data);
        $this->syncPermission($destination);
        $brand->flush();

        return redirect()->route('admin.destinations')->with('success', "La destination « {$destination->name} » a été créée.");
    }

    public function update(Request $request, Destination $destination, Branding $brand)
    {
        $data = $this->validated($request, $destination);
        $destination->update($data);
        $this->syncPermission($destination->fresh('country'));
        $brand->flush();

        return redirect()->route('admin.destinations')->with('success', "La destination « {$destination->name} » a été mise à jour.");
    }

    public function destroy(Destination $destination, Branding $brand)
    {
        if (Flight::where('destination', $destination->slug)->exists()) {
            $destination->update(['is_active' => false]);
            $brand->flush();

            return redirect()->route('admin.destinations')->with('warning', "« {$destination->name} » a des vols : elle a été masquée plutôt que supprimée.");
        }

        if ($destination->icon && str_starts_with($destination->icon, 'uploads/')) {
            File::delete(public_path($destination->icon));
        }
        $destination->delete();
        $brand->flush();

        return redirect()->route('admin.destinations')->with('success', 'Destination supprimée.');
    }

    private function validated(Request $request, ?Destination $destination = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'slug' => ['required', 'alpha_dash', 'max:100', Rule::unique('destinations', 'slug')->ignore($destination?->id)],
            'country_id' => ['nullable', 'exists:countries,id'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'position' => ['nullable', 'integer', 'min:0'],
            'icon_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
        ], [], ['name' => 'nom', 'slug' => 'identifiant', 'icon_file' => 'icône']);

        // Sans pays choisi, on crée le pays du même nom (il porte les groupes de vols et la permission)
        if (empty($data['country_id'])) {
            $country = Country::firstOrNew(['nom' => $data['name']]);
            if (! $country->exists) {
                $country->show = true;
                $country->save();
            }
            $data['country_id'] = $country->id;
        }

        if ($request->hasFile('icon_file')) {
            $file = $request->file('icon_file');
            $name = $data['slug'] . '-' . time() . '.' . $file->extension();
            $file->move(public_path('uploads/destinations'), $name);
            $data['icon'] = 'uploads/destinations/' . $name;
        }

        $data['is_active'] = $request->boolean('is_active');
        unset($data['icon_file']);

        return $data;
    }

    private function syncPermission(Destination $destination): void
    {
        if ($name = $destination->permissionName()) {
            Permission::findOrCreate($name, 'gent');
        }
    }
}
