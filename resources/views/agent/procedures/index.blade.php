@extends('layouts.admin')
@section('title', 'Procédures · ' . $brand->name())

@section('SideBar')
    <x-side-bar />
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <h1 class="h3 mb-1">Procédures</h1>
            <p class="text-gray-500 mb-0">Documents, fiches et vidéos de l'agence.</p>
        </div>

        <div class="row">
            @forelse($procedures as $procedure)
                @php($type = $procedure->type())
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="mb-3 text-primary" style="font-size: 1.8rem">
                                <i class="fa-solid {{ ['image' => 'fa-image', 'video' => 'fa-film', 'document' => 'fa-file-lines'][$type] }}"></i>
                            </div>
                            <h6 class="font-weight-bold mb-3">{{ $procedure->name }}</h6>
                            <div class="mt-auto d-flex" style="gap: 8px">
                                <a href="{{ route('agent.procedures.show', $procedure->id) }}" class="btn btn-light btn-sm" @if($type === 'document') target="_blank" @endif>
                                    <i class="fa-solid fa-eye mr-1"></i> Ouvrir
                                </a>
                                <a href="{{ route('agent.procedures.download', $procedure->id) }}" class="btn btn-light btn-sm">
                                    <i class="fa-solid fa-download mr-1"></i> Télécharger
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card"><div class="card-body text-center text-gray-500 py-5">Aucune procédure pour l'instant.</div></div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
