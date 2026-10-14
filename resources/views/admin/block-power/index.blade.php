@extends('layouts.admin')
@section('title', 'Allotements · ' . $brand->name())

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
    @include('admin.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <h1 class="h3 mb-1">Allotements</h1>
            <p class="text-gray-500 mb-0">Listes proposées aux agents lors de la création d'une option, et guide d'utilisation.</p>
        </div>

        <div class="card mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Guide</h6></div>
            <div class="card-body d-flex flex-wrap align-items-center">
                <p class="mb-0 mr-auto">
                    @if($guide)
                        Guide actuel : <strong>{{ $guide->name }}</strong>, déposé le {{ \Illuminate\Support\Carbon::parse($guide->created_at)->format('d/m/Y') }}.
                    @else
                        Aucun guide déposé : les agents voient un message à la place.
                    @endif
                </p>
                <form method="POST" action="{{ route('admin.block-power.guide.store') }}" enctype="multipart/form-data" class="form-inline">
                    @csrf
                    <input type="file" name="file" class="form-control-file mr-2" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="width: 260px">
                    <button class="btn btn-primary">{{ $guide ? 'Remplacer' : 'Déposer' }}</button>
                </form>
            </div>
        </div>

        <div class="row">
            @foreach($catalogs as $key => $catalog)
                <div class="col-xl-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header py-3 d-flex align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">{{ $catalog['title'] }}</h6>
                            <span class="badge badge-primary">{{ $catalog['items']->count() }}</span>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.block-power.store', $key) }}" class="form-row mb-3">
                                @csrf
                                <div class="col"><input type="text" name="name" class="form-control" required maxlength="150" placeholder="Nom" aria-label="Nom"></div>
                                <div class="col-auto"><button class="btn btn-primary">Ajouter</button></div>
                            </form>
                            <table class="table table-sm mb-0">
                                <tbody>
                                @forelse($catalog['items'] as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td class="text-right">
                                            <form method="POST" action="{{ route('admin.block-power.destroy', $key) }}" onsubmit="return confirm('Supprimer « {{ $item->name }} » ?')">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $item->id }}">
                                                <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="text-gray-500">Aucun élément.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
