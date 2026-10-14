@extends('layouts.admin')
@section('title', $pr->name . ' · ' . $brand->name())

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1">{{ $pr->name }}</h1>
                <p class="text-gray-500 mb-0">Procédure interne</p>
            </div>
            <div class="d-flex" style="gap: 8px">
                <a href="{{ route('agent.procedures') }}" class="btn btn-light"><i class="fa-solid fa-arrow-left mr-1"></i> Toutes les procédures</a>
                <a href="{{ route('agent.procedures.download', $pr->id) }}" class="btn btn-primary"><i class="fa-solid fa-download mr-1"></i> Télécharger</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body text-center">
                @if($type === 'image')
                    <img src="{{ $pr->url() }}" alt="{{ $pr->name }}" class="img-fluid rounded">
                @else
                    <video controls class="w-100 rounded" style="max-height: 75vh">
                        <source src="{{ $pr->url() }}">
                        Votre navigateur ne sait pas lire cette vidéo : utilisez le bouton « Télécharger ».
                    </video>
                @endif
            </div>
        </div>
    </div>
@endsection
