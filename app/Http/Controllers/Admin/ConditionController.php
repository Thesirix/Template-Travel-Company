<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesCondition;
use Illuminate\Http\Request;

class ConditionController extends Controller
{

    public function index(Request $request)
    {
        $conditions = SalesCondition::all();
        return view('admin.conditions.allconditions', compact('conditions'));
    }

    public function create()
    {
        return view('admin.conditions.add');
    }

    public function store(Request $request)
    {
        SalesCondition::create([
            'nom' => $request->name,
            'Formalite_sante' => $request->Formalite_sante,
            'Formalite_police' => $request->Formalite_police,
            'Formalite_inscription' => $request->Formalite_inscription,
            'Formalite_modifiation' => $request->Formalite_modifiation,
            'Formalite_annulation' => $request->Formalite_annulation,

        ]);
        return redirect()->route('admin.conditions');

    }

    public function destroy(Request $request)
    {
        SalesCondition::where('id', $request->condition_id)->delete();
        return redirect()->back();
    }

    public function edit($id)
    {
        $condition=SalesCondition::where('id',$id)->first();
        return view('admin.conditions.update',compact('condition'));

    }

    public function update(Request $request)
    {
        SalesCondition::where('id',$request->condition_id)->update([
            'nom' => $request->name,
            'Formalite_sante' => $request->Formalite_sante,
            'Formalite_police' => $request->Formalite_police,
            'Formalite_inscription' => $request->Formalite_inscription,
            'Formalite_modifiation' => $request->Formalite_modifiation,
            'Formalite_annulation' => $request->Formalite_annulation,

        ]);
        return redirect()->route('admin.conditions');

    }

}
