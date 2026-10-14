@extends('layouts.admin')
@section('title', 'Confirmer l\'option · Allotements · ' . $brand->name())

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <h1 class="h3 mb-1">Confirmer l'option · {{ $option->name }}</h1>
            <p class="text-gray-500 mb-0">
                {{ $option->passengers->count() }} passager(s) sur {{ $option->seats }} place(s).
                Saisissez le PNR et la date limite donnés par la compagnie pour les places confirmées.
            </p>
        </div>

        <form method="POST" action="{{ route('agent.block-power.confirm', $option) }}" class="card" style="max-width: 720px">
            @csrf
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="pnr">PNR confirmé</label>
                        <input type="text" name="pnr" id="pnr" class="form-control" required maxlength="50" value="{{ old('pnr', $option->pnr) }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="option_date">Nouvelle date limite</label>
                        <input type="date" name="option_date" id="option_date" class="form-control" required
                               value="{{ old('option_date', \Illuminate\Support\Carbon::parse($option->option_date)->format('Y-m-d')) }}">
                    </div>
                </div>
                <ul class="mb-3">
                    @foreach($option->passengers as $passenger)
                        <li>{{ $passenger->type }} {{ $passenger->first_name }} {{ $passenger->last_name }}</li>
                    @endforeach
                </ul>
                @if($option->freeSeats() > 0)
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="reprendre" name="reprendre" value="1" checked>
                        <label class="custom-control-label" for="reprendre">Créer ensuite une option pour les {{ $option->freeSeats() }} place(s) non vendue(s)</label>
                    </div>
                @endif
            </div>
            <div class="card-footer">
                <button class="btn btn-success">Confirmer</button>
                <a href="{{ route('agent.block-power.show', $option) }}" class="btn btn-link">Annuler</a>
            </div>
        </form>
    </div>
@endsection
