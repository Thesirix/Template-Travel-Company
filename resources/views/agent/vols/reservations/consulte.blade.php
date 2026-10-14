@extends('layouts.admin')

@section('styles')
    <style>
        .clearfix:after, form:after {
            display: none !important;
        }

        .custum_table td, .custum_table th {
            text-align: start;
            width: 200px;
            padding-bottom: 10px;
            padding-top: 10px;
        }

        .table td, .table th {
            padding-bottom: 7px;
            padding-top: 7px;
            padding-left: 10px;
            text-align: start;
        }

        td, th {
            text-align: start;
        }


        .custom-file-upload {
            border: 1px solid #ccc;
            display: inline-block;
            padding: 6px 12px;
            cursor: pointer;
        }


        .img_preview {
            cursor: pointer;
            position: relative;
            max-width: 200px;
        }

        .error {
            color: red;
        }

        .passportfieldfile {
            display: none;
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
            <h1 class="h3 mb-0 text-gray-800">
                Consultation
            </h1>
            <img src="{{$flightsGroup->country->image}}" style="height: 50px">

            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('agent.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">{{$flightsGroup->country->nom}}</li>
                <li class="breadcrumb-item">Recherche</li>
                <li class="breadcrumb-item">Consultation</li>

            </ol>
        </div>
        <form action="{{ route('agent.vols.reservations.update', $flightsGroup->id) }}"
              id="passagers_form" class="row"
              method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="reservation_id" value="{{$reservation->id}}">
            <input type="hidden" name="nb_new_adult" id="nb_new_adult" value="0">
            <input type="hidden" name="nb_new_enfant" id="nb_new_enfant" value="0">
            <input type="hidden" name="nb_new_bebe" id="nb_new_bebe" value="0">

            <input type="hidden" name="nb_old_adult" id="nb_old_adult" value="{{$reservation->nb_adult}}">
            <input type="hidden" name="nb_old_enfant" id="nb_old_enfant" value="{{$reservation->nb_enfant}}">
            <input type="hidden" name="nb_old_bebe" id="nb_old_bebe" value="{{$reservation->nb_bebe}}">

            <input type="hidden" name="nb_deleted_passagers" id="nb_deleted_passagers">

            

            <div id="deleted_passagers">
            </div>

            <div class="col-lg-6">
                <!-- Form Basic -->
                <div class="card mb-4  pb-3" style="border: solid 5px #6777ef">
                    <div class="card-header pb-3 pt-0">
                        <div style="height: 30px" class="bg-primary text-center">
                            <h6 class="m-0 font-weight-bold text-light">
                                Résérvation
                            </h6>
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="custum_table" style="width: 450px">
                            <tr>
                                <td style="width: 40%">N° Réservation</td>
                                <td class="d-flex justify-content-between align-items-center">
                                    {{substr($reservation->passagers[0]['nom'],0,3).''.$reservation->id}}
                                    @if($reservation->status)
                                        <div class="text-right">
                                            <button class="btn btn-primary"
                                                    id="annuler_tout" type="button">Annuler
                                                Tout
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Date Réservation</td>
                                <td>{{\App\Support\Dates::format($reservation->created_at) }}</td>
                            </tr>
                            <tr>
                                <td>Agent</td>
                                <td class="d-flex align-items-center">
                                    {{$reservation->gent->name}} {{$reservation->gent->prenom}}
                                    <a href="{{route('agent.vols.vente.contrat', [$flightsGroup->id, $reservation->id])}}"
                                       class="btn btn-primary ml-3"
                                       type="button">
                                        Contrat
                                    </a>
                                </td>

                            </tr>
                            <tr>
                                <td>Itineraire</td>
                                <td>
                                    {{$reservation->vol->itineraire}}
                                </td>
                            </tr>
                            @if($reservation->type_voyage != 'retour_simple')
                                <tr>
                                    <td>Date Aller</td>
                                    <td class="d-flex justify-content-between align-items-center">
                                        {{\Carbon\Carbon::parse($reservation->vol->date_aller)->format('d/m/Y')}}
                                        @if($reservation->status)
                                            <a href="{{ route('agent.vols.reservations.change-flight', [$flightsGroup->id, $reservation->id]) }}"
                                               class="btn btn-primary">Modifier</a>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            @if($reservation->type_voyage != 'aller_simple')
                                <tr>
                                    <td>Date Retour</td>
                                    <td>
                                        @if($reservation->type_voyage=='aller_retour_same_vol')
                                            {{\Carbon\Carbon::parse($reservation->vol->date_retour)->format('d/m/Y')}}
                                        @elseif($reservation->type_voyage=='aller_retour_diffrent_vol')
                                            {{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->format('d/m/Y')}}
                                        @elseif($reservation->type_voyage=='retour_simple')
                                            {{\Carbon\Carbon::parse($reservation->vol->date_aller)->format('d/m/Y')}}
                                            @if($reservation->status)
                                                <a href="{{ route('agent.vols.reservations.change-flight', [$flightsGroup->id, $reservation->id]) }}"
                                                   class="btn btn-primary">Modifier</a>
                                            @endif
                                        @endif
                                    </td>
                                    <td></td>
                                </tr>
                            @endif
                            <tr>
                                <td>Numero Facture</td>
                                <td>
                                    <div id="bill-section">
                                        {{$reservation->bill?->n_acompte}}
                                        @if($reservation->status)
                                            <button type="button" class="btn btn-primary" id="display-bill-form">
                                                Modifier
                                            </button>
                                        @endif
                                    </div>
                                    <div id="bill-form" style="display: none">
                                        <input type="number" name="billNumber" id="billNumber" class="form-control"
                                               value="{{$reservation->bill?->n_acompte}}" min="400000" max="600000">
                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                                        <button type="button" class="btn btn-danger" id="hide-bill-form"
                                                n_acompte="{{$reservation->bill?->n_acompte}}">
                                            Annuler
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <!-- Form Basic -->
                <div class="card mb-4  pb-3" style="border: solid 5px #6777ef">
                    <div class="card-header pb-3 pt-0">
                        <div style="height: 30px" class="bg-primary text-center">
                            <h6 class="m-0 font-weight-bold text-light">
                                @if($reservation->status)
                                    Ajouter Passagers
                                @else
                                    Passagers
                                @endif
                            </h6>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <input type="hidden" id="nbPassagers" class="input" name="nbPassagers"
                               value="{{$reservation->nb_adult+$reservation->nb_enfant+$reservation->nb_bebe}}">
                        @if($reservation->status)
                            <button class="btn btn-primary mb-3 mt-3" type="button">
                                Modifier
                            </button>
                        @endif
                        <p class="d-flex align-items-center  mt-2" style="width: 270px" type="adult">
                                <span id="adultcount" style="font-size: 18px"
                                      class="nbtext mr-4">{{$reservation->nb_adult}}</span>
                            @if($reservation->status)
                                <img src="{{asset('front/img/plus.png')}}" target="adultcountinput2"
                                     style="cursor:pointer;width: 20px"
                                     class="plus mr-4">
                            @endif
                            <span id="adultcount" style="font-size: 17px">Adultes</span>
                            <input type="hidden" id="adultcountinput" class="input" name="adultcountinput"
                                   value="{{$reservation->nb_adult}}">
                        </p>
                        <p class="d-flex align-items-center  mt-3" style="width: 270px" type="enfant">

                                <span id="enfantcount" style="font-size: 18px" class="nbtext mr-4">
                                    {{$reservation->nb_enfant}}
                        </span>
                            @if($reservation->status)
                                <img src="{{asset('front/img/plus.png')}}" target="enfantcountinput2"
                                     style="cursor:pointer;width: 20px"
                                     class="plus mr-4">
                            @endif
                            <span id="enfantcount" style="font-size: 17px">Enfant (2 - 11 ans)</span>
                            <input type="hidden" id="enfantcountinput" class="input" name="enfantcountinput"
                                   value="{{$reservation->nb_enfant}}">
                        </p>
                        
                            <p class="d-flex align-items-center  mt-3" style="width: 270px" type="bebe">
                                <span id="bebecount" style="font-size: 18px"
                                      class="nbtext mr-4">{{$reservation->nb_bebe}}</span>
                                @if($reservation->status)
                                    <img src="{{asset('front/img/plus.png')}}" target="bebecountinput2"
                                         style="cursor:pointer;width: 20px"
                                         class="plus mr-4">
                                @endif
                                <span id="bebecount" style="font-size: 17px">Bebe (< 2 ans)</span>
                                <input type="hidden" id="bebecountinput" max="1" class="input" name="bebecountinput"
                                       value="{{$reservation->nb_bebe}}">
                            </p>
                        
                    </div>
                </div>
                
            </div>
            <div class="col-lg-12">
                <!-- Form Basic -->
                <div class=" d-flex flex-wrap" id="passagersinputs">
                    @foreach($reservation->passagers as $person)
                        <div class="my-3 mx-3"
                             style="border:solid 2px @if($person->type=='bebe') #e74ac6 @elseif($person->type=='enfant') #f8953c @else #5656e7 @endif;width: 400px"
                             id="passager_{{$person->id}}">
                            <div class="text-light d-flex align-items-center justify-content-between px-4 py-3"
                                 style="font-size:20px;background-color: @if($person->type=='bebe') #e74ac6 @elseif($person->type=='enfant') #f8953c @else #5656e7 @endif ">
                                <button class="btn btn-primary modify_passager" type="button"
                                        passager_id="{{$person->id}}"
                                        passager_type="{{$person->type}}">
                                    Modifier
                                </button>
                                <button class="btn btn-primary enregister_passager form-submit"
                                        style="display:none;">Enregistrer
                                </button>

                                {{$person->type}} @if($person->type=='bebe')
                                    < 2
                                    ans
                                @elseif($person->type=='enfant')
                                    <
                                    12 ans
                                @endif

                                @if($reservation->status)
                                    <button class="btn btn-danger delete_passager"
                                            type="button" target="{{$person->id}}">Supprimer
                                    </button>
                                @else
                                    <div></div>
                                @endif
                            </div>
                            @php
                                $showVerificationSection = true;
                            @endphp

                            <x-agent.vols.common.reservation-passager-card :$reservation
                                                                          :destination="strtolower($flightsGroup->country->nom)"
                                                                          :$person
                                                                          :$assurences
                                                                          :showVerificationSection="$showVerificationSection"/>
                        </div>
                    @endforeach
                </div>
            </div>

        </form>
    </div>
    <div class="modal fade" id="deletepassagermodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog " role="document">
            <div class="modal-content">
                <div class="modal-header" style="border: none !important;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" class="modal-body submit-form annuler_tout"
                      action="{{route('agent.vols.reservations.cancel', $flightsGroup->id)}}"
                      style="font-size: 16px">
                    @csrf
                    <input type="hidden" name="reservation_id" value="{{$reservation->id}}"/>
                    <p style="font-size: 20px" class="text-center mb-5 annulation_text_1">Vous souhaitez annulez tout le
                        dossier</p>
                    <label>Pour quelles raisons</label>
                    <textarea class="form-control mb-3" name="all_annulation_reason" required></textarea>
                    
                        <p class="mb-3">Pénalités d'annulation {{$annulaion_price}} € par personne</p>
                        <p class="mb-5">Total : {{$annuletout_price}}€</p>
                    
                    <div class="d-flex justify-content-end form-submit">
                        <button class="btn btn-primary form-submit">Annuler tout</button>
                    </div>
                </form>
                <form method="post" class="modal-body annuler_one submit-form" style="font-size: 16px"
                      action="{{route('agent.vols.reservations.cancel-passenger', $flightsGroup->id)}}">
                    @csrf
                    <input type="hidden" name="reservation_id" value="{{$reservation->id}}">
                    <input type="hidden" name="passager_id" id="passager_id" value="">

                    <p style="font-size: 20px" class="text-center mb-5 annulation_text_1">Vous souhaitez annulez ce
                        dossier</p>
                    <label>Pour quelles raisons</label>
                    <textarea class="form-control mb-3" required name="all_annulation_reason"></textarea>
                    
                        <p class="mb-3">Pénalités d'annulation {{$annulaion_price}} € par personne</p>
                        <p class="mb-5">Total : {{$annulaion_price}} €</p>
                    
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary delete_passager_click form-submit" target=""
                        >Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="error_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 mb-0" style="border-bottom: none">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body d-flex align-items-center" style="font-size: 20px">
                    <img src="{{asset('sys/warning.svg')}}" style="height: 40px">
                    <p class="exist_message ml-3"></p>
                </div>
            </div>
        </div>
    </div>
    <x-agent.common.image-preview-modal/>
    <x-agent.vols.common.delete-passenger-passport-modal :groupId="$flightsGroup->id"/>
@endsection
@push("script")
    <script src="{{ asset('assets/js/validation/flightPassengerValidation.js') }}"></script>
    <script src="{{asset('assets/js/agent/vols/common/reservation-infos.js')}}"></script>
    <script>

        $(document).ready(function () {

            $('.submit-form').submit(function () {
                $(".spinner-overlay").css('display', 'flex');
            })

            {{--    passager managment (add and remove)--}}
            $('.plus').click(function () {

                let nb_old_adult = $('#nb_old_adult')
                let nb_old_enfant = $('#nb_old_enfant')
                let nb_old_bebe = $('#nb_old_bebe')
                let nbOldPassagers = parseInt(nb_old_adult.val()) + parseInt(nb_old_enfant.val()) + parseInt(nb_old_bebe.val())

                let nb_new_adult = $('#nb_new_adult')
                let nb_new_enfant = $('#nb_new_enfant')
                let nb_new_bebe = $('#nb_new_bebe')
                let nbNewPassagers = parseInt(nb_new_adult.val()) + parseInt(nb_new_enfant.val()) + parseInt(nb_new_bebe.val())

                let nbPassagers = parseInt(nbOldPassagers) + nbNewPassagers
                console.log("adda after " + nbPassagers)
                let type = $(this).parent().attr('type')
                let oldnum = $(this).parent().children('.input').val()
                let i = parseInt(nbNewPassagers) + 1

                let destination = "{{strtolower($flightsGroup->country->nom)}}";
                if (true) {

                    let bgColor, dateNaissanceMinDate = '', dateNaissanceMaxDate = '';
                    let passportMinDate = '', sejourMinDate = '';

                    @if($reservation->type_voyage=='aller_retour_same_vol')
                        passportMinDate = '{{\Carbon\Carbon::parse($reservation->vol->date_retour)->addMonths(6)->format('Y-m-d')}}'
                    sejourMinDate = '{{\Carbon\Carbon::parse($reservation->vol->date_retour)->format('Y-m-d')}}'
                    @elseif($reservation->type_voyage=='aller_retour_diffrent_vol')
                        passportMinDate = '{{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->addMonths(6)->format('Y-m-d')}}';
                    sejourMinDate = '{{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->format('Y-m-d')}}';
                    @elseif(in_array($reservation->type_voyage,['retour_simple','aller_simple']))
                        passportMinDate = '{{\Carbon\Carbon::parse($reservation->vol->date_aller)->addMonths(6)->format('Y-m-d')}}';
                    sejourMinDate = '{{\Carbon\Carbon::parse($reservation->vol->date_aller)->format('Y-m-d')}}';
                    @endif

                    if (type == 'adult') {
                        bgColor = '#5656e7'
                        @if($reservation->type_voyage=='aller_retour_same_vol')
                            dateNaissanceMaxDate = '{{\Carbon\Carbon::parse($reservation->vol->date_retour)->subYears(12)->subDays(1)->format('Y-m-d')}}';
                        @elseif($reservation->type_voyage=='aller_retour_diffrent_vol')
                            dateNaissanceMaxDate = '{{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->subYears(12)->subDays(1)->format('Y-m-d')}}';
                        @else
                            dateNaissanceMaxDate = '{{\Carbon\Carbon::parse($reservation->vol->date_aller)->subYears(12)->subDays(1)->format('Y-m-d')}}';
                        @endif

                    } else if (type == 'enfant') {
                        bgColor = '#f8953c'
                        @if($reservation->type_voyage=='aller_retour_same_vol')
                            dateNaissanceMinDate = '{{\Carbon\Carbon::parse($reservation->vol->date_retour)->subYears(12)->format('Y-m-d')}}';
                        dateNaissanceMaxDate = '{{\Carbon\Carbon::parse($reservation->vol->date_retour)->subYears(2)->format('Y-m-d')}}';
                        @elseif($reservation->type_voyage=='aller_retour_diffrent_vol')
                            dateNaissanceMinDate = '{{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->subYears(12)->format('Y-m-d')}}';
                        dateNaissanceMaxDate = '{{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->subYears(2)->format('Y-m-d')}}';
                        @elseif(in_array($reservation->type_voyage,['retour_simple','aller_simple']))
                            dateNaissanceMinDate = '{{\Carbon\Carbon::parse($reservation->vol->date_aller)->subYears(12)->format('Y-m-d')}}';
                        dateNaissanceMaxDate = '{{\Carbon\Carbon::parse($reservation->vol->date_aller)->subYears(2)->format('Y-m-d')}}';
                        @endif
                    } else {
                        bgColor = '#e74ac6'

                        @if($reservation->type_voyage=='aller_retour_same_vol')
                            dateNaissanceMinDate = '{{\Carbon\Carbon::parse($reservation->vol->date_retour)->subYears(2)->format('Y-m-d')}}';
                        @elseif($reservation->type_voyage=='aller_retour_diffrent_vol')
                            dateNaissanceMinDate = '{{\Carbon\Carbon::parse($reservation->volRetour->date_aller)->subYears(2)->format('Y-m-d')}}';
                        @elseif(in_array($reservation->type_voyage,['retour_simple','aller_simple']))
                            dateNaissanceMinDate = '{{\Carbon\Carbon::parse($reservation->vol->date_aller)->subYears(2)->format('Y-m-d')}}';
                        @endif

                    }

                    $html = '<div class="my-3 mx-3 ' + type + '_div_' + i + '" style="border:solid 2px ' + bgColor + ';width: 400px">' +
                        '<p class="text-light d-flex align-items-center justify-content-around py-3" style="font-size:20px;background-color: ' + bgColor + '">' +
                        '<button class="btn btn-primary enregister_passager form-submit" >Enregistrer</button>' +
                        type.toUpperCase() +
                        '<button class="btn btn-danger delete_new_passager"' +
                        'type="button" passager_type="' + type + '" target="' + type + '_div_' + i + '">Supprimer</button>' +
                        '</p>' +
                        '<div class="py-2 px-1">' +
                        '<table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%">' +
                        '<tr>' +
                        '<td class="pt-3 pl-3">Genre</td>' +
                        '<td class="pt-3">' +
                        '<input type="hidden" name="passagers[' + i + '][type]" value="' + type + '" />' +
                        '<select required="required" class="form-control" name="passagers[' + i + '][genre]" style="height:35px!important">' +
                        '<option value="male">Homme</option>' +
                        '<option value="female">Femme</option>' +
                        '</select>' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td class="pl-3">Nom</td>' +
                        '<td class="pt-3">' +
                        '<input required="required" type="text" id="' + type + '_nom_' + i + '" name="passagers[' + i + '][nom]" class="form-control nom" style="height:35px!important; ">' +
                        ' <p class="error"></p>' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td class="pl-3">Prenom</td>' +
                        '<td class="pt-3">' +
                        '<input required="required" type="text" id="' + type + '_prenom_' + i + '" name="passagers[' + i + '][prenom]" class="form-control prenom" style="height:35px!important; ">' +
                        ' <p class="error"></p>' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td class="pl-3">Date naissance</td>' +
                        '<td class="pt-3">' +
                        '<input required="required" min="' + dateNaissanceMinDate + '" max="' + dateNaissanceMaxDate + '" type="date"  name="passagers[' + i + '][date_naissance]"  id="' + type + '_date_naissance_' + i + '" class="form-control date_naissance" style="height:35px!important; ">' +
                        ' <p class="error"></p>' +
                        '</td>' +
                        '</tr>' +
                        
                            '</table>' +
                        '<table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%">' +
                        '<tr>' +
                        '<td class="pl-3">N Passport</td>' +
                        '<td class="pt-3">' +
                        '   <div class="iti iti--allow-dropdown">' +
                        '<input  type="text" name="passagers[' + i + '][passport]" id="' + type + '_passport_' + i + '" class="form-control passportfield" style="height:35px!important; ">' +
                        ' <p class="error"></p>' +
                        '</div>' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        ' <td class="pl-3">Download passport</td>' +
                        ' <td class="pt-3"> ' +
                        '<input  type="file" name="passagers[' + i + '][passport_file]" id="' + type + '_passport_file_' + i + '"  class=" passportfieldfile " style="height:35px!important; ">' +
                        '<label for="' + type + '_passport_file_' + i + '" class="custom-file-upload">' +
                        '<i class="fa fa-cloud-upload"></i> Custom Upload' +
                        '</label>' +
                        '  <div class="img_preview" ></div>' +
                        ' <p class="error"></p>' +
                        ' </td>' +
                        '</tr>' +
                        '<tr>' +
                        ' <td class="pl-3">Date expiration</td>' +
                        ' <td class="pt-3"> ' +
                        '   <input  min="' + passportMinDate + '" type="date" name="passagers[' + i + '][expiration]" id="' + type + '_passport_expiration_' + i + '" class="form-control date_expiration" style="height:35px!important; ">' +
                        ' <p class="error"></p>' +
                        ' </td>' +
                        '</tr>' +
                        '</table>' +
                        '<table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%">' +
                        '<tr>' +
                        '<td class="pl-3">N Titre Séjour</td>' +
                        '<td class="pt-3">' +
                        '<input  type="text" name="passagers[' + i + '][sejour]" id="' + type + '_sejour_' + i + '" class="form-control sejourfield" style="height:35px!important; ">' +
                        ' <p class="error"></p>' +
                        '</div>' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        ' <td class="pl-3">Download Titre Séjour</td>' +
                        ' <td class="pt-3"> ' +
                        '<input  type="file" name="passagers[' + i + '][sejour_file]" id="' + type + '_sejour_file_' + i + '" class=" passportfieldfile " style="height:35px!important;visibility:hidden ">' +
                        '<label for="' + type + '_sejour_file_' + i + '" class="custom-file-upload">' +
                        '<i class="fa fa-cloud-upload"></i> Custom Upload' +
                        '</label>' +
                        '  <div class="img_preview" ></div>' +
                        ' <p class="error"></p>' +
                        ' </td>' +
                        '</tr>' +
                        '<tr>' +
                        ' <td class="pl-3">Date expiration</td>' +
                        ' <td class="pt-3"> ' +
                        '   <input  min="' + sejourMinDate + '" type="date" name="passagers[' + i + '][sejour_expiration]" id="' + type + '_sejour_expiration_' + i + '" class="form-control date_expiration" style="height:35px!important; ">' +
                        ' <p class="error"></p>' +
                        ' </td>' +
                        '</tr>' +
                        '<tr>' +
                        ' <td class="pl-3">Ttitre Séjour en cours</td>' +
                        ' <td class="pt-3">' +
                        '   <input type="checkbox" name="passagers[' + i + '][sejour_en_cour]" id="' + type + '_sejour_en_cour_' + i + '" class="pl-3 ' + type + '_sejour_en_cour_" >' +
                        ' <p class="error"></p>' +
                        ' </td>' +
                        '</tr>' +
                        '</table>';
                    if (type == 'adult') {
                        $html += '<table class="px-3 py-2 mb-2 " style="border:solid 1px orange;width:100%">' +
                            '<tr>' +
                            '<td class="pl-3"> Telephone 1 </td>' +
                            '<td class="pt-3">' +
                            '<input  type="tel" name="passagers[' + i + '][tele1]" id="adult_tele1_' + i + '" class="form-control tele tele_1" style="height:35px!important; ">' +
                            ' <p class="error"></p>' +
                            '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td class="pl-3"> Telephone 2 </td>' +
                            '<td class="pt-3">' +
                            ' <input type="tel" name="passagers[' + i + '][tele2]" id="passagers[' + i + '][tele2]" class="form-control tele" style="height:35px!important; ">' +
                            ' </td>' +
                            ' </tr>' +
                            '<tr>' +
                            ' <td class="pl-3">Téléphone sur place</td>' +
                            ' <td class="pt-3">' +
                            '<input  type="tel" name="passagers[' + i + '][tele_destination]" id="passagers[' + i + '][tele_destination]"  class="form-control tele" style="height:35px!important; ">' +
                            '</td>' +
                            '</tr>' +
                            '<tr>' +
                            ' <td class="pl-3">Email</td>' +
                            ' <td class="pt-3 pb-3">' +
                            '<input type="email" name="passagers[' + i + '][email]" class="form-control " style="height:35px!important; ">' +
                            '</td>' +
                            '</tr>' +
                            '</table>';
                    } else {
                        $html += '<table class="px-3 py-2 mb-2 d-none" style="border:solid 1px orange;width:100%">' +
                            '<tr>' +
                            '<td class="pt-3">' +
                            '<input  type="tel" name="passagers[' + i + '][tele1]" id="passagers[' + i + '][tele1]" class="form-control tele tele_1" style="height:35px!important; ">' +
                            ' <input  type="tel" name="passagers[' + i + '][tele2]" id="passagers[' + i + '][tele2]" class="form-control tele" style="height:35px!important; ">' +
                            ' <input  type="tel" name="passagers[' + i + '][tele2]" id="passagers[' + i + '][tele2]" class="form-control tele" style="height:35px!important; ">' +
                            '<input  type="tel" name="passagers[' + i + '][tele_destination]" id="passagers[' + i + '][tele_destination]"  class="form-control tele" style="height:35px!important; ">' +
                            '<input  type="email" name="passagers[' + i + '][email]" class="form-control " style="height:35px!important; ">' +
                            '</td>' +
                            '</tr>' +
                            '</table>';
                    }
                    $html += '<table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%">' +
                        '<tr>' +
                        '<td class="pl-3">Assistance specifique</td>' +
                        '<td class="pt-3">' +
                        '<input  type="text" name="passagers[' + i + '][assistance]" class="form-control" style="height:35px!important; ">' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td  class="pl-3">Observation</td>' +
                        '<td class="pt-3">' +
                        '<textarea  style="height:38px;" name="passagers[' + i + '][observation]" class="form-control" ></textarea>' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td class="pl-3">Dossier</td>' +
                        '<td class="pt-3">' +
                        '<label for="' + type + '_dossier_etat_' + i + '1">Complet</label><input type="radio" name="passagers[' + i + '][dossier_etat]"  id="' + type + '_dossier_etat_' + i + '1" value="Complet" class="mr-3 ml-2"/>' +
                        '<label for="' + type + '_dossier_etat_' + i + '2">Incomplet</label><input checked type="radio" name="passagers[' + i + '][dossier_etat]" id="' + type + '_dossier_etat_' + i + '2" value="Incomplet" class="ml-2"/>' +
                        '</td>' +
                        '</tr>' +
                        '</table>' +
                        '<table class="px-3 py-2 mb-2" style="border:solid 1px orange;width:100%;display:none">' +
                        @foreach($assurences as $assurence)
                            '<tr class="pr-5">' +
                        '<td class="pt-3 pl-3">{{$assurence->name}}</td>' +
                        '<td class="pt-3">{{$assurence->prix}} € </td>' +
                        '<td class="pt-3">' +
                        '<input type="radio" @if($assurence->prix==0) checked @endif name="passagers[' + i + '][assurance]" id="passagers[' + i + '][assurance]" value="{{$assurence->id}}" >' +
                        '</td>' +
                        '</tr>' +
                        '<tr><td class="pb-3"></td></tr>' +
                        @endforeach
                            '</table>' +
                        ' </div>' +
                        '</div>';

                    if (type == 'bebe' && oldnum < $('#adultcountinput').val()) {
                        console.log("increment bebe")

                        $('#nbPassagers').val(parseInt(nbPassagers) + 1)

                        $(this).parent().children('.input').val(parseInt(oldnum) + 1)
                        $(this).parent().children('.nbtext').text(parseInt(oldnum) + 1)
                        $('#' + $(this).attr('target')).val(parseInt(oldnum) + 1)
                        nb_new_bebe.val(parseInt(nb_new_bebe.val()) + 1)

                        $('#passagersinputs').append($html)
                    } else if (type != 'bebe' && nbNewPassagers < {{$reservation->vol->place_disponible}}) {
                        console.log("increment")
                        $('#nbPassagers').val(parseInt(nbPassagers) + 1)

                        $(this).parent().children('.input').val(parseInt(oldnum) + 1)
                        $(this).parent().children('.nbtext').text(parseInt(oldnum) + 1)

                        if (type == 'adult') {
                            nb_new_adult.val(parseInt(nb_new_adult.val()) + 1)
                        } else if (type == 'enfant') {
                            nb_new_enfant.val(parseInt(nb_new_enfant.val()) + 1)
                        }
                        $('#passagersinputs').append($html)
                    }
                }
            })

            {{--  chamres managment  --}}
            


            $('.modify_passager').click(function () {
                $(this).hide();
                $(this).parent().children('.enregister_passager').show()
            })
            @if(session()->get('already_enregistred')=='already_enregistred')
            $('.exist_message').text("le nombre disponible de vol < nombre de passagers")
            $('#error_modal').modal('show')
            @endif

            $(document).on('click', '.delete_new_passager', function () {
                $('.' + $(this).attr('target')).remove()
                let type = $(this).attr('passager_type')
                if (type == 'bebe') {
                    $('#nb_new_bebe').val($('#nb_new_bebe').val() - 1)
                    $('#bebecount').text($('#bebecount').text() - 1)
                    $('#bebecountinput').val($('#bebecountinput').val() - 1)
                } else if (type == 'enfant') {
                    $('#nb_new_enfant').val($('#nb_new_enfant').val() - 1)
                    $('#enfantcount').text($('#enfantcount').text() - 1)
                    $('#enfantcountinput').val($('#enfantcountinput').val() - 1)
                } else {
                    $('#nb_new_adult').val($('#nb_new_adult').val() - 1)
                    $('#adultcount').text($('#adultcount').text() - 1)
                    $('#adultcountinput').val($('#adultcountinput').val() - 1)
                }
            })

            $(document).on('click', '.modify_passager', function () {
                let id = $(this).attr('passager_id')
                $('.input_' + id).removeAttr('readonly')
                $('.input_' + id).removeAttr('disabled')
            })

            $(document).on('submit', '#passagers_form', function (e) {
                $(".spinner-overlay").css('display', 'flex');
                $('.input').removeAttr('disabled')
                let error_nb = 0;
                let allowedImageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                $('.error').text('')
                let data = {
                    nb_adult: $('#adultcountinput').val(), nb_enfnat: $('#enfantcountinput').val(),
                    nb_bebe: $('#bebecountinput').val()
                }
                let nom;
                let prenom
                let date_naissance
                let passport
                let passportfile
                let date_expiration
                let sejour
                let sejour_date_expiration
                let sejour_en_cour
                let sejourfile
                let passportFileExt
                let sejourFileExt
                let phoneNumber1;
                @foreach($reservation->passagers as $passager)
                    nom = $('#old_{{$passager->type}}_nom_{{$passager->id}}')
                prenom = $('#old_{{$passager->type}}_prenom_{{$passager->id}}')
                date_naissance = $('#old_{{$passager->type}}_date_naissance_{{$passager->id}}')
                passport = $('#old_{{$passager->type}}_passport_{{$passager->id}}')
                passportfile = $('#old_{{$passager->type}}_passport_file_{{$passager->id}}')
                passportFileExt = passportfile.val().split('.').pop().toLowerCase();
                date_expiration = $('#old_{{$passager->type}}_expiration_{{$passager->id}}')
                sejour = $('#old_{{$passager->type}}_sejour_{{$passager->id}}')
                sejour_date_expiration = $('#old_{{$passager->type}}_sejour_expiration_{{$passager->id}}')
                sejour_en_cour = $('#old_{{$passager->type}}_sejour_en_cour_{{$passager->id}}')
                sejourfile = $('#old_{{$passager->type}}_sejour_file_{{$passager->id}}')
                sejourFileExt = sejourfile.val().split('.').pop().toLowerCase();

                data['old_{{$passager->type}}_nom_{{$passager->id}}'] = nom.val()
                data['old_{{$passager->type}}_prenom_{{$passager->id}}'] = prenom.val()
                data['old_{{$passager->type}}_date_{{$passager->id}}'] = date_naissance.val()


                if (nom.val() == '' || nom.val() == null) {
                    nom.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++
                }
                if (prenom.val() == '' || prenom.val() == null) {
                    prenom.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++
                }
                if (date_naissance.val() == '' || date_naissance.val() == null) {
                    date_naissance.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++
                }
                @if($passager->type== 'adulte')
                    phoneNumber1 = $('#old_{{$passager->type}}_tele1_{{$passager->id}}');
                if (!isValidFlightPassengerPhoneNumber(phoneNumber1.val())) {
                    phoneNumber1.parent().children('.error').text('Téléphone doit commencer par 06 ou 07 et contenir de 8 à 10 chiffres.');
                    error_nb++;
                    console.log(phoneNumber1);
                }
                @endif
                if (date_naissance.attr('max')) {
                    if (date_naissance.val() > date_naissance.attr('max')) {
                        let datesplt = date_naissance.attr('max').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                        date_naissance.parent().children('.error').append('date de naissance ne doit pa passe ' + datefinal)
                        error_nb++
                    }
                }
                if (date_naissance.attr('min')) {
                    if (date_naissance.val() < date_naissance.attr('min')) {
                        let datesplt = date_naissance.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                        date_naissance.parent().children('.error').append('date de naissance superieur a ' + datefinal)
                        error_nb++
                    }
                }
                if (passport.val() == '' || passport.val() == null) {
                    passport.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++
                }
                if (date_expiration.val() == '' || date_expiration.val() == null) {
                    date_expiration.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++
                } else if (date_expiration.val() < date_expiration.attr('min')) {
                    let datesplt = date_expiration.attr('min').split('-')
                    let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                    date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal)
                    error_nb++
                }
                if ((passportfile.val() == '' || passportfile.val() == null) && passportfile.parent().children('.img_preview').html().trim() == '') {
                    passportfile.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++
                } else if (passportfile.val() != '' && passportfile.val() != null && $.inArray(passportFileExt, allowedImageExtensions) == -1) {
                    passportfile.parent().children('.error').text('Format d\'image invalide. Veuillez sélectionner un fichier JPG, PNG, GIF, BMP ou WEBP.')
                    error_nb++
                }

                if (sejour.val() == '' && !sejour_en_cour.is(':checked')) {
                    sejour.parent().children('.error').append('ce champ est obligatoire')
                    error_nb++
                }
                if (sejour_date_expiration.val() == '' && !sejour_en_cour.is(':checked')) {
                    sejour_date_expiration.parent().children('.error').append('ce champ est obligatoire')
                    error_nb++
                } else if (sejour_date_expiration.val() < sejour_date_expiration.attr('min') && sejour_date_expiration.val() != '') {
                    let datesplt = sejour_date_expiration.attr('min').split('-')
                    let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                    sejour_date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal)
                    error_nb++
                }

                if ((sejourfile.val() == '' || sejourfile.val() == null) && sejourfile.parent().children('.img_preview').html().trim() == '' && !sejour_en_cour.is(':checked')) {
                    sejourfile.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++
                } else if (sejourfile.val() != '' && sejourfile.val() != null && $.inArray(sejourFileExt, allowedImageExtensions) == -1) {
                    sejourfile.parent().children('.error').text('Format d\'image invalide. Veuillez sélectionner un fichier JPG, PNG, GIF, BMP ou WEBP.')
                    error_nb++
                }
                    @endforeach

                for (let i = 1; i <= $('#nb_new_adult').val(); i++) {
                    let nom = $('#adult_nom_' + i)
                    let prenom = $('#adult_prenom_' + i)
                    let date_naissance = $('#adult_date_naissance_' + i)
                    let passport = $('#adult_passport_' + i)
                    let passportfile = $('#adult_passport_file_' + i)
                    let passportFileExt = passportfile.val().split('.').pop().toLowerCase();
                    let date_expiration = $('#adult_passport_expiration_' + i)

                    let sejour = $('#adult_sejour_' + i)
                    let sejour_date_expiration = $('#adult_sejour_expiration_' + i)
                    let sejour_en_cour = $('#adult_sejour_en_cour_' + i)
                    let sejourfile = $('#adult_sejour_file_' + i)
                    let sejourFileExt = sejourfile.val().split('.').pop().toLowerCase();
                    phoneNumber1 = $('#adult_tele1_' + i)

                    data['adult_nom_' + i] = nom.val()
                    data['adult_prenom_' + i] = prenom.val()
                    data['adult_date_' + i] = date_naissance.val()


                    if (nom.val() == '' || nom.val() == null) {
                        nom.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (prenom.val() == '' || prenom.val() == null) {
                        prenom.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (date_naissance.val() == '' || date_naissance.val() == null) {
                        date_naissance.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (date_naissance.attr('max')) {
                        if (date_naissance.val() > date_naissance.attr('max')) {
                            let datesplt = date_naissance.attr('max').split('-')
                            let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                            date_naissance.parent().children('.error').append('date de naissance ne doit pa passe ' + datefinal)
                            error_nb++
                        }
                    }
                    if (date_naissance.attr('min')) {
                        if (date_naissance.val() < date_naissance.attr('min')) {
                            let datesplt = date_naissance.attr('min').split('-')
                            let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]

                            date_naissance.parent().children('.error').append('date de naissance superieur a ' + datefinal)
                            error_nb++
                        }
                    }
                    if (!isValidFlightPassengerPhoneNumber(phoneNumber1.val())) {
                        phoneNumber1.parent().children('.error').text('Téléphone doit commencer par 06 ou 07 et contenir de 8 à 10 chiffres.')
                        error_nb++
                    }

                    if (passport.val() == '' || passport.val() == null) {
                        passport.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (date_expiration.val() == '') {
                        date_expiration.parent().children('.error').append('ce champ est obligatoire.')
                        error_nb++
                    } else if (date_expiration.val() < date_expiration.attr('min')) {
                        let datesplt = date_expiration.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]

                        date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal)
                        error_nb++
                    }
                    if ((passportfile.val() == '' || passportfile.val() == null)) {
                        passportfile.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    } else if (passportfile.val() != '' && passportfile.val() != null && $.inArray(passportFileExt, allowedImageExtensions) == -1) {
                        passportfile.parent().children('.error').text('Format d\'image invalide. Veuillez sélectionner un fichier JPG, PNG, GIF, BMP ou WEBP.')
                        error_nb++
                    }

                    if (sejour.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejour.parent().children('.error').append('ce champ est obligatoire')
                        error_nb++
                    }
                    if (sejour_date_expiration.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejour_date_expiration.parent().children('.error').append('ce champ est obligatoire')
                        error_nb++
                    } else if (sejour_date_expiration.val() < sejour_date_expiration.attr('min') && sejour_date_expiration.val() != '') {
                        let datesplt = sejour_date_expiration.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                        sejour_date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal)
                        error_nb++
                    }
                    if ((sejourfile.val() == '' || sejourfile.val() == null) && !sejour_en_cour.is(':checked')) {
                        sejourfile.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (sejourfile.val() != '' && sejourfile.val() != null && $.inArray(sejourFileExt, allowedImageExtensions) == -1) {
                        sejourfile.parent().children('.error').text('Format d\'image invalide. Veuillez sélectionner un fichier JPG, PNG, GIF, BMP ou WEBP.')
                        error_nb++
                    }
                }
                for (let i = 1; i <= $('#nb_new_enfant').val(); i++) {
                    let nom = $('#enfant_nom_' + i)
                    let prenom = $('#enfant_prenom_' + i)
                    let date_naissance = $('#enfant_date_naissance_' + i)
                    let passport = $('#enfant_passport_' + i)
                    let passportfile = $('#enfant_passport_file_' + i)
                    let passportFileExt = passportfile.val().split('.').pop().toLowerCase();
                    let date_expiration = $('#enfant_passport_expiration_' + i)

                    let sejour = $('#enfant_sejour_' + i)
                    let sejour_date_expiration = $('#enfant_sejour_expiration_' + i)
                    let sejour_en_cour = $('#enfant_sejour_en_cour_' + i)
                    let sejourfile = $('#enfant_sejour_file_' + i)
                    let sejourFileExt = sejourfile.val().split('.').pop().toLowerCase();


                    data['enfant_nom_' + i] = nom.val()
                    data['enfant_prenom_' + i] = prenom.val()
                    data['enfant_date_' + i] = date_naissance.val()

                    if (nom.val() == '' || nom.val() == null) {
                        nom.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (prenom.val() == '' || prenom.val() == null) {
                        prenom.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (date_naissance.val() == '' || date_naissance.val() == null) {
                        date_naissance.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (date_naissance.attr('max')) {
                        if (date_naissance.val() > date_naissance.attr('max')) {
                            let datesplt = date_naissance.attr('max').split('-')
                            let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]

                            date_naissance.parent().children('.error').append('date de naissance ne doit pa passe ' + datefinal)
                            error_nb++
                        }
                    }
                    if (date_naissance.attr('min')) {
                        if (date_naissance.val() < date_naissance.attr('min')) {
                            let datesplt = date_naissance.attr('min').split('-')
                            let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]

                            date_naissance.parent().children('.error').append('date de naissance superieur a ' + datefinal)
                            error_nb++
                        }
                    }
                    if (passport.val() == '' || passport.val() == null) {
                        passport.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if ((date_expiration.val() == '' || date_expiration.val() == null)) {
                        date_expiration.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    } else if (date_expiration.val() < date_expiration.attr('min')) {
                        let datesplt = date_expiration.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]

                        date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal)
                        error_nb++
                    }
                    if ((passportfile.val() == '' || passportfile.val() == null)) {
                        passportfile.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (passportfile.val() != '' && passportfile.val() != null && $.inArray(passportFileExt, allowedImageExtensions) == -1) {
                        passportfile.parent().children('.error').text('Format d\'image invalide. Veuillez sélectionner un fichier JPG, PNG, GIF, BMP ou WEBP.')
                        error_nb++
                    }
                    if (sejour.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejour.parent().children('.error').append('ce champ est obligatoire')
                    }
                    if (sejour_date_expiration.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejour_date_expiration.parent().children('.error').append('ce champ est obligatoire')
                        error_nb++
                    } else if (sejour_date_expiration.val() < sejour_date_expiration.attr('min') && sejour_date_expiration.val() != '') {
                        let datesplt = sejour_date_expiration.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]

                        sejour_date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal)
                        error_nb++
                    }
                    if ((sejourfile.val() == '' || sejourfile.val() == null) && !sejour_en_cour.is(':checked')) {
                        sejourfile.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (sejourfile.val() != '' && sejourfile.val() != null && $.inArray(sejourFileExt, allowedImageExtensions) == -1) {
                        sejourfile.parent().children('.error').text('Format d\'image invalide. Veuillez sélectionner un fichier JPG, PNG, GIF, BMP ou WEBP.')
                        error_nb++
                    }
                }
                for (let i = 1; i <= $('#nb_new_bebe').val(); i++) {
                    let nom = $('#bebe_nom_' + i)
                    let prenom = $('#bebe_prenom_' + i)
                    let date_naissance = $('#bebe_date_naissance_' + i)
                    let passport = $('#bebe_passport_' + i)
                    let passportfile = $('#bebe_passport_file_' + i)
                    let passportFileExt = passportfile.val().split('.').pop().toLowerCase();
                    let date_expiration = $('#bebe_passport_expiration_' + i)

                    let sejour = $('#bebe_sejour_' + i)
                    let sejour_date_expiration = $('#bebe_sejour_expiration_' + i)
                    let sejour_en_cour = $('#bebe_sejour_en_cour_' + i)
                    let sejourfile = $('#bebe_sejour_file_' + i)
                    let sejourFileExt = sejourfile.val().split('.').pop().toLowerCase();

                    data['bebe_nom_' + i] = nom.val()
                    data['bebe_prenom_' + i] = prenom.val()
                    data['bebe_date_' + i] = date_naissance.val()


                    if (nom.val() == '' || nom.val() == null) {
                        nom.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (prenom.val() == '' || prenom.val() == null) {
                        prenom.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (date_naissance.val() == '' || date_naissance.val() == null) {
                        date_naissance.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (date_naissance.attr('max')) {
                        if (date_naissance.val() > date_naissance.attr('max')) {
                            let datesplt = date_naissance.attr('max').split('-')
                            let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                            date_naissance.parent().children('.error').append('date de naissance ne doit pa passe ' + datefinal)
                            error_nb++
                        }
                    }
                    if (date_naissance.attr('min')) {
                        if (date_naissance.val() < date_naissance.attr('min')) {
                            let datesplt = date_naissance.attr('min').split('-')
                            let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                            date_naissance.parent().children('.error').append('date de naissance superieur a ' + datefinal)
                            error_nb++
                        }
                    }
                    if (passport.val() == '' || passport.val() == null) {
                        passport.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    }
                    if (date_expiration.val() == '' || date_expiration.val() == null) {
                        date_expiration.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    } else if (date_expiration.val() < date_expiration.attr('min')) {
                        let datesplt = date_expiration.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]

                        date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal)
                        error_nb++
                    }
                    if (passportfile.val() == '' || passportfile.val() == null) {
                        passportfile.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    } else if (passportfile.val() != '' && passportfile.val() != null && $.inArray(passportFileExt, allowedImageExtensions) == -1) {
                        passportfile.parent().children('.error').text('Format d\'image invalide. Veuillez sélectionner un fichier JPG, PNG, GIF, BMP ou WEBP.')
                        error_nb++
                    }

                    if (sejour.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejour.parent().children('.error').append('ce champ est obligatoire')
                    }
                    if (sejour_date_expiration.val() == '' && !sejour_en_cour.is(':checked')) {
                        sejour_date_expiration.parent().children('.error').append('ce champ est obligatoire')
                        error_nb++
                    } else if (sejour_date_expiration.val() < sejour_date_expiration.attr('min') && sejour_date_expiration.val() != '') {
                        let datesplt = sejour_date_expiration.attr('min').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]

                        sejour_date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal)
                        error_nb++
                    }

                    if ((sejourfile.val() == '' || sejourfile.val() == null) && !sejour_en_cour.is(':checked')) {
                        sejourfile.parent().children('.error').text('ce champ est obligatoire')
                        error_nb++
                    } else if (sejourfile.val() != '' && sejourfile.val() != null && $.inArray(sejourFileExt, allowedImageExtensions) == -1) {
                        sejourfile.parent().children('.error').text('Format d\'image invalide. Veuillez sélectionner un fichier JPG, PNG, GIF, BMP ou WEBP.')
                        error_nb++
                    }
                }
                console.log('the number of errors : ' + error_nb);
                console.log('number of adults ' + $('#adultcountinput').val());
                console.log('number of enfants ' + $('#enfantcountinput').val());
                if (error_nb == 0 && ($('#enfantcountinput').val() > 0 || $('#adultcountinput').val() > 0)) {
                    console.log('form submited')
                    $('.input').removeAttr('disabled')
                    $('.input').removeAttr('readonly')
                    $('.enregister_passager').hide()
                } else {
                    console.log('uknown error')
                    $(".spinner-overlay").css('display', 'none');
                    e.preventDefault();
                }
            })

            $(document).on('change', '.passportfieldfile ', function (event) {
                $(this).parent().children('.custom-file-upload').css({'background': 'green', 'color': 'white'})
                let forma = event.target.files[0]['name'].toLowerCase().split(".")
                forma = forma[forma.length - 1]
                if (forma == "pdf") {
                    $(this).parent().children('.img_preview').html('<embed  src="' + URL.createObjectURL(event.target.files[0]) + '" class="pasport_file" style="height: 50px;"/>')
                } else if (["jpg", "png", "jpeg"].includes(forma)) {
                    $(this).parent().children('.img_preview').html('<img src="' + URL.createObjectURL(event.target.files[0]) + '" class="pasport_file" style="height: 50px"/>')
                }
            })


            $('#edit_passager').click(function () {
                $('#passagers_form').show()
            })
            $('#annuler_tout').on('click', function () {
                $('.annuler_tout').show()
                $('.annuler_one').hide()

                $('#deletepassagermodal').modal('show')
            })

            $('.delete_passager').on('click', function () {
                $('.annuler_tout').hide()
                $('.annuler_one').show()
                $('#passager_id').val($(this).attr('target'))
                $('#deletepassagermodal').modal('show')
                $('.delete_passager_click').attr('target', $(this).attr('target'))
            })

            $('.delete_passager_click').on('click', function () {
                $('#passager_' + $(this).attr('target')).hide();
                $('#deletepassagermodal').modal('hide')
            })

        })

    </script>

    <script>
        $('#display-bill-form').click(function () {
            $('#bill-form').show();
            $('#bill-section').hide();
        })
        $('#hide-bill-form').click(function () {
            $('#bill-form').hide();
            $('#bill-section').show();
            $('#billNumber').val({{$reservation->bill?->n_acompte}});
        })
    </script>
@endpush
