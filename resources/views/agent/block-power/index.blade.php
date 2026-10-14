@extends('layouts.admin')
@section('title', 'Allotements · ' . $brand->name())

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@php
    $tabs = \App\Http\Controllers\Agent\BlockPowerController::TABS;
    $date = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y') : '—';
    $sortLink = fn (string $column, string $label) => '<a href="' . e(route('agent.block-power', ['statut' => $tab, 'tri' => $column,
        'sens' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc'] + $filters)) . '">' . e($label)
        . ($sort === $column ? ($direction === 'asc' ? ' ↑' : ' ↓') : '') . '</a>';
    $tabCount = fn (string $key) => $key === 'emises' || $key === 'archives' ? null : collect($tabs[$key][1])->sum(fn ($s) => $counts[$s] ?? 0);
@endphp

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1">Allotements</h1>
                <p class="text-gray-500 mb-0">Options de places auprès des compagnies : placer les passagers, confirmer, émettre.</p>
            </div>
            <div>
                <a href="{{ route('agent.block-power.guide') }}" class="btn btn-light" target="_blank"><i class="fa-solid fa-book mr-1"></i> Guide</a>
                <a href="{{ route('agent.block-power.requests') }}" class="btn btn-light ml-2"><i class="fa-solid fa-inbox mr-1"></i> Demandes</a>
                <a href="{{ route('agent.block-power.create') }}" class="btn btn-primary ml-2"><i class="fa-solid fa-plus mr-1"></i> Nouvelle option</a>
            </div>
        </div>

        <ul class="nav nav-pills mb-3">
            @foreach($tabs as $key => [$label])
                <li class="nav-item">
                    <a class="nav-link {{ $key === $tab ? 'active' : '' }}" href="{{ route('agent.block-power', ['statut' => $key]) }}">
                        {{ $label }}
                        @if($tabCount($key))<span class="badge badge-light ml-1">{{ $tabCount($key) }}</span>@endif
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('agent.block-power') }}" class="form-row align-items-end">
                    <input type="hidden" name="statut" value="{{ $tab }}">
                    @foreach(['pays' => ['Pays', $countries], 'depart' => ['Départ', $departureCities], 'arrivee' => ['Arrivée', $arrivalCities]] as $name => [$label, $items])
                        <div class="form-group col-md-3">
                            <label for="filter-{{ $name }}">{{ $label }}</label>
                            <select name="{{ $name }}" id="filter-{{ $name }}" class="form-control">
                                <option value="">Tous</option>
                                @foreach($items as $item)
                                    <option @selected(($filters[$name] ?? '') === $item)>{{ $item }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                    <div class="form-group col-md-2">
                        <label for="filter-date">Aller le</label>
                        <input type="date" name="date_aller" id="filter-date" class="form-control" value="{{ $filters['date_aller'] ?? '' }}">
                    </div>
                    <div class="form-group col-md-1">
                        <button class="btn btn-primary btn-block" title="Filtrer"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-flush table-hover mb-0">
                    <thead class="thead-light">
                    <tr>
                        <th>Groupe</th>
                        <th>PNR</th>
                        <th>Trajet</th>
                        <th>{!! $sortLink('date_aller', 'Aller') !!}</th>
                        <th>Retour</th>
                        <th>Compagnie</th>
                        <th>{!! $sortLink('seats', 'Places') !!}</th>
                        <th>{!! $sortLink('price', 'Prix') !!}</th>
                        <th>{!! $sortLink('option_date', 'Date limite') !!}</th>
                        <th>Agent</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($options as $option)
                        <tr class="{{ $option->status === 'expiring' ? 'text-danger' : '' }}">
                            <td>{{ $option->name }}@if($option->status === 'expiring')<br><small>{{ $option->statusLabel() }}</small>@endif</td>
                            <td>{{ $option->pnr }}</td>
                            <td>{{ $option->departure_city }} → {{ $option->arrival_city }}<br><small class="text-gray-500">{{ $option->country }}</small></td>
                            <td>{{ $date($option->date_aller) }}</td>
                            <td>{{ $date($option->date_retour) }}</td>
                            <td>{{ $option->company ?: '—' }}</td>
                            <td>{{ $option->passengers->count() }} / {{ $option->seats }}</td>
                            <td>{{ number_format((float) $option->price, 0, ',', ' ') }} €</td>
                            <td>{{ $date($option->option_date) }}</td>
                            <td>
                                {{ $option->agent->prenom ?? '—' }}
                                @if($option->release)<br><small class="text-gray-500">relâchée par {{ $option->release->agent->prenom ?? '—' }}</small>@endif
                            </td>
                            <td class="text-right text-nowrap">
                                @if($option->isActive() && $option->freeSeats() > 0)
                                    <a href="{{ route('agent.block-power.passengers', $option) }}" class="btn btn-sm btn-primary">Placer</a>
                                @endif
                                @if($option->status === 'named')
                                    <a href="{{ route('agent.block-power.confirm-form', $option) }}" class="btn btn-sm btn-success">Confirmer</a>
                                @endif
                                @if($option->status === 'confirmed')
                                    <form method="POST" action="{{ route('agent.block-power.issue', $option) }}" class="d-inline" onsubmit="return confirm('Émettre les billets de cette option ?')">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Émettre</button>
                                    </form>
                                @endif
                                <a href="{{ route('agent.block-power.show', $option) }}" class="btn btn-sm btn-light">Ouvrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center text-gray-500 py-4">Aucune option.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
