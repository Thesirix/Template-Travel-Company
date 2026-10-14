@extends('layouts.admin')
@section('title', 'Clients · ' . $brand->name())

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
                <h1 class="h3 mb-1">Clients</h1>
                <p class="text-gray-500 mb-0">Clients du site public et clients enregistrés par les agents, avec leurs demandes de devis.</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="get" action="{{ route('admin.clients') }}" class="d-flex flex-wrap" style="gap: 12px">
                    <input type="search" name="q" value="{{ $search }}" class="form-control" style="max-width: 360px"
                           placeholder="Nom, prénom, e-mail ou téléphone">
                    <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass mr-1"></i> Rechercher</button>
                    @if($search !== '')
                        <a href="{{ route('admin.clients') }}" class="btn btn-light">Effacer</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ $search !== '' ? 'Résultats pour « ' . $search . ' »' : 'Tous les clients' }}</h6>
                <span class="badge badge-primary">{{ $clients->total() }}</span>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-flush">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">Client</th>
                        <th scope="col">Téléphone</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">Ville</th>
                        <th scope="col">Origine</th>
                        <th scope="col">Demandes</th>
                        <th scope="col">Inscrit le</th>
                        <th scope="col"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td class="font-weight-bold">{{ $client->lastName }} {{ $client->firstName }}</td>
                            <td style="white-space: nowrap">{{ $client->phoneNumber }}</td>
                            <td>{{ $client->email }}</td>
                            <td>{{ $client->city }}</td>
                            <td>
                                @if($client->gent)
                                    <span class="badge badge-info">Agent · {{ $client->gent->prenom }} {{ $client->gent->name }}</span>
                                @else
                                    <span class="badge badge-success">{{ $client->provenance ?: 'Site web' }}</span>
                                @endif
                            </td>
                            <td>{{ $client->vol_estimates_count }}</td>
                            <td style="white-space: nowrap">{{ $client->created_at ? \Illuminate\Support\Carbon::parse($client->created_at)->format('d/m/Y') : '' }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.clients.show', $client->id) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-eye mr-1"></i> Fiche</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-gray-500 py-5">Aucun client.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
                {{ $clients->links() }}
            </div>
        </div>
    </div>
@endsection
