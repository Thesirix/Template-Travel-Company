@extends('layouts.admin')
@section('title', 'Catalogue bateau · ' . $brand->name())

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
    @include('admin.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <h1 class="h3 mb-1">Catalogue bateau</h1>
            <p class="text-gray-500 mb-0">Listes proposées aux agents dans les demandes de traversée. Un élément déjà utilisé ne peut pas être supprimé.</p>
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
                            <form method="POST" action="{{ route('admin.bateau.store', $key) }}" class="form-row mb-3">
                                @csrf
                                @if($catalog['perDestination'])
                                    <div class="col-sm-5 mb-2">
                                        <select name="destination_id" class="form-control" required aria-label="Destination">
                                            @foreach($destinations as $destination)
                                                <option value="{{ $destination->id }}">{{ $destination->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="col mb-2">
                                    <input type="text" name="name" class="form-control" required maxlength="150" placeholder="Nom" aria-label="Nom">
                                </div>
                                <div class="col-auto mb-2"><button class="btn btn-primary">Ajouter</button></div>
                            </form>
                            <table class="table table-sm mb-0">
                                <tbody>
                                @forelse($catalog['items'] as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        @if($catalog['perDestination'])
                                            <td class="text-gray-500">{{ $item->destination->name ?? '—' }}</td>
                                        @endif
                                        <td class="text-right">
                                            <form method="POST" action="{{ route('admin.bateau.destroy', $key) }}" onsubmit="return confirm('Supprimer « {{ $item->name }} » ?')">
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
