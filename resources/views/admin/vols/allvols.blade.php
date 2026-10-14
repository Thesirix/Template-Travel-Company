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
                {{ucfirst($destination) }}
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">Gestion vols</li>
            </ol>
        </div>

        <a href="{{route('admin.vols.create', $destination)}}" class="btn btn-primary mb-5">Ajouter</a>

        <div class="card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recherche</h6>
            </div>
            <form method="get" action="{{ route('admin.vols', [$destination, 'archive' => request('archive')]) }}"
                  class="card-body d-flex flex-wrap align-items-end" style="gap: 16px">
                <div>
                    <label for="compagnie_id">Compagnie</label>
                    <select id="compagnie_id" name="compagnie_id" class="form-control">
                        <option value=""></option>
                        @foreach($compagnies as $compagnie)
                            <option
                                value="{{ $compagnie->id }}" {{ request('compagnie_id') == $compagnie->id ? 'selected' : '' }}>
                                {{ $compagnie->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="itineraire">Itinéraire</label>
                    <input type="text" id="itineraire" name="itineraire" class="form-control"
                           value="{{ request('itineraire') }}">
                </div>
                <div>
                    <label for="type_voyage">Type</label>
                    <select id="type_voyage" name="type_voyage" class="form-control">
                        <option value=""></option>
                        <option value="aller_retour" {{ request('type_voyage') == 'aller_retour' ? 'selected' : '' }}>
                            Aller retour
                        </option>
                        <option value="aller_simple" {{ request('type_voyage') == 'aller_simple' ? 'selected' : '' }}>
                            Aller simple
                        </option>
                        <option value="retour_simple" {{ request('type_voyage') == 'retour_simple' ? 'selected' : '' }}>
                            Retour simple
                        </option>
                    </select>
                </div>
                <div>
                    <label for="date_aller">Aller</label>
                    <input type="date" id="date_aller" name="date_aller" class="form-control"
                           value="{{ request('date_aller') }}">
                </div>
                <div>
                    <label for="date_retour">Retour</label>
                    <input type="date" id="date_retour" name="date_retour" class="form-control"
                           value="{{ request('date_retour') }}">
                </div>
                <div>
                    <label for="ville_id">Ville</label>
                    <select id="ville_id" name="ville_id" class="form-control">
                        <option value=""></option>
                        @foreach($villes as $ville)
                            <option
                                value="{{ $ville->id }}" {{ request('ville_id') == $ville->id ? 'selected' : '' }}>
                                {{ $ville->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary">Rechercher</button>
            </form>
        </div>
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Vols</h6>
                </div>
                <div class="table-responsive">
                    
                        <table class="table table-flush">
                            <thead>
                            <tr>
                                <th scope="col">Companie</th>
                                <th scope="col">Itineraire</th>
                                <th scope="col">Type</th>
                                <th scope="col">Aller</th>
                                <th scope="col">Retour</th>
                                
                                    <th scope="col">Période</th>
                                
                                    <th scope="col">Ville</th>
                                
                                <th scope="col">Seats</th>
                                <th scope="col">Vendu</th>
                                <th scope="col">Reste</th>
                                <th scope="col">PNR</th>
                                <th scope="col">Adulte</th>
                                <th scope="col">Enfant</th>
                                
                                    <th scope="col">Bebe</th>
                                
                                <th scope="col">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($vols as $vol)
                                <tr>
                                    <th scope="row">
                                        {{$vol->company->nom}}
                                    </th>
                                    <th scope="row">
                                        {{$vol->itineraire}}
                                    </th>
                                    <th scope="row">
                                        {{$vol->type_voyage}}
                                    </th>
                                    <td>
                                        @if(in_array($vol->type_voyage,['aller_simple','aller_retour']))
                                            {{\Carbon\Carbon::parse($vol->date_aller)->format('d/m/Y')}}
                                        @endif
                                    </td>
                                    <td>
                                        @if($vol->type_voyage == 'aller_retour')
                                            {{\Carbon\Carbon::parse($vol->date_retour)->format('d/m/Y')}}
                                        @elseif($vol->type_voyage == 'retour_simple' )
                                            {{\Carbon\Carbon::parse($vol->date_aller)->format('d/m/Y')}}
                                        @endif
                                    </td>
                                    
                                        <td>
                                            {{$vol->period_voyage}}
                                        </td>
                                    
                                        <td>
                                            @isset($vol->ville)
                                                {{$vol->ville->nom}}
                                            @endisset
                                        </td>
                                    
                                    <td>{{$vol->place_total}}</td>
                                    <td>{{$vol->place_reserver}}</td>
                                    <td>{{$vol->place_disponible}}</td>
                                    <td>{{$vol->pnr}}</td>
                                    <td>{{$vol->prix_vente_adult}}€</td>
                                    <td>{{$vol->prix_vente_enfant}}€</td>
                                    
                                        <td>{{$vol->prix_vente_bebe}}€</td>
                                    
                                    <td class="d-flex align-items-center">
                                        <a href="{{ route('admin.vols.edit', [$vol->destination, $vol->id]) }}"
                                           class="btn btn-primary btn-sm">Consulter</a>
                                        <a href="{{ route('admin.vols.copy', [$vol->destination, $vol->id]) }}" class="btn btn-primary btn-sm">Copier</a>
                                        <form method="post"
                                              action="{{route('admin.vols.destroy', $vol->destination)}}">
                                            @csrf
                                            <input type="hidden" name="vol_id" value="{{$vol->id}}">
                                            <button class="btn btn-danger btn-sm">Supprimer</button>
                                        </form>

                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    
                </div>
            </div>
        </div>
        <!-- Button trigger modal -->
        <!-- Modal -->
        <div class="modal fade" id="etatmodel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border: none !important;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body d-flex align-items-center">
                        @if(session()->get('etat')=='success')
                            <img src="{{asset('sys/success.svg')}}" style="height: 60px"/>
                            <span style="font-size: 20px" class="ml-4">Vol supprimer avec succés</span>
                        @else
                            <img src="{{asset('sys/warning.svg')}}" style="height: 60px"/>
                            <span style="font-size: 20px" class="ml-4">Il y a des reservations</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')

    <script>
        $(document).ready(function () {
            @if(session()->get('etat'))
            $('#etatmodel').modal('show')
            @endif
        })
    </script>

@endsection
