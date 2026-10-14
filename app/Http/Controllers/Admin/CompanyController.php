<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StoresLogo;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Country;
use Illuminate\Http\Request;

/**
 * Compagnies aériennes.
 */
class CompanyController extends Controller
{
    use StoresLogo;

    public function index()
    {
        $companies = Company::with('country')->orderBy('nom')->get();
        $countries = Country::orderBy('nom')->get();

        return view('admin.compagnies.index', compact('companies', 'countries'));
    }

    public function store(Request $request)
    {
        $company = new Company();
        $this->fill($company, $request);
        $company->save();

        return redirect()->route('admin.compagnies')->with('success', "La compagnie « {$company->nom} » a été ajoutée.");
    }

    public function edit(int $company)
    {
        $company = Company::findOrFail($company);
        $countries = Country::orderBy('nom')->get();

        return view('admin.compagnies.edit', compact('company', 'countries'));
    }

    public function update(Request $request, int $company)
    {
        $company = Company::findOrFail($company);
        $this->fill($company, $request);
        $company->save();

        return redirect()->route('admin.compagnies')->with('success', "La compagnie « {$company->nom} » a été mise à jour.");
    }

    public function destroy(int $company)
    {
        $company = Company::findOrFail($company);
        if ($company->vols()->exists()) {
            return back()->with('error', 'Cette compagnie a des vols : elle ne peut pas être supprimée.');
        }
        $company->delete();

        return redirect()->route('admin.compagnies')->with('success', 'La compagnie a été supprimée.');
    }

    private function fill(Company $company, Request $request): void
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'exists:countries,id'],
            'image' => self::LOGO_RULE,
        ]);
        $company->nom = $data['name'];
        $company->country_id = $data['country'];
        $company->type = $request->boolean('type');
        $company->show = $request->boolean('show');
        $company->chart = $request->boolean('chart');
        $company->image = $this->storeLogo($request) ?? $company->getRawOriginal('image') ?? '';
    }
}
