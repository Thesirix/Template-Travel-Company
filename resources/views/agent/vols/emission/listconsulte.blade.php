@extends('layouts.admin')

@section('SideBar')
    <x-side-bar/>
@endsection


@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@section('styles')
    <style>
        .table_content p {
            margin-bottom: 0;
        }

        th, td {
            white-space: nowrap;
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }

    </style>

@endsection


@section('content')
    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Emission</h1>
            <img src="{{$flightsGroup->country->image}}" style="height: 50px">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('agent.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">{{$flightsGroup->country->nom}}</li>
                <li class="breadcrumb-item">Emission</li>
                <li class="breadcrumb-item">Consultation</li>
            </ol>
        </div>
        <div class="card" style="font-size: 20px">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Infotmations</h6>
            </div>

            <div class="card-body d-flex justify-content-between">
                <p>
                    {{'Pnr :'.$vol->pnr}}
                </p>
                <p>
                    Date
                    @if(in_array($vol->type_voyage,['aller_simple','aller_retour']))
                        {{\App\Support\Dates::format($vol->date_aller, 'd/m/Y')}}
                    @endif

                    @if($vol->type_voyage=='retour_simple')
                        {{\App\Support\Dates::format($vol->date_aller, 'd/m/Y')}}
                    @elseif($vol->type_voyage=='aller_retour')
                        {{\App\Support\Dates::format($vol->date_retour, 'd/m/Y')}}
                    @endif
                </p>
                <form method="post"
                      action="{{route('agent.vols.emission.export', $flightsGroup->id)}}"
                      class="col-2">
                    @csrf
                    <input type="hidden" name="vol_id" value="{{$vol->id}}">
                    <button class="btn btn-primary">Telecharger</button>
                </form>
                
                    <form method="post"
                          action="{{route('agent.vols.emission.export.format', [$flightsGroup->id, 'volotea'])}}"
                          class="col-2">
                        @csrf
                        <input type="hidden" name="vol_id" value="{{$vol->id}}">
                        <button class="btn btn-secondary">Volotea</button>
                    </form>

                
                    <form method="post"
                          action="{{route('agent.vols.emission.export.format', [$flightsGroup->id, 'amadeus'])}}"
                          class="col-2">
                        @csrf
                        <input type="hidden" name="vol_id" value="{{$vol->id}}">
                        <button class="btn btn-secondary">Amadeus</button>
                    </form>
                    <form method="post"
                          action="{{route('agent.vols.emission.export.format', [$flightsGroup->id, 'ethiopian'])}}"
                          class="col-2">
                        @csrf
                        <input type="hidden" name="vol_id" value="{{$vol->id}}">
                        <button class="btn btn-secondary">Ethiopian</button>
                    </form>
                

            </div>
        </div>
        <div class="card mb-4 mt-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Résérvations</h6>
            </div>

            <div class="card-body table-responsive">

                <table class="table table-flush">
                    <thead>
                    <tr>
                        <th scope="col" style="width: 50px">Num</th>
                        <th scope="col">Nom</th>
                        <th scope="col">Prenom</th>
                        <th scope="col" style="width: 50px">Statut</th>
                        <th scope="col">Agent</th>
                        <th scope="col">Facture</th>
                        <th scope="col">Solde</th>
                        <th scope="col">Vérifier</th>
                        
                            <th scope="col">Ebillet</th>
                        
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody class="table_content">
                    @php
                        $passengerCounter = 1;
                    @endphp
                    @foreach($vol->reservations as $res)
                        @if($res->status == 1)
                            <tr class="vol_row">
                                <td>
                                    @foreach($res->passagers as $pssgr)
                                        <p>
                                            @if(strcasecmp($pssgr->type, 'bebe') != 0)
                                                {{$passengerCounter}}
                                                @php
                                                    $passengerCounter++;
                                                @endphp
                                            @endif
                                        </p>
                                    @endforeach
                                </td>
                                <td class="text-left" style="font-size: 16px;font-weight: 700">
                                    @foreach($res->passagers as $pssgr)
                                        <p>-{{$pssgr->nom}}</p>
                                    @endforeach
                                </td>
                                <td class="text-left" style="font-size: 16px;font-weight: 700">
                                    @foreach($res->passagers as $pssgr)
                                        <p>-{{$pssgr->prenom}}</p>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($res->passagers as $pssgr)
                                        <p>{{$pssgr->type}}</p>
                                    @endforeach
                                </td>
                                <td style="font-size: 16px;font-weight: 700">
                                    {{$res->gent->name}} {{$res->gent->prenom}}
                                </td>
                                <td style="font-size: 16px;font-weight: 700">
                                    {{$res->bill?->n_acompte}}
                                </td>
                                <td style="font-size: 16px;font-weight: 700">
                                    {{$res->prix_total - $res->prix_payed}} €
                                </td>
                                <td style="font-size: 16px;font-weight: 700">
                                    @foreach($res->passagers as $pssgr)
                                        <p>
                                        <span
                                            class="badge  @if($pssgr->verifier=='NON') badge-danger @else badge-success @endif">{{$pssgr->verifier}}</span>
                                        </p>
                                    @endforeach
                                </td>
                                
                                    <td>
                                        <a href="{{route('agent.vols.vente.e-billet', [$flightsGroup->id, $res->id])}}"
                                           class="btn btn-sm btn-primary">
                                            Ebillet
                                        </a>
                                    </td>
                                
                                <td>
                                    <a href="{{route('agent.vols.emission.reservation', [$flightsGroup->id, $res->id])}}"
                                       class="btn btn-sm btn-primary">
                                        Consulter
                                    </a>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@push("script")
    <script>
        $(document).ready(function () {
            $(document).on('mouseenter', '.vol_row', function () {
                $(this).children('td').addClass("table-primary")
            })
            $(document).on('mouseleave', '.vol_row', function () {
                $(this).children('td').removeClass("table-primary")
            })
        })
    </script>
@endpush
