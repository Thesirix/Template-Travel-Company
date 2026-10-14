<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function store(Request $request)
    {
        $note = Note::create([
            'text' => $request->blacknotename,
            'gent_id' => \Illuminate\Support\Facades\Auth::guard('gent')->id()
        ]);

        return response()->json([
            'id' => $note->id,
            'text' => $note->text,
        ]);
    }

    public function destroy(Request $request)
    {
        Note::where('id', $request->id)->where('gent_id', \Illuminate\Support\Facades\Auth::guard('gent')->id())->delete();
        return $request->id;
    }
}
