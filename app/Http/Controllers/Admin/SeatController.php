<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    public function index()
    {
        $vols = Flight::with('company')->get()->all();
        return view('admin.places.index', compact('vols'));
    }

    public function edit($id)
    {
        $vol = Flight::with('company')->where('id', $id)->first();
        return view('admin.places.modifyvolpage', compact('vol'));
    }

    public function update(Request $req)
    {
        Flight::where('id', $req->volid)->update([
            'place_total' => $req->placetotal,
            'place_reserver' => $req->placereserver,
            'place_disponible' => $req->placedisponsible,
        ]);
        return redirect()->route('admin.places');
    }
}
