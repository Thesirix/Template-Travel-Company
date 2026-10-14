{{-- Compteurs de congés de l'agent connecté (valeurs remplies par solde_agent / presence.js) --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0">Mes congés</h6>
        <span class="small text-gray-500">{{ \Illuminate\Support\Facades\Auth::user()->prenom }} {{ \Illuminate\Support\Facades\Auth::user()->name }}</span>
    </div>
    <dl class="tc-kv mb-0">
        <div><dt>Congés payés</dt><dd><span id="solde-conge">0</span> j</dd></div>
        <div><dt>Repos</dt><dd><span id="solde-repos">0</span> j</dd></div>
        <div><dt>Jours fériés</dt><dd><span id="solde-ferie">0</span> j</dd></div>
        <div><dt>Maladie</dt><dd><span id="solde-maladie">0</span> j</dd></div>
        <div><dt>Autres absences</dt><dd><span id="solde-autre">0</span> j</dd></div>
        <div><dt>Jours travaillés cette année</dt><dd><span id="presenceCounterYear">0</span></dd></div>
    </dl>
</div>
