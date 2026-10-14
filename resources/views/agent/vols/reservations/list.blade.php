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
    <x-side-bar/>
@endsection

@section('TopBar')
    @include('agent.partials.top-nav')
@endsection

@section('content')
    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Recherche</h1>

            <img src="{{$flightsGroup->country->image}}" style="height: 50px">

            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('agent.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">{{$flightsGroup->country->nom}}</li>
                <li class="breadcrumb-item">Recherche</li>
            </ol>
        </div>
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    Recherche
                </h6>
            </div>
            <form method="get" action="{{route('agent.vols.reservations', $flightsGroup->id)}}"
                  class="card-body d-flex flex-wrap ">
                <div class="mt-3 mr-3">
                    <label for="gent">Agent</label>
                    <select class="form-control" id="gent" name="gent" style="width: 200px">
                        <option value="">Tous les agents</option>
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
                    <input type="text" class="form-control" name="date_aller" value="{{$request->date_aller}}"
                           id="date_aller" style="width: 200px">
                </div>
                <div class="mt-3 mr-3">
                    <label for="date_retour">Date Retour</label>
                    <input type="text" class="form-control" name="date_retour" value="{{$request->date_retour}}"
                           id="date_retour" style="width: 200px">
                </div>
                <div class="mt-3 mr-3 d-flex align-items-end">
                    <button class="btn btn-primary">Recherche</button>
                </div>
            </form>
        </div>
        <div class="card mb-4 mt-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Résérvations</h6>
            </div>
            <div class="table-responsive">
                <table class="card-body table table-flush">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">Date Aller</th>
                        <th scope="col">Date Retour</th>
                        <th scope="col">Nom complet</th>
                        <th scope="col">Agent</th>
                        <th scope="col">Date de l'opération</th>
                        
                            <th scope="col">N° Facture</th>
                            <th scope="col">Vérifier</th>
                            <th scope="col">Inscrit</th>
                            <th scope="col">REF</th>
                        
                        <th scope="col">Status</th>
                        <th scope="col">Historique</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody>
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
                                    -{{$passager->nom.' '.$passager->prenom}}<br>
                                @endforeach
                            </td>
                            
                            <td>{{$reservation->gent->name}} {{$reservation->gent->prenom}}</td>
                            <td>{{\Illuminate\Support\Carbon::parse($reservation->history_date)->format('d/m/Y')}}</td>
                            
                                <td>{{$reservation->bill?->n_acompte}}</td>
                                <td>
                                    @foreach($reservation->passagers as $pssgr)
                                        <p>
                                            <i class="fas @if(strtolower($pssgr->verifier) == 'oui') fa-check text-success @else fa-times text-danger @endif"
                                               style="font-size: 22px"></i>
                                        </p>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($reservation->passagers as $pssgr)
                                        <p>
                                            <i class="fas @if(($reservation->type_voyage== 'aller_retour_diffrent_vol' && $pssgr->inscrit_aller && $pssgr->inscrit_retour) || ($reservation->type_voyage == 'aller_simple' && $pssgr->inscrit_aller) || ($reservation->type_voyage == 'retour_simple' && $pssgr->inscrit_retour)) fa-check text-success @else fa-times text-danger @endif"
                                               style="font-size: 22px"></i>
                                        </p>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($reservation->passagers as $pssgr)
                                        <p>
                                            {{$pssgr->ref_aller}}
                                            @if($pssgr->ref_aller!= '' && $pssgr->ref_retour!= '')
                                                ->
                                            @endif
                                            {{$pssgr->ref_retour}}
                                        </p>
                                    @endforeach
                                </td>
                            
                            <td>
                                <div class="d-flex align-items-center">
                                    @switch($reservation->history_type)
                                        @case(app\Enums\ReservationHistoryEnum::create->value)
                                            <i class="fas fa-check text-success" style="font-size: 22px"></i>
                                            @break
                                        @case(app\Enums\ReservationHistoryEnum::cancel->value)
                                            <i class="fas fa-times text-danger" style="font-size: 22px"></i>
                                            @break
                                        @case(app\Enums\ReservationHistoryEnum::modify_date->value)
                                            <i class="fas fa-exclamation-triangle text-warning"
                                               style="font-size: 22px"></i>
                                            @break
                                        @case(app\Enums\ReservationHistoryEnum::add_passager->value)
                                            <i class="fas fa-plus text-success"
                                               style="font-size: 22px"></i>
                                            @break
                                        @case(app\Enums\ReservationHistoryEnum::delete_passager->value)
                                            <i class="fas fa-minus text-danger"
                                               style="font-size: 22px"></i>
                                            @break
                                    @endswitch
                                    <p class="mb-0 ml-2" style="font-size: 10px"> {{$reservation->history_type}} {{$reservation->history_details}}</p>
                                </div>
                            </td>
                            <td>
                                <a data-toggle="collapse" href="#collapseReservationHistories{{$reservation->id}}"
                                   role="button" aria-expanded="false">
                                    Cliquez ici
                                </a>
                                <div class="collapse" id="collapseReservationHistories{{$reservation->id}}">
                                    <div class="card card-body">
                                        <table>
                                            @foreach($reservation->histories as $history)
                                                <tr>
                                                    <td class="py-0">{{$history->type}} {{$history->details}}</td>
                                                    <td class="py-0">{{$history->gent->name}} {{$history->gent->prenom}}</td>
                                                    <td class="py-0">{{\Illuminate\Support\Carbon::parse($history->created_at)->format('d/m/Y H:i')}}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>

                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                            id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                        Action
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a href="{{route('agent.vols.reservations.show', [$flightsGroup->id, $reservation->id])}}"
                                           class="dropdown-item">Consulter</a>
                                        @if(\Illuminate\Support\Facades\Auth::guard('gestion')->check())
                                            <form method="post" class="dropdown-item"
                                                  action="{{route('agent.vols.reservations.destroy', $flightsGroup->id)}}">
                                                @csrf
                                                <input type="hidden" name="reservation_id" value="{{$reservation->id}}">
                                                <button class="border-0 px-0 text-danger delete_passager"
                                                        style="background: none">
                                                    Supprimer
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

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

            const outbound_config = {
                enable: @json($outboundDates),
                altInput: true,      // Enables a second input for user-friendly display
                altFormat: "d-m-Y",  // Format to display to the user
                dateFormat: "Y-m-d",
                defaultDate: "{{$request->date_aller}}"
            };
            const return_config = {
                enable: @json($returnDates),
                altInput: true,      // Enables a second input for user-friendly display
                altFormat: "d-m-Y",  // Format to display to the user
                dateFormat: "Y-m-d",
                defaultDate: "{{$request->date_retour}}"
            };

            $("#date_aller").flatpickr(outbound_config);
            $("#date_retour").flatpickr(return_config);

            $('.delete_passager').click(function () {
                $(".spinner-overlay").css('display', 'flex');
            })

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
