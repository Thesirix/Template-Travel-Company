<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockPowerArrivalCity;
use App\Models\BlockPowerCompany;
use App\Models\BlockPowerCountry;
use App\Models\BlockPowerDepartureCity;
use App\Models\BlockPowerGuide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Allotements côté administration : listes proposées aux agents (pays, compagnies, villes) et guide.
 * Les options enregistrent le nom choisi : supprimer un élément de liste ne modifie pas les options existantes.
 */
class BlockPowerController extends Controller
{
    /** clé d'URL => [modèle, titre] */
    public const CATALOGS = [
        'pays' => [BlockPowerCountry::class, 'Pays'],
        'compagnies' => [BlockPowerCompany::class, 'Compagnies'],
        'villes-depart' => [BlockPowerDepartureCity::class, 'Villes de départ'],
        'villes-arrivee' => [BlockPowerArrivalCity::class, "Villes d'arrivée"],
    ];

    public function index()
    {
        return view('admin.block-power.index', [
            'catalogs' => collect(self::CATALOGS)->map(fn ($catalog) => ['title' => $catalog[1], 'items' => $catalog[0]::orderBy('name')->get()]),
            'guide' => BlockPowerGuide::first(),
        ]);
    }

    public function store(Request $request, string $catalog)
    {
        $model = self::CATALOGS[$catalog][0];
        $name = $request->validate(['name' => ['required', 'string', 'max:150', 'unique:' . (new $model())->getTable() . ',name']], [], ['name' => 'nom'])['name'];
        $model::create(['name' => $name]);

        return redirect()->route('admin.block-power')->with('success', 'Élément ajouté.');
    }

    public function destroy(Request $request, string $catalog)
    {
        $model = self::CATALOGS[$catalog][0];
        $model::findOrFail($request->validate(['id' => ['required', 'integer']])['id'])->delete();

        return redirect()->route('admin.block-power')->with('success', 'Élément supprimé.');
    }

    /** Guide Allotements (un seul fichier) : remplace le précédent, disque « public », dossier « guide » */
    public function storeGuide(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,jpg,jpeg,png']], [], ['file' => 'fichier']);

        if ($previous = BlockPowerGuide::first()) {
            Storage::disk('public')->delete('guide/' . $previous->name);
            $previous->delete();
        }
        $name = 'guide-allotements.' . $request->file('file')->getClientOriginalExtension();
        $request->file('file')->storeAs('guide', $name, 'public');
        BlockPowerGuide::create(['name' => $name]);

        return redirect()->route('admin.block-power')->with('success', 'Le guide des allotements a été mis à jour.');
    }
}
