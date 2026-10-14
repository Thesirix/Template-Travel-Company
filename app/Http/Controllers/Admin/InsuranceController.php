<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Insurance;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{

    public function index()
    {
        $assurances = Insurance::get()->all();
        return view('admin.assurances.allassurances', compact('assurances'));
    }

    public function create()
    {
        return view('admin.assurances.add');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'type_assu' => 'required|',
            'name' => 'required|',
            'Description' => 'required',
            'Prix' => 'required|numeric',

        ]);
        $cont= Insurance::create([
            'type' => $request->type_assu,
            'name' => $request->name,
            'description' => $request->Description,
            'prix' => $request->Prix,

        ]);
        return redirect()->route('admin.assurances');

    }

    public function destroy(Request $request)
    {
        Insurance::where('id', $request->condition_id)->delete();
        return redirect()->back();
    }

    public function edit($id)
    {
        $assurence=Insurance::findOrFail($id);
        return view('admin.assurances.edit',compact('assurence'));
    }

    public function update(Request $request,$id)
    {
        $validatedData = $request->validate([
            'type_assu' => 'required|',
            'name' => 'required|',
            'Description' => 'required',
            'Prix' => 'required|numeric',

        ]);
        $assurence=Insurance::findOrFail($id);
        $assurence->type=$request->type_assu;
        $assurence->name=$request->name;
        $assurence->description=$request->Description;
        $assurence->prix=$request->Prix;

        $assurence->save();
        return redirect()->route('admin.assurances');

    }

}
