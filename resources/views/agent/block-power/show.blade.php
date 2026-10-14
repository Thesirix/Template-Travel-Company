@extends('layouts.admin')
@section('title', 'Option ' . $option->pnr . ' · Allotements · ' . $brand->name())

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@php($date = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y') : '-')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1">{{ $option->name }} <span class="badge badge-{{ $option->status === 'expiring' || $option->status === 'expired' ? 'danger' : 'primary' }} align-middle">{{ $option->statusLabel() }}</span></h1>
                <p class="text-gray-500 mb-0">PNR {{ $option->pnr }} · créée par {{ trim(($option->agent->prenom ?? '') . ' ' . ($option->agent->name ?? '')) }}</p>
            </div>
            <div class="text-nowrap">
                <a href="{{ route('agent.block-power') }}" class="btn btn-light"><i class="fa-solid fa-arrow-left mr-1"></i> Liste</a>
                @if($option->isActive())
                    <a href="{{ route('agent.block-power.edit', $option) }}" class="btn btn-light ml-2"><i class="fa-solid fa-pen mr-1"></i> Modifier</a>
                @endif
                @if($option->isActive() && $option->freeSeats() > 0)
                    <a href="{{ route('agent.block-power.passengers', $option) }}" class="btn btn-primary ml-2"><i class="fa-solid fa-user-plus mr-1"></i> Placer des passagers</a>
                @endif
                @if($option->status === 'named')
                    <a href="{{ route('agent.block-power.confirm-form', $option) }}" class="btn btn-success ml-2"><i class="fa-solid fa-check mr-1"></i> Confirmer</a>
                @endif
                @if($option->status === 'confirmed')
                    <form method="POST" action="{{ route('agent.block-power.issue', $option) }}" class="d-inline" onsubmit="return confirm('Émettre les billets de cette option ?')">
                        @csrf
                        <button class="btn btn-success ml-2"><i class="fa-solid fa-ticket mr-1"></i> Émettre</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Option</h6></div>
                    <div class="card-body">
                        <dl class="mb-0">
                            <dt>Trajet</dt><dd>{{ $option->departure_city }} → {{ $option->arrival_city }} ({{ $option->country }})</dd>
                            <dt>Compagnie</dt><dd>{{ $option->company ?: '-' }}</dd>
                            <dt>Aller / retour</dt><dd>{{ $date($option->date_aller) }} / {{ $date($option->date_retour) }}</dd>
                            <dt>Places</dt><dd>{{ $option->passengers->count() }} placée(s) sur {{ $option->seats }}</dd>
                            <dt>Classe · prix</dt><dd>{{ $option->cabin_class }} · {{ number_format((float) $option->price, 2, ',', ' ') }} €</dd>
                            <dt>Date limite</dt><dd>{{ $date($option->option_date) }}</dd>
                            <dt>PNR créé le</dt><dd>{{ $date($option->pnr_created_on) }}</dd>
                            @if($option->release)
                                <dt>Relâchée</dt><dd>le {{ $date($option->release->created_at) }} par {{ $option->release->agent->prenom ?? '-' }}</dd>
                            @endif
                            @if($option->note)
                                <dt>Note</dt><dd class="mb-0">{{ $option->note }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Passagers</h6></div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                            <tr><th>Type</th><th>Nom</th><th>Prénom</th><th>Vendu</th><th>Acompte</th><th>N° acompte</th><th></th></tr>
                            </thead>
                            <tbody>
                            @forelse($option->passengers as $passenger)
                                <tr>
                                    <td>{{ $passenger->type }}</td>
                                    <td>{{ $passenger->last_name }}</td>
                                    <td>{{ $passenger->first_name }}</td>
                                    <td>{{ $passenger->sale_price !== null ? number_format((float) $passenger->sale_price, 2, ',', ' ') . ' €' : '-' }}</td>
                                    <td>{{ $passenger->deposit !== null ? number_format((float) $passenger->deposit, 2, ',', ' ') . ' €' : '-' }}</td>
                                    <td>{{ $passenger->deposit_number ?: '-' }}</td>
                                    <td class="text-right">
                                        @if($option->isActive())
                                            <form method="POST" action="{{ route('agent.block-power.passengers.destroy', $passenger) }}" onsubmit="return confirm('Retirer ce passager ?')">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger" title="Retirer"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-gray-500">Aucun passager placé.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if($option->isActive())
            <div class="card border-left-danger mb-4">
                <div class="card-body d-flex flex-wrap align-items-center">
                    <p class="mb-0 mr-auto">Relâcher l'option rend les places à la compagnie. « Relâcher et reprendre » ouvre une nouvelle option avec les mêmes informations.</p>
                    <form method="POST" action="{{ route('agent.block-power.release', $option) }}" onsubmit="return confirm('Relâcher cette option ?')">
                        @csrf
                        <button name="reprendre" value="0" class="btn btn-outline-danger ml-2">Relâcher</button>
                        <button name="reprendre" value="1" class="btn btn-danger ml-2">Relâcher et reprendre</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
