@extends('layouts.admin')

@section('SideBar')
    <x-side-bar />
@endsection


@section('TopBar')
    @include('agent.partials.top-nav')
@endsection
@push("script")

@endpush
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
    </style>
@endsection


@section('content')
    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Consultation</h1>
            <img src="{{$flightsGroup->country->image}}" style="height: 50px">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('agent.home')}}">Accueil</a></li>
                <li class="breadcrumb-item">{{$flightsGroup->country->nom}}</li>
                <li class="breadcrumb-item">Consultation</li>
                <li class="breadcrumb-item">Consulte</li>

            </ol>
        </div>
        <div class="d-flex flex-wrap justify-content-between flex-fill " id="passagersinputs">
            <div class="card mb-4 pt-0 pb-3" style="border: solid 5px #6777ef">
                <div class="card-header pb-3 pt-0">
                    <div style="height: 30px" class="bg-primary text-center">
                        <h6 class="m-0 font-weight-bold text-light">
                            Reservation
                        </h6>
                    </div>
                </div>

                <div class="card-body">
                    <table class="custum_table">
                        <tr>
                            <td style="width: 40%">N° Réservation</td>
                            <td class="d-flex justify-content-between align-items-center">
                                {{substr($reservation->passagers[0]['nom'],0,3).''.$reservation->id}}
                            </td>

                        </tr>
                        <tr>
                            <td>Date Réservation</td>
                            <td>{{ \App\Support\Dates::format($reservation->created_at) }}</td>
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
                        <tr>
                            <td>Date Aller</td>
                            <td class="d-flex justify-content-between align-items-center">
                                {{\Carbon\Carbon::parse($reservation->vol->date_aller)->format('d/m/Y')}}
                            </td>
                        </tr>
                        <tr>
                            <td>Date Retour</td>
                            <td>{{\Carbon\Carbon::parse($reservation->vol->date_retour)->format('d/m/Y')}}</td>
                        </tr>
                        @if(!$reservation->acomptes)
                            <tr>
                                <td>Paiement</td>
                                <td>
                                    <div class="text-center">
                                        <button class="btn btn-primary mt-3  add_payment" type="button">
                                            Ajouter
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
            @foreach($reservation->acomptes as $acompte)
                <x-agent.vols.common.edit-reservation-payment-card :$flightsGroup
                                                                  :$acompte
                                                                  :$loop
                                                                  :reservationId="$reservation->id"
                                                                  :reservationTotalPrice="$reservation->prix_total"
                                                                  :reservationPayedAmount="$reservation->prix_payed"

                />
            @endforeach
            <x-agent.vols.common.add-reservation-payment-card :$flightsGroup
                                                             :reservationId="$reservation->id"
                                                             :reservationTotalPrice="$reservation->prix_total"
                                                             :reservationPayedAmount="$reservation->prix_payed"
            />

        </div>
        <form action="{{ route('agent.vols.dossiers.update', $flightsGroup->id) }}"
              id="passagers_form" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="reservation_id" value="{{$reservation->id}}">

            <div class="d-flex flex-wrap">
                @foreach($reservation->passagers as $person)
                    <div class="my-3 mx-3"
                         style="border:solid 2px @if($person->type=='bebe') #e74ac6 @elseif($person->type=='enfant') #f8953c @else #5656e7 @endif;width: 400px"
                         id="passager_{{$person->id}}">
                        <p class="text-light d-flex align-items-center justify-content-between px-4 py-3"
                           style="font-size:20px;background-color: @if($person->type=='bebe') #e74ac6 @elseif($person->type=='enfant') #f8953c @else #5656e7 @endif ">
                            <button class="btn btn-primary modify_passager" type="button"
                                    passager_id="{{$person->id}}"
                                    passager_type="{{$person->type}}">
                                Modifier
                            </button>
                            <button class="btn btn-primary enregister_passager"
                                    style="display:none;">Enregistrer
                            </button>

                            {{$person->type}} @if($person->type=='bebe')
                                < 2
                                ans
                            @elseif($person->type=='enfant')
                                <
                                12 ans
                            @endif
                            <span></span>
                        </p>
                        <x-agent.vols.common.reservation-passager-card :$reservation
                                                                      :destination="strtolower($flightsGroup->country->nom)"
                                                                      :$person
                                                                      :$assurences
                                                                      :showVerificationSection="0"/>
                    </div>
                @endforeach
            </div>
        </form>
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
    <div class="modal fade" id="success_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 mb-0" style="border-bottom: none">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body d-flex align-items-center" style="font-size: 20px">
                    <img src="{{asset('sys/check.png')}}" style="height: 40px">
                    <p class="success_message ml-3"></p>
                </div>
            </div>
        </div>
    </div>
    <x-agent.common.image-preview-modal/>
    <x-agent.vols.common.delete-passenger-passport-modal :groupId="$flightsGroup->id"/>

@endsection

@push("script")
    <script src="{{ asset('assets/js/agent/vols/gestion/consulte/index.js') }}"></script>
    <script src="{{ asset('assets/js/validation/flightPassengerValidation.js') }}"></script>
    <script src="{{ asset('assets/js/agent/vols/common/payment-management.js') }}"></script>
    <script src="{{asset('assets/js/agent/vols/common/reservation-infos.js')}}"></script>

    <script>
        $(document).ready(function () {

            @if(session()->get('already_enregistred')=='already_enregistred')
            $('.exist_message').text("le nombre disponible de vol < nombre de passagers")
            $('#error_modal').modal('show')
            @endif

            @if(session()->get('etat')=='success')
            $('.success_message').text("Success")
            $('#success_modal').modal('show')
            @endif

            $('#passagers_form').submit(function (e) {
                let error_nb = 0;
                $('.error').text('')
                let passager_exist = "yes";
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
                let phoneNumber1;
                @foreach($reservation->passagers as $passager)
                    nom = $('#old_{{$passager->type}}_nom_{{$passager->id}}')
                prenom = $('#old_{{$passager->type}}_prenom_{{$passager->id}}')
                date_naissance = $('#old_{{$passager->type}}_date_naissance_{{$passager->id}}')
                passport = $('#old_{{$passager->type}}_passport_{{$passager->id}}')
                passportfile = $('#old_{{$passager->type}}_passport_file_{{$passager->id}}')
                date_expiration = $('#old_{{$passager->type}}_expiration_{{$passager->id}}')
                sejour = $('#old_{{$passager->type}}_sejour_{{$passager->id}}')
                sejour_date_expiration = $('#old_{{$passager->type}}_sejour_expiration_{{$passager->id}}')
                sejour_en_cour = $('#old_{{$passager->type}}_sejour_en_cour_{{$passager->id}}')
                sejourfile = $('#old_{{$passager->type}}_sejour_file_{{$passager->id}}')

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
                if (date_naissance.attr('max')) {
                    if (date_naissance.val() > date_naissance.attr('max')) {
                        let datesplt = date_naissance.attr('max').split('-')
                        let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]
                        console.log(datefinal)
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
                @if($passager->type== 'adulte')
                    phoneNumber1 = $('#old_{{$passager->type}}_tele1_{{$passager->id}}');
                if (!isValidFlightPassengerPhoneNumber(phoneNumber1.val())) {
                    phoneNumber1.parent().children('.error').text('Téléphone doit commencer par 06 ou 07 et contenir de 8 à 10 chiffres.');
                    error_nb++;
                    console.log(phoneNumber1);
                }
                @endif

                if (passport.val() == '' || passport.val() == null) {
                    passport.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++

                }
                if (date_expiration.val() < date_expiration.attr('min')) {
                    let datesplt = date_expiration.attr('min').split('-')
                    let datefinal = datesplt[2] + '-' + datesplt[1] + '-' + datesplt[0]

                    date_expiration.parent().children('.error').append('date d\'expiration superieur a ' + datefinal)
                    error_nb++

                }
                if ((passportfile.val() == '' || passportfile.val() == null) && passportfile.parent().children('.img_preview').html().trim() == '') {
                    passportfile.parent().children('.error').text('ce champ est obligatoire')
                    error_nb++

                }
                if (date_expiration.val() == '' || date_expiration.val() == null) {
                    date_expiration.parent().children('.error').text('ce champ est obligatoire')
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
                }
                @endforeach

                if (error_nb != 0 || ($('#enfantcountinput').val() <= 0 && $('#adultcountinput').val() <= 0)) {
                    e.preventDefault();
                } else {
                    $(".spinner-overlay").css('display', 'flex');
                    $('.input').removeAttr('disabled')
                    $('.input').removeAttr('readonly')
                }
            })
        })
    </script>
@endpush
