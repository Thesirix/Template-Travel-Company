@extends('layouts.admin')
@section('title', 'Demandes · Allotements · ' . $brand->name())

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@php($date = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y') : '—')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1">Demandes de groupes</h1>
                <p class="text-gray-500 mb-0">Besoins exprimés par les agents, avec l'objectif de prix et de classe à obtenir.</p>
            </div>
            <div>
                <a href="{{ route('agent.block-power') }}" class="btn btn-light"><i class="fa-solid fa-arrow-left mr-1"></i> Options</a>
                <a href="{{ route('agent.block-power.requests.create') }}" class="btn btn-primary ml-2"><i class="fa-solid fa-plus mr-1"></i> Nouvelle demande</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-flush mb-0">
                    <thead class="thead-light">
                    <tr><th>Groupe</th><th>Trajet</th><th>Aller</th><th>Retour</th><th>Places</th><th>Prix actuel</th><th>Objectif</th><th>Date limite</th><th>Agent</th><th></th></tr>
                    </thead>
                    <tbody>
                    @forelse($requests as $item)
                        <tr>
                            <td>{{ $item->name }}@if($item->pnr)<br><small class="text-gray-500">{{ $item->pnr }}</small>@endif</td>
                            <td>{{ $item->departure_city }} → {{ $item->arrival_city }}<br><small class="text-gray-500">{{ $item->country }}{{ $item->company ? ' · ' . $item->company : '' }}</small></td>
                            <td>{{ $date($item->date_aller) }}</td>
                            <td>{{ $date($item->date_retour) }}</td>
                            <td>{{ $item->seats }}</td>
                            <td>{{ number_format((float) $item->price, 0, ',', ' ') }} € · {{ $item->cabin_class }}</td>
                            <td>{{ number_format((float) $item->target_price, 0, ',', ' ') }} € · {{ $item->target_class }}</td>
                            <td>{{ $date($item->option_date) }}</td>
                            <td>{{ $item->agent->prenom ?? '—' }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('agent.block-power.requests.destroy', $item) }}" onsubmit="return confirm('Supprimer cette demande ?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-gray-500 py-4">Aucune demande.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
