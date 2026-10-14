<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveCounter;
use App\Models\Agency;
use App\Models\Agent;
use App\Services\LeaveService;
use App\Support\AgentPermissions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Comptes des agents : création, modification, droits, mot de passe, activation.
 */
class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::with('agence')->orderBy('name')->get();

        return view('admin.agents.index', compact('agents'));
    }

    public function create()
    {
        $agences = Agency::orderBy('name')->get();

        return view('admin.agents.add', compact('agences'));
    }

    public function store(Request $request, LeaveService $leaves)
    {
        $data = $request->validate($this->rules() + [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:gents,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'contract_start_date' => ['required', 'date'],
        ]);

        $agent = new Agent();
        $this->fill($agent, $request, $data);
        $agent->password = Hash::make($data['password']);
        $agent->state = 'enable';
        $agent->save();

        foreach (['conge', 'repos', 'ferie', 'maladie', 'autre'] as $type) {
            LeaveCounter::create(['gent_id' => $agent->id, 'type' => $type, 'balance' => 0]);
        }
        // Solde de congés acquis depuis la date d'embauche
        LeaveCounter::where('gent_id', $agent->id)->where('type', 'conge')
            ->update(['balance' => $leaves->accruedUpTo($agent, Carbon::now()->startOfMonth())]);

        return redirect()->route('admin.agents')->with('success', "L'agent {$agent->prenom} {$agent->name} a été créé.");
    }

    public function edit(int $agent)
    {
        $agent = Agent::findOrFail($agent);
        $agences = Agency::orderBy('name')->get();

        return view('admin.agents.edit', compact('agent', 'agences'));
    }

    public function update(Request $request, int $agent, AgentPermissions $permissions)
    {
        $agent = Agent::findOrFail($agent);
        $data = $request->validate($this->rules() + [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('gents', 'email')->ignore($agent->id)],
            'contract_start_date' => ['nullable', 'date'],
        ]);

        $this->fill($agent, $request, $data);
        $agent->save();
        $permissions->sync($agent, $request->input('permissions', []));

        return redirect()->route('admin.agents')->with('success', "L'agent {$agent->prenom} {$agent->name} a été mis à jour.");
    }

    public function editPassword(int $agent)
    {
        $agent = Agent::findOrFail($agent);

        return view('admin.agents.reset', compact('agent'));
    }

    public function updatePassword(Request $request, int $agent)
    {
        $agent = Agent::findOrFail($agent);
        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);
        $agent->update(['password' => Hash::make($data['password'])]);

        return redirect()->route('admin.agents')->with('success', 'Le mot de passe a été changé.');
    }

    /** Active ou désactive un compte (un compte désactivé ne peut plus se connecter). */
    public function toggle(Request $request)
    {
        $agent = Agent::findOrFail($request->input('gent_id'));
        $agent->update(['state' => $agent->state === 'disable' ? 'enable' : 'disable']);

        return back()->with('success', $agent->state === 'disable' ? 'Le compte a été désactivé.' : 'Le compte a été réactivé.');
    }

    /** Un agent qui a déjà vendu ne peut pas être supprimé : on le désactive. */
    public function destroy(int $agent)
    {
        $agent = Agent::findOrFail($agent);
        if ($agent->flightsReservations()->exists()) {
            return back()->with('error', 'Cet agent a des réservations : désactivez son compte plutôt que de le supprimer.');
        }
        $agent->delete();

        return redirect()->route('admin.agents')->with('success', "L'agent a été supprimé.");
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'agence' => ['required', 'exists:agences,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];
    }

    private function fill(Agent $agent, Request $request, array $data): void
    {
        $agent->name = $data['name'];
        $agent->prenom = $data['prenom'];
        $agent->email = $data['email'];
        $agent->phone = $data['phone'];
        $agent->adress = $request->input('adress', $agent->adress);
        $agent->agence_id = $data['agence'];
        $agent->contract_start_date = $data['contract_start_date'] ?? $agent->contract_start_date;
        $agent->is_super_agent = $request->boolean('is_super_agent');

        if ($request->hasFile('image')) {
            $filename = time() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads/agents'), $filename);
            $agent->image = $filename;
        }
    }
}
