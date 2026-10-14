@extends('layouts.admin')
@section('title', 'Historique · ' . $brand->name())

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
    @include('admin.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <h1 class="h3 mb-1">Historique</h1>
            <p class="text-gray-500 mb-0">Actions des agents sur les réservations : créations, paiements, modifications, annulations.</p>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="get" action="{{ route('admin.historique') }}" class="d-flex flex-wrap align-items-end" style="gap: 12px">
                    <div>
                        <label for="agent" class="small font-weight-bold text-gray-600 mb-1">Agent</label>
                        <select name="agent" id="agent" class="form-control" style="min-width: 220px">
                            <option value="">Tous les agents</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" @selected(request('agent') == $agent->id)>{{ $agent->prenom }} {{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="action" class="small font-weight-bold text-gray-600 mb-1">Action</label>
                        <select name="action" id="action" class="form-control" style="min-width: 260px">
                            <option value="">Toutes les actions</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary"><i class="fa-solid fa-filter mr-1"></i> Filtrer</button>
                    @if(request()->hasAny(['agent', 'action']))
                        <a href="{{ route('admin.historique') }}" class="btn btn-light">Effacer</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Journal</h6>
                <span class="badge badge-primary">{{ $entries->total() }}</span>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-flush">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Agent</th>
                        <th scope="col">Action</th>
                        <th scope="col">Réservation</th>
                        <th scope="col">Vol</th>
                        <th scope="col">Détails</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($entries as $entry)
                        @php($flight = $entry->reservation?->vol)
                        <tr>
                            <td style="white-space: nowrap">{{ \Illuminate\Support\Carbon::parse($entry->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ $entry->gent ? $entry->gent->prenom . ' ' . $entry->gent->name : '—' }}</td>
                            <td><span class="badge badge-light">{{ $entry->type }}</span></td>
                            <td>#{{ $entry->reservation_id }}</td>
                            <td>
                                @if($flight)
                                    {{ $flight->itineraire }} · {{ \Illuminate\Support\Carbon::parse($flight->date_aller)->format('d/m/Y') }}
                                    <div class="small text-gray-500">{{ $flight->volGroup->name ?? '' }}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="small text-gray-600">{{ \Illuminate\Support\Str::limit((string) $entry->details, 80) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-gray-500 py-5">Aucune action enregistrée.</td></tr>
                    @endforelse
                    </tbody>
                </table>
                {{ $entries->links() }}
            </div>
        </div>
    </div>
@endsection
