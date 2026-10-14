<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Procedure;

/**
 * Consultation des procédures internes par les agents.
 */
class ProcedureController extends Controller
{
    public function index()
    {
        $procedures = Procedure::latest('id')->get();

        return view('agent.procedures.index', compact('procedures'));
    }

    /**
     * Images et vidéos s'affichent dans la page ; les documents (PDF, Word) s'ouvrent directement.
     */
    public function show(int $procedure)
    {
        $pr = Procedure::findOrFail($procedure);
        abort_unless($pr->exists(), 404);
        $type = $pr->type();

        if ($type === 'document') {
            return response()->file($pr->path());
        }

        return view('agent.procedures.specific', compact('pr', 'type'));
    }

    public function download(int $procedure)
    {
        return Procedure::findOrFail($procedure)->download();
    }
}
