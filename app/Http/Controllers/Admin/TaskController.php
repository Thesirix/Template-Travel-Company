<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $gents = Agent::get()->all();
        $todolists = Task::with('gent')->get()->all();
        return view('admin.taches', compact('gents', 'todolists'));
    }

    public function store(Request $request)
    {
        Task::create(['gent_id' => $request->gent, 'text' => $request->Note]);
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        Task::where('id', $request->id)->delete();
        return redirect()->back();
    }
}
