@extends('layouts.admin')
@section('title', 'Nouvelle demande · Allotements · ' . $brand->name())

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@php
    $value = fn (string $field) => old($field);
    $date = fn (string $field) => old($field) ?: '';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <h1 class="h3 mb-1">Nouvelle demande de groupe</h1>
            <p class="text-gray-500 mb-0">Indiquez le trajet, le prix et la classe actuels, et l'objectif à obtenir de la compagnie.</p>
        </div>

        <form method="POST" action="{{ route('agent.block-power.requests.store') }}" class="card" style="max-width: 960px">
            @csrf
            <div class="card-body form-row">
                <div class="form-group col-md-6">
                    <label for="name">Nom du groupe</label>
                    <input type="text" name="name" id="name" class="form-control" required maxlength="150" value="{{ old('name') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="pnr">PNR (facultatif)</label>
                    <input type="text" name="pnr" id="pnr" class="form-control" maxlength="50" value="{{ old('pnr') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="option_date">Réponse souhaitée avant le</label>
                    <input type="date" name="option_date" id="option_date" class="form-control" required value="{{ old('option_date') }}">
                </div>
                @include('agent.block-power.partials.trip-fields')
                <div class="form-group col-md-3">
                    <label for="target_class">Classe visée</label>
                    <input type="text" name="target_class" id="target_class" class="form-control" required maxlength="50" value="{{ old('target_class') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="target_price">Prix visé (€)</label>
                    <input type="number" name="target_price" id="target_price" class="form-control" required min="0" step="0.01" value="{{ old('target_price') }}">
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('agent.block-power.requests') }}" class="btn btn-link">Annuler</a>
            </div>
        </form>
    </div>
@endsection
