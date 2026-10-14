@extends('layouts.admin')

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Extractions</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('agent.home') }}">Accueil</a></li>
                <li class="breadcrumb-item">Extractions</li>
            </ol>
        </div>

        <div class="card mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filtrer les contacts</h6>
            </div>
            <div class="card-body d-flex flex-wrap align-items-end" style="gap: 12px">
                <form method="get" action="{{ route('agent.extractions') }}" class="d-flex flex-wrap align-items-end" style="gap: 12px">
                    <div>
                        <label for="destination" class="small font-weight-bold text-gray-600 mb-1">Destination</label>
                        <select class="form-control" name="destination" id="destination" style="min-width: 260px">
                            <option value="">Toutes les destinations</option>
                            @foreach($brand->destinations() as $item)
                                <option value="{{ $item->slug }}" @selected($destination?->is($item))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass mr-1"></i> Rechercher</button>
                </form>
                <form method="post" action="{{ route('agent.extractions.export') }}" class="ml-auto">
                    @csrf
                    <input type="hidden" name="destination" value="{{ $destination?->slug }}">
                    <button class="btn btn-outline-primary" @disabled($contacts->isEmpty())>
                        <i class="fa-solid fa-file-excel mr-1"></i> Exporter les téléphones (Excel)
                    </button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    Contacts{{ $destination ? ' — ' . $destination->name : '' }}
                </h6>
                <span class="badge badge-primary">{{ $contacts->count() }}</span>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-flush">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">Nom</th>
                        <th scope="col">Prénom</th>
                        <th scope="col">Téléphone</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">Destination</th>
                        <th scope="col">Source</th>
                        <th scope="col">Agence</th>
                        <th scope="col">Vendeur</th>
                        <th scope="col">Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($contacts as $contact)
                        <tr>
                            <td class="font-weight-bold">{{ $contact['nom'] }}</td>
                            <td>{{ $contact['prenom'] }}</td>
                            <td style="white-space: nowrap">{{ $contact['phone'] }}</td>
                            <td>{{ $contact['email'] }}</td>
                            <td>{{ $contact['destination'] }}</td>
                            <td><span class="badge {{ $contact['source'] === 'Réservation' ? 'badge-success' : 'badge-info' }}">{{ $contact['source'] }}</span></td>
                            <td>{{ $contact['agence'] }}</td>
                            <td>{{ $contact['vendeur'] }}</td>
                            <td style="white-space: nowrap">{{ $contact['date'] ? \Illuminate\Support\Carbon::parse($contact['date'])->format('d/m/Y') : '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-gray-500 py-5">Aucun contact pour cette destination.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
