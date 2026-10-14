<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

/**
 * Fichier clients : clients du site public et clients créés par les agents, avec leurs demandes de devis.
 */
class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $clients = Client::with('gent')
            ->withCount('volEstimates')
            ->when($search !== '', fn ($q) => $q->where(fn ($q2) => $q2
                ->where('lastName', 'like', "%{$search}%")
                ->orWhere('firstName', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phoneNumber', 'like', "%{$search}%")))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.clients.index', compact('clients', 'search'));
    }

    public function show(int $client)
    {
        $client = Client::with(['gent.agence', 'volEstimates' => fn ($q) => $q->latest('id'), 'volEstimates.gentRelation', 'volEstimates.agenceRelation'])
            ->findOrFail($client);

        return view('admin.clients.show', compact('client'));
    }
}
