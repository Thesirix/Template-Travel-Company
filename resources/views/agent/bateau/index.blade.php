@extends('layouts.admin')
@section('title', 'Bateau · ' . $title . ' · ' . $brand->name())

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@php
    $routes = ['pending' => 'agent.bateau.pending', 'confirmer' => 'agent.bateau.confirmed', 'final' => 'agent.bateau.sold', 'echange' => 'agent.bateau.vouchers'];
    $reserved = $status !== 'pending';
    $date = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y') : '-';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1">Bateau · {{ $title }}</h1>
                <p class="text-gray-500 mb-0">{{ $bookings->count() }} traversée{{ $bookings->count() > 1 ? 's' : '' }}</p>
            </div>
            <div>
                <a href="{{ route('agent.bateau.export', ['statut' => $status] + $filters) }}" class="btn btn-light"><i class="fa-solid fa-file-excel mr-1"></i> Exporter</a>
                <a href="{{ route('agent.bateau.create') }}" class="btn btn-primary ml-2"><i class="fa-solid fa-plus mr-1"></i> Nouvelle demande</a>
            </div>
        </div>

        <ul class="nav nav-pills mb-3">
            @foreach(\App\Models\BoatBooking::STATUSES as $key => $label)
                <li class="nav-item"><a class="nav-link {{ $key === $status ? 'active' : '' }}" href="{{ route($routes[$key]) }}">{{ $label }}</a></li>
            @endforeach
        </ul>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route($routes[$status]) }}" class="form-row align-items-end">
                    <div class="form-group col-md-3 col-lg">
                        <label for="destination">Destination</label>
                        <select name="destination" id="destination" class="form-control">
                            <option value="">Toutes</option>
                            @foreach($destinations as $item)
                                <option value="{{ $item->id }}" @selected(($filters['destination'] ?? '') == $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 col-lg">
                        <label for="compagnie">Compagnie</label>
                        <select name="compagnie" id="compagnie" class="form-control">
                            <option value="">Toutes</option>
                            @foreach($companies as $item)
                                <option value="{{ $item->id }}" @selected(($filters['compagnie'] ?? '') == $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 col-lg">
                        <label for="port">Port de départ</label>
                        <select name="port" id="port" class="form-control">
                            <option value="">Tous</option>
                            @foreach($departurePorts as $item)
                                <option value="{{ $item->id }}" @selected(($filters['port'] ?? '') == $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 col-lg">
                        <label for="nom">Nom</label>
                        <input type="text" name="nom" id="nom" class="form-control" value="{{ $filters['nom'] ?? '' }}">
                    </div>
                    <div class="form-group col-md-3 col-lg">
                        <label for="date_aller">Aller le</label>
                        <input type="date" name="date_aller" id="date_aller" class="form-control" value="{{ $filters['date_aller'] ?? '' }}">
                    </div>
                    <div class="form-group col-md-3 col-lg">
                        <label for="date_retour">Retour le</label>
                        <input type="date" name="date_retour" id="date_retour" class="form-control" value="{{ $filters['date_retour'] ?? '' }}">
                    </div>
                    <div class="form-group col-md-3 col-lg">
                        <label for="agent">Agent</label>
                        <select name="agent" id="agent" class="form-control">
                            <option value="">Tous</option>
                            @foreach($agents as $item)
                                <option value="{{ $item->id }}" @selected(($filters['agent'] ?? '') == $item->id)>{{ $item->prenom }} {{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 col-lg-auto">
                        <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass mr-1"></i> Filtrer</button>
                        @if(array_filter($filters))
                            <a href="{{ route($routes[$status]) }}" class="btn btn-light ml-1">Effacer</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-flush table-hover mb-0">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">Demande</th>
                        <th scope="col">Responsable</th>
                        <th scope="col">Destination</th>
                        <th scope="col">Trajet</th>
                        <th scope="col">Aller</th>
                        <th scope="col">Retour</th>
                        <th scope="col">Compagnie</th>
                        <th scope="col">Passagers</th>
                        <th scope="col">Véhicule</th>
                        @if($reserved)
                            <th scope="col">N° résa</th>
                            <th scope="col">Option</th>
                        @endif
                        <th scope="col">Vendeur</th>
                        <th scope="col"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($bookings as $booking)
                        @php($lead = $booking->passengers->firstWhere('respo', true) ?? $booking->passengers->first())
                        <tr>
                            <td>{{ $date($booking->created_at) }}</td>
                            <td>{{ $lead ? $lead->nom . ' ' . $lead->prenom : '-' }}</td>
                            <td>{{ $booking->destination->name ?? '-' }}</td>
                            <td>{{ $booking->departurePort->name ?? '-' }}{{ $booking->arrivalPort ? ' ⇄ ' . $booking->arrivalPort->name : '' }}</td>
                            <td>{{ $date($booking->date_aller) }}</td>
                            <td>{{ $date($booking->date_retour) }}</td>
                            <td>{{ $booking->company->name ?? '-' }}</td>
                            <td>{{ $booking->passengerSummary() }}</td>
                            <td>{{ $booking->hasVehicle() ? $booking->voiture_matricule : '-' }}</td>
                            @if($reserved)
                                <td>{{ $booking->reservation->num_reservation ?? '-' }}</td>
                                <td>{{ $date($booking->reservation?->date_option) }}</td>
                            @endif
                            <td>{{ $booking->agent->prenom ?? '-' }}</td>
                            <td class="text-right"><a class="btn btn-sm btn-primary" href="{{ route('agent.bateau.show', $booking) }}">Ouvrir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="13" class="text-center text-gray-500 py-4">Aucune traversée.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
