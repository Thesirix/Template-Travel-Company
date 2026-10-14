@extends('layouts.admin')
@section('title', $client->lastName . ' ' . $client->firstName . ' · ' . $brand->name())

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
    @include('admin.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1">{{ $client->lastName }} {{ $client->firstName }}</h1>
                <p class="text-gray-500 mb-0">Fiche client et demandes de devis.</p>
            </div>
            <a href="{{ route('admin.clients') }}" class="btn btn-light"><i class="fa-solid fa-arrow-left mr-1"></i> Tous les clients</a>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Coordonnées</h6></div>
                    <div class="card-body">
                        <dl class="mb-0">
                            <dt>Téléphone</dt><dd>{{ $client->phoneNumber ?: '—' }}</dd>
                            <dt>E-mail</dt><dd>{{ $client->email ?: '—' }}</dd>
                            <dt>Ville</dt><dd>{{ trim($client->city . ' ' . ($client->country ? '(' . $client->country . ')' : '')) ?: '—' }}</dd>
                            <dt>Passeport expire le</dt><dd>{{ $client->expire_passport ? \Illuminate\Support\Carbon::parse($client->expire_passport)->format('d/m/Y') : '—' }}</dd>
                            <dt>À rappeler le</dt><dd>{{ $client->recall ? \Illuminate\Support\Carbon::parse($client->recall)->format('d/m/Y') : '—' }}</dd>
                            <dt>Suivi par</dt>
                            <dd class="mb-0">{{ $client->gent ? $client->gent->prenom . ' ' . $client->gent->name . ' · ' . ($client->gent->agence->name ?? '') : ($client->provenance ?: 'Site web') }}</dd>
                        </dl>
                        @if($client->observation)
                            <hr>
                            <p class="mb-0 text-gray-600">{{ $client->observation }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Demandes de devis</h6>
                        <span class="badge badge-primary">{{ $client->volEstimates->count() }}</span>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-flush">
                            <thead class="thead-light">
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Destination</th>
                                <th scope="col">Trajet</th>
                                <th scope="col">Passagers</th>
                                <th scope="col">Prix proposé</th>
                                <th scope="col">Statut</th>
                                <th scope="col">Agent</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($client->volEstimates as $request)
                                <tr>
                                    <td style="white-space: nowrap">{{ \Illuminate\Support\Carbon::parse($request->created_at)->format('d/m/Y') }}</td>
                                    <td>{{ $request->destination }}</td>
                                    <td>{{ $request->departure_city }} → {{ $request->arrived_city }}</td>
                                    <td>{{ $request->nb_passagers_adultes + $request->nb_passagers_childs + $request->nb_passagers_babies }}</td>
                                    <td style="white-space: nowrap">{{ number_format((float) $request->proposed_price, 0, ',', ' ') }} €</td>
                                    <td>
                                        @if($request->selled)
                                            <span class="badge badge-success">Vendu</span>
                                        @else
                                            <span class="badge badge-warning">En cours</span>
                                        @endif
                                    </td>
                                    <td>{{ $request->gentRelation ? $request->gentRelation->prenom . ' ' . $request->gentRelation->name : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-gray-500 py-5">Aucune demande pour ce client.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
