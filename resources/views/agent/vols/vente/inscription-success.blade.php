@extends('layouts.admin')
@section('title', 'Réservation enregistrée · ' . $brand->name())

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@php
    $groupId = $reservation->vol->vol_group_id;
    $lead = $reservation->passagers->firstWhere('type', 'adulte') ?? $reservation->passagers->first();
    $waitingList = $reservation->type_reservation === 'liste_attente';
    $types = ['aller_simple' => 'Aller simple', 'retour_simple' => 'Retour simple', 'aller_retour_same_vol' => 'Aller-retour', 'aller_retour_diffrent_vol' => 'Aller et retour sur deux vols'];
@endphp

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1">Réservation {{ substr($lead?->nom ?? '', 0, 4) . $reservation->id }}</h1>
                <p class="text-gray-500 mb-0">
                    {{ $waitingList ? "Inscription enregistrée en liste d'attente." : 'Inscription enregistrée.' }}
                    Le client reçoit une confirmation par SMS et par e-mail, l'agence une copie par e-mail.
                </p>
            </div>
            <a href="{{ route('agent.vols.vente', $groupId) }}" class="btn btn-light"><i class="fa-solid fa-plus mr-1"></i> Nouvelle vente</a>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Voyage</h6></div>
                    <div class="card-body">
                        <dl class="mb-0">
                            <dt>Compagnie</dt><dd>{{ $reservation->vol->company->nom ?? '—' }}</dd>
                            <dt>Type</dt><dd>{{ $types[$reservation->type_voyage] ?? $reservation->type_voyage }}</dd>
                            <dt>Aller</dt><dd>{{ $reservation->vol->date_aller ? \Illuminate\Support\Carbon::parse($reservation->vol->date_aller)->format('d/m/Y') : '—' }}</dd>
                            <dt>Retour</dt>
                            <dd>
                                @php($return = $reservation->volRetour ? $reservation->volRetour->date_aller : $reservation->vol->date_retour)
                                {{ $return && $reservation->type_voyage !== 'aller_simple' ? \Illuminate\Support\Carbon::parse($return)->format('d/m/Y') : '—' }}
                            </dd>
                            <dt>Passagers</dt>
                            <dd>{{ $reservation->nb_adult }} adulte(s), {{ $reservation->nb_enfant }} enfant(s), {{ $reservation->nb_bebe }} bébé(s)</dd>
                            <dt>Acompte</dt><dd class="mb-0">{{ number_format((float) $reservation->acomptes->sum('montant'), 2, ',', ' ') }} €</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Passagers</h6></div>
                    <div class="card-body table-responsive">
                        <table class="table table-flush mb-0">
                            <thead class="thead-light">
                            <tr>
                                <th scope="col">Nom</th>
                                <th scope="col">Type</th>
                                <th scope="col">Naissance</th>
                                <th scope="col">Téléphone</th>
                                <th scope="col">Dossier</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($reservation->passagers as $passenger)
                                <tr>
                                    <td>{{ $passenger->nom }} {{ $passenger->prenom }}</td>
                                    <td>{{ ucfirst($passenger->type) }}</td>
                                    <td>{{ $passenger->date_naissance ? \Illuminate\Support\Carbon::parse($passenger->date_naissance)->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $passenger->tele1 ?: '—' }}</td>
                                    <td>{{ $passenger->dossier_etat ?: '—' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body d-flex flex-wrap align-items-center">
                <p class="mb-0 mr-auto text-gray-600">
                    Imprimer le contrat en deux exemplaires (signature du client, tampon et signature de l'agence) :
                    un pour le client, un à classer.
                </p>
                @unless($waitingList)
                    <a href="{{ route('agent.vols.vente.contrat', [$groupId, $reservation->id]) }}" class="btn btn-primary ml-2" target="_blank">
                        <i class="fa-solid fa-file-signature mr-1"></i> Contrat
                    </a>
                    <a href="{{ route('agent.vols.vente.e-billet', [$groupId, $reservation->id]) }}" class="btn btn-primary ml-2" target="_blank">
                        <i class="fa-solid fa-ticket mr-1"></i> E-billet
                    </a>
                @endunless
                <a href="{{ route('agent.vols.reservations.show', [$groupId, $reservation->id]) }}" class="btn btn-light ml-2">
                    <i class="fa-solid fa-folder-open mr-1"></i> Dossier
                </a>
            </div>
        </div>
    </div>
@endsection
