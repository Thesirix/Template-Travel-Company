<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Procedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Procédures internes (documents, images, vidéos) mises à disposition des agents.
 * Fichiers sur le disque « public », dossier « procedures ».
 */
class ProcedureController extends Controller
{
    public function index()
    {
        $procedures = Procedure::latest('id')->get();

        return view('admin.procedures.index', compact('procedures'));
    }

    public function create()
    {
        return view('admin.procedures.add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'media' => 'required|file|max:51200|mimes:pdf,doc,docx,jpg,jpeg,png,mp4,avi,wmv',
        ]);

        $path = $request->file('media')->store(Procedure::DIRECTORY, 'public');
        Procedure::create(['name' => $request->input('name'), 'media' => basename($path)]);

        return redirect()->route('admin.procedures')->with('success', 'La procédure a été ajoutée.');
    }

    public function destroy(Request $request)
    {
        $procedure = Procedure::findOrFail($request->input('procedureid'));
        Storage::disk('public')->delete(Procedure::DIRECTORY . '/' . $procedure->media);
        $procedure->delete();

        return redirect()->route('admin.procedures')->with('success', 'La procédure a été supprimée.');
    }

    public function download(int $procedure)
    {
        $procedure = Procedure::findOrFail($procedure);

        return $procedure->download();
    }
}
