@extends('layouts.admin')

@php
    $editing = $option->exists;
    $value = fn (string $field) => old($field, $option->$field);
    $date = fn (string $field) => ($v = $value($field)) ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d') : '';
@endphp

@section('title', ($editing ? 'Modifier l\'option' : 'Nouvelle option') . ' · Allotements · ' . $brand->name())

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <h1 class="h3 mb-1">{{ $editing ? 'Modifier l\'option ' . $option->pnr : 'Nouvel allotement' }}</h1>
            <p class="text-gray-500 mb-0">Places réservées auprès de la compagnie jusqu'à la date limite.</p>
        </div>

        <form method="POST" action="{{ $editing ? route('agent.block-power.update', $option) : route('agent.block-power.store') }}" class="card" style="max-width: 960px">
            @csrf
            <div class="card-body form-row">
                <div class="form-group col-md-6">
                    <label for="name">Nom du groupe</label>
                    <input type="text" name="name" id="name" class="form-control" required maxlength="150" value="{{ $value('name') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="pnr">PNR</label>
                    <input type="text" name="pnr" id="pnr" class="form-control" required maxlength="50" value="{{ $value('pnr') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="pnr_created_on">PNR créé le</label>
                    <input type="date" name="pnr_created_on" id="pnr_created_on" class="form-control" required value="{{ $date('pnr_created_on') ?: date('Y-m-d') }}">
                </div>
                @include('agent.block-power.partials.trip-fields')
                <div class="form-group col-md-3">
                    <label for="option_date">Date limite de l'option</label>
                    <input type="date" name="option_date" id="option_date" class="form-control" required value="{{ $date('option_date') }}">
                </div>
                <div class="form-group col-12 mb-0">
                    <label for="note">Note</label>
                    <textarea name="note" id="note" class="form-control" rows="2" maxlength="2000">{{ $value('note') }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary">Enregistrer</button>
                <a href="{{ $editing ? route('agent.block-power.show', $option) : route('agent.block-power') }}" class="btn btn-link">Annuler</a>
            </div>
        </form>
    </div>
@endsection
