@extends('layouts.admin')

@section('styles')
    <style>
        td, th {
            text-align: start;
            white-space: nowrap;
            padding-top: 15px !important;
            padding-bottom: 15px !important;
        }
    </style>
@endsection


@section('SideBar')
    <x-side-bar />
@endsection


@section('TopBar')
    @include('agent.partials.top-nav')
@endsection


@section('content')
    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Gestion</h1>
            <img src="{{$flightsGroup->country->image}}" style="height: 50px">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('agent.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">{{$flightsGroup->country->nom}}</li>
                <li class="breadcrumb-item">Gestion</li>
            </ol>
        </div>
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    Recherche
                </h6>
            </div>
            <form method="get" action="{{route('agent.vols.dossiers', $flightsGroup->id)}}"
                  class="card-body d-flex flex-wrap ">
                <div class="mt-3 mr-3">
                    <label for="gent">Agent</label>
                    <select class="form-control" id="gent" name="gent" style="width: 200px">
                        <option></option>
                        @foreach($gents as $gent)
                            <option value="{{$gent->id}}"
                                    @if($request->gent==$gent->id) selected @endif>{{$gent->name.' '.$gent->prenom}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-3 mr-3">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" name="nom" id="nom" value="{{$request->nom}}"
                           style="width: 200px">
                </div>
                <div class="mt-3 mr-3">
                    <label for="prenom">Prenom</label>
                    <input type="text" class="form-control" name="prenom" value="{{$request->prenom}}" id="prenom"
                           style="width: 200px">
                </div>
                <div class="mt-3 mr-3">
                    <label for="date_aller">Date Aller</label>
                    <input type="date" class="form-control" name="date_aller" value="{{$request->date_aller}}"
                           id="date_aller" style="width: 200px">
                </div>
                <div class="mt-3 mr-3">
                    <label for="date_retour">Date Retour</label>
                    <input type="date" class="form-control" name="date_retour" value="{{$request->date_retour}}"
                           id="date_retour" style="width: 200px">
                </div>
                <div class="mt-3 mr-3 d-flex align-items-end">
                    <button class="btn btn-primary" >Recherche</button>
                </div>
            </form>
        </div>

        <div class="card mb-4 mt-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Résérvations</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-flush">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col" class="sort_date_aller sort " target="date_aller" type="desc"
                            style="cursor: pointer">Date Aller
                        </th>
                        <th scope="col" class="sort_date_retour sort" target="date_retour" type="desc"
                            style="cursor: pointer">Date Retour
                        </th>
                        <th scope="col">Nom complet</th>
                        <th scope="col">Total dossier</th>
                        <th scope="col">Total Acompte Verse</th>
                        <th scope="col">Solde A Payer</th>
                        <th scope="col">Date</th>
                        <th scope="col">Dossier</th>
                        <th scope="col">Facture</th>
                        <th scope="col">Agent</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody id="rows_data">
                    @foreach($reservations as $reservation)
                        <tr class="vol_row">
                            <td style="font-size: 17px;font-weight: 700">
                                @if($reservation->type_voyage != 'retour_simple')
                                    {{\Carbon\Carbon::parse($reservation->vol->date_aller)->format('d/m/Y')}}
                                @endif
                            </td>
                            <td style="font-size: 17px;font-weight: 700">
                                @if($reservation->type_voyage != 'aller_simple')
                                    @if($reservation->type_voyage=='aller_retour_same_vol')
                                        {{\Carbon\Carbon::parse($reservation->vol->date_retour)->format('d/m/Y')}}
                                    @elseif($reservation->type_voyage=='aller_retour_diffrent_vol')
                                        {{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->format('d/m/Y')}}
                                    @elseif($reservation->type_voyage=='retour_simple')
                                        {{\Carbon\Carbon::parse($reservation->vol->date_aller)->format('d/m/Y')}}
                                    @endif
                                @endif
                            </td>
                            <td style="font-size: 17px;font-weight: 700">
                                @foreach($reservation->passagers as $passager)
                                    <p>{{$passager->nom.' '.$passager->prenom}}</p>
                                @endforeach
                            </td>
                            <td>{{$reservation->prix_total}} €</td>
                            <td>{{$reservation->prix_payed}} €</td>
                            <td>{{$reservation->prix_total - $reservation->prix_payed}} €</td>
                            <td>{{\Carbon\Carbon::parse($reservation->vol->date_depart)->subDays(45)->format('d/m/Y')}}</td>
                            <td >
                                @foreach($reservation->passagers as $passager)
                                    <p class="badge mb-4 d-block @if($passager->dossier_etat=='Incomplet') badge-danger @else badge-success @endif">{{$passager->dossier_etat}}</p>
                                @endforeach
                            </td>
                            <td style="font-size: 16px;font-weight: 700">
                                {{$reservation->bill?->n_acompte}}
                            </td>
                            <td>{{$reservation->gent->name}} {{$reservation->gent->prenom}}</td>
                            <td>
                                <a href="{{route('agent.vols.dossiers.show', [$flightsGroup->id, $reservation->id])}}"
                                   class="btn btn-primary btn-sm" >Consulter</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="etatmodel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="border: none !important;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body d-flex align-items-center">
                    <img src="{{asset('sys/success.gif')}}" style="height: 140px"/>
                    <span style="font-size: 20px" class="ml-4">Felication votre inscription est bien enregistree</span>
                </div>
            </div>
        </div>
    </div>
@endsection
@push("script")
    <script>
        $(document).ready(function () {
            @if(session()->get('etat')=='success')
            $('#etatmodel').modal('show')
            @endif

            $(document).on('mouseenter', '.vol_row', function () {
                $(this).children('td').addClass("table-primary")
            })
            $(document).on('mouseleave', '.vol_row', function () {
                $(this).children('td').removeClass("table-primary")
            })
        })
    </script>

@endpush
