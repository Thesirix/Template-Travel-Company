@extends('layouts.admin')

@section('SideBar')
    @include('admin.partials.side-nav')
@endsection

@section('TopBar')
    @include('admin.partials.top-nav')
@endsection
@section('content')

    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                Vol Groups
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Vol Groups
            </ol>
        </div>
        <div class="col-12">
            <div class="card mb-4">
                <div class="table-responsive">
                    <table class="table table-flush">
                        <thead>
                        <tr class="headings">
                            <th class="column-title">Nom</th>
                            <th class="column-title">Pays</th>
                            <th class="column-title">Type</th>
                            <th class="column-title">Méme itenaraire</th>
                            <th class="column-title">Actif</th>
                            <th class="column-title">Date de création</th>
                            <th class="column-title no-link last"><span class="nobr">Action</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($volGroups as $group)
                            <tr class="even pointer">
                                <td class=" ">{{$group->name}}</td>
                                <td class=" ">{{$group->country->nom}}</td>
                                <td class=" ">{{$group->type}}</td>
                                <td class=" ">{{$group->same_itineraire ? 'Oui' : 'Non'}}</td>
                                <td class=" ">{{$group->is_active ? 'Oui' : 'Non'}}</td>
                                <td class=" ">{{\Carbon\Carbon::parse($group->created_at)->format('d/m/Y')}}</td>
                                <td class=" ">
                                    <a href="{{ route('admin.groupes-vols.edit', $group->id) }}" class="btn btn-primary">
                                        Modifier
                                    </a>
                                    <form method="post" action="{{ route('admin.groupes-vols.destroy') }}"
                                          style="display: inline-table;"
                                          onsubmit="return confirm('Vous êtes sûre?')">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="id" value="{{$group->id}}">
                                        <button type="submit" class="btn btn-danger waves-effect">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
